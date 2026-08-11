Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$currentDir = Get-Location
$zipPath = Join-Path $currentDir "universal-post-rss-loop.zip"
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)
$sourceFolder = Join-Path $currentDir "universal-post-rss-loop"

$files = Get-ChildItem -Path $sourceFolder -Recurse | Where-Object { -not $_.PSIsContainer }

foreach ($file in $files) {
    $relPath = $file.FullName.Substring($sourceFolder.Length + 1).Replace('\', '/')
    $entryName = "universal-post-rss-loop/" + $relPath
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $file.FullName, $entryName, [System.IO.Compression.CompressionLevel]::Optimal)
}

$zip.Dispose()
Write-Host "ZIP file successfully created with POSIX slashes!"
