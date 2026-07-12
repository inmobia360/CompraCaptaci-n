param(
    [string]$Source = "stable-1.5.1\captacion-app",
    [string]$Output = "dist\captacion-app.zip",
    [string]$FolderOutput = "dist\captacion-app-folder.zip",
    [string]$LegacyFlatOutput = "dist\captacion-app-flat.zip"
)

$ErrorActionPreference = "Stop"

$Source = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($Source)
$Output = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($Output)
$FolderOutput = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($FolderOutput)
$LegacyFlatOutput = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($LegacyFlatOutput)

if (-not (Test-Path -LiteralPath $Source -PathType Container)) {
    throw "No existe la carpeta fuente del tema: $Source"
}

$stylePath = Join-Path $Source "style.css"
if (-not (Test-Path -LiteralPath $stylePath -PathType Leaf)) {
    throw "El tema no contiene style.css en la raiz: $stylePath"
}

$style = Get-Content -LiteralPath $stylePath -Raw -Encoding UTF8
if ($style -notmatch "Theme Name:\s*" -or $style -notmatch "Text Domain:\s*captacion-app") {
    throw "style.css no contiene la cabecera WordPress esperada del tema"
}

foreach ($path in @($Output, $FolderOutput, $LegacyFlatOutput)) {
    $parent = Split-Path -Parent $path
    if ($parent -and -not (Test-Path -LiteralPath $parent -PathType Container)) {
        New-Item -ItemType Directory -Path $parent | Out-Null
    }
}

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

# captacion-app.zip  -> con carpeta (compatible con WordPress Admin)
# captacion-app-flat.zip -> plano (subida manual Hostinger/cPanel)
# captacion-app-folder.zip -> alias con carpeta (legado)

function New-CaptacionZip {
    param(
        [string]$SourcePath,
        [string]$ZipPath,
        [bool]$WrapInThemeFolder
    )

    $tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("captacion-theme-build-" + [guid]::NewGuid().ToString("N"))
    $tempTheme = if ($WrapInThemeFolder) { Join-Path $tempRoot "captacion-app" } else { $tempRoot }

    try {
        New-Item -ItemType Directory -Path $tempTheme -Force | Out-Null
        Copy-Item -Path (Join-Path $SourcePath "*") -Destination $tempTheme -Recurse -Force

        $forbiddenNames = @(".git", "wp-config.php", "uploads", "cache", "tools")
        Get-ChildItem -LiteralPath $tempTheme -Recurse -Force -ErrorAction SilentlyContinue | Where-Object {
            $forbiddenNames -contains $_.Name -or $_.Name -like "*.log" -or $_.Name -like "*.md"
        } | ForEach-Object {
            Remove-Item -LiteralPath $_.FullName -Recurse -Force
        }

        if (Test-Path -LiteralPath $ZipPath -PathType Leaf) {
            Remove-Item -LiteralPath $ZipPath -Force
        }

        $archive = [System.IO.Compression.ZipFile]::Open($ZipPath, [System.IO.Compression.ZipArchiveMode]::Create)
        try {
            Get-ChildItem -LiteralPath $tempTheme -Recurse -File | ForEach-Object {
                $relativePath = $_.FullName.Substring($tempRoot.Length + 1).Replace([System.IO.Path]::DirectorySeparatorChar, "/")
                [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                    $archive,
                    $_.FullName,
                    $relativePath,
                    [System.IO.Compression.CompressionLevel]::Optimal
                ) | Out-Null
            }
        }
        finally {
            $archive.Dispose()
        }
    }
    finally {
        if (Test-Path -LiteralPath $tempRoot) {
            Remove-Item -LiteralPath $tempRoot -Recurse -Force
        }
    }
}

function Test-CaptacionZipEntries {
    param(
        [string]$ZipPath,
        [string[]]$RequiredEntries
    )

    $archive = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
    try {
        $entries = $archive.Entries | ForEach-Object { $_.FullName }
        foreach ($entry in $RequiredEntries) {
            if ($entries -notcontains $entry) {
                throw "El ZIP generado no contiene $entry"
            }
        }
    }
    finally {
        $archive.Dispose()
    }
}

New-CaptacionZip -SourcePath $Source -ZipPath $Output -WrapInThemeFolder $true
Test-CaptacionZipEntries -ZipPath $Output -RequiredEntries @("captacion-app/style.css", "captacion-app/functions.php", "captacion-app/template-app-interactiva.php")
$mainZip = Get-Item -LiteralPath $Output
"ZIP principal generado: $($mainZip.FullName)"
"Tamano: $([Math]::Round($mainZip.Length / 1MB, 2)) MB"
"Estructura esperada: captacion-app/style.css"

Copy-Item -LiteralPath $Output -Destination $FolderOutput -Force
Test-CaptacionZipEntries -ZipPath $FolderOutput -RequiredEntries @("captacion-app/style.css", "captacion-app/functions.php", "captacion-app/template-app-interactiva.php")
"Alias con carpeta generado: $FolderOutput"

New-CaptacionZip -SourcePath $Source -ZipPath $LegacyFlatOutput -WrapInThemeFolder $false
Test-CaptacionZipEntries -ZipPath $LegacyFlatOutput -RequiredEntries @("style.css", "functions.php", "template-app-interactiva.php")
"ZIP plano generado: $LegacyFlatOutput (para subida manual Hostinger/cPanel)"
