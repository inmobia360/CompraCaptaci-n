<?php
/**
 * Importador territorial INE para Captacion.app.
 *
 * Uso recomendado:
 *   wp eval-file wp-content/themes/captacion-app/tools/import-ine-territories.php -- path/al/archivo.csv
 *   wp captacion territory import path/al/archivo.xlsx
 *   wp captacion territory update --source=https://fuente-oficial-ine/archivo.xlsx
 */
if (!defined('ABSPATH')) {
    fwrite(STDERR, "Ejecuta este archivo dentro de WordPress mediante WP-CLI.\n");
    exit(1);
}
$source = $args[0] ?? '';
if (!$source || !function_exists('captacion_app_import_ine_territories')) {
    fwrite(STDERR, "Falta la fuente CSV/XLSX o el tema no está activo.\n");
    exit(1);
}
$result = captacion_app_import_ine_territories($source, true);
if (is_wp_error($result)) {
    fwrite(STDERR, $result->get_error_message() . "\n");
    exit(1);
}
echo wp_json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
