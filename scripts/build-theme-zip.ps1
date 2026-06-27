param(
    [string]$Source = "stable-1.5.1\captacion-app",
    [string]$Output = "dist\captacion-app.zip"
)

$ErrorActionPreference = "Stop"

$Source = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($Source)
$Output = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($Output)

if (-not (Test-Path -LiteralPath $Source -PathType Container)) {
    throw "No existe la carpeta fuente del tema: $Source"
}

$stylePath = Join-Path $Source "style.css"
if (-not (Test-Path -LiteralPath $stylePath -PathType Leaf)) {
    throw "El tema no contiene style.css en la raiz: $stylePath"
}

$style = Get-Content -LiteralPath $stylePath -Raw
if ($style -notmatch "Theme Name:\s*Captacion\.app") {
    throw "style.css no contiene la cabecera esperada de Captacion.app"
}

$outputParent = Split-Path -Parent $Output
if ($outputParent -and -not (Test-Path -LiteralPath $outputParent -PathType Container)) {
    New-Item -ItemType Directory -Path $outputParent | Out-Null
}

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("captacion-theme-build-" + [guid]::NewGuid().ToString("N"))
$tempTheme = Join-Path $tempRoot "captacion-app"

try {
    New-Item -ItemType Directory -Path $tempTheme | Out-Null
    Copy-Item -Path (Join-Path $Source "*") -Destination $tempTheme -Recurse -Force

    $forbiddenNames = @(".git", "wp-config.php", "uploads", "cache")
    Get-ChildItem -LiteralPath $tempTheme -Recurse -Force -ErrorAction SilentlyContinue | Where-Object {
        $forbiddenNames -contains $_.Name -or $_.Name -like "*.log"
    } | ForEach-Object {
        Remove-Item -LiteralPath $_.FullName -Recurse -Force
    }

    if (Test-Path -LiteralPath $Output -PathType Leaf) {
        Remove-Item -LiteralPath $Output -Force
    }

    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($tempRoot, $Output)
    $zip = Get-Item -LiteralPath $Output
    $archive = [System.IO.Compression.ZipFile]::OpenRead($Output)
    try {
        $entries = $archive.Entries | ForEach-Object { $_.FullName.Replace("\", "/") }
        $requiredEntries = @(
            "captacion-app/style.css",
            "captacion-app/functions.php",
            "captacion-app/template-app-interactiva.php"
        )
        foreach ($entry in $requiredEntries) {
            if ($entries -notcontains $entry) {
                throw "El ZIP generado no contiene $entry"
            }
        }
    }
    finally {
        $archive.Dispose()
    }
    "ZIP generado: $($zip.FullName)"
    "Tamano: $([Math]::Round($zip.Length / 1MB, 2)) MB"
    "Estructura esperada: captacion-app/style.css"
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}
