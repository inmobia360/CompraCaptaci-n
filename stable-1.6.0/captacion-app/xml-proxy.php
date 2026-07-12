<?php
/**
 * Proxy XML seguro y mínimo para Compra Captación.
 * Subir este fichero al mismo directorio que el HTML.
 * Uso: ./xml-proxy.php?url=https%3A%2F%2Fdominio.es%2Ffeed.xml
 */
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

function fail_response(int $status, string $message): void {
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

function is_private_or_reserved_ip(string $ip): bool {
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
}

$url = trim($_GET['url'] ?? '');
if ($url === '') {
    fail_response(400, 'Falta el parámetro url.');
}
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    fail_response(400, 'La URL no es válida.');
}
$parts = parse_url($url);
$scheme = strtolower($parts['scheme'] ?? '');
$host = $parts['host'] ?? '';
if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
    fail_response(400, 'Solo se permiten URLs públicas http o https.');
}

$ips = gethostbynamel($host);
if ($ips === false || count($ips) === 0) {
    fail_response(502, 'No se pudo resolver el dominio remoto.');
}
foreach ($ips as $ip) {
    if (is_private_or_reserved_ip($ip)) {
        fail_response(403, 'El dominio remoto resuelve a una IP privada o reservada.');
    }
}

$maxBytes = 5 * 1024 * 1024;
$body = '';

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS => 0,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 18,
        CURLOPT_USERAGENT => 'Compra Captación XML Importer/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/xml,text/xml,application/rss+xml,*/*;q=0.5'],
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_HEADER => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$body, $maxBytes) {
            $body .= $chunk;
            if (strlen($body) > $maxBytes) {
                return 0;
            }
            return strlen($chunk);
        },
    ]);
    $ok = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($ok === false) {
        fail_response(502, 'No se pudo descargar el XML remoto: ' . ($error ?: 'error de conexión.'));
    }
    if ($status < 200 || $status >= 300) {
        fail_response(502, 'El servidor remoto respondió con HTTP ' . $status . '.');
    }
} else {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 18,
            'header' => "User-Agent: Compra Captación XML Importer/1.0\r\nAccept: application/xml,text/xml,application/rss+xml,*/*;q=0.5\r\n",
            'follow_location' => 0,
            'max_redirects' => 0,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $downloaded = @file_get_contents($url, false, $context, 0, $maxBytes + 1);
    if ($downloaded === false) {
        fail_response(502, 'No se pudo descargar el XML remoto desde el servidor.');
    }
    $body = $downloaded;
}

if ($body === '') {
    fail_response(502, 'El XML remoto está vacío.');
}
if (strlen($body) > $maxBytes) {
    fail_response(413, 'El XML supera el tamaño máximo permitido de 5 MB.');
}

$preview = strtolower(substr(trim($body), 0, 500));
if (preg_match('/^<!doctype\s+html/i', $body) || preg_match('/^<html\b/i', $body) || preg_match('/<body\b/i', $body) || preg_match('/ha fallado la comprobaci[oó]n de la cookie|cookie|consent|login|iniciar sesi[oó]n|acceso restringido/i', $preview)) {
    fail_response(422, 'La URL remota devuelve HTML o una pantalla intermedia de cookies/login, no XML válido.');
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($body);
if ($xml === false) {
    fail_response(422, 'La respuesta remota no contiene un XML válido.');
}

echo $body;
