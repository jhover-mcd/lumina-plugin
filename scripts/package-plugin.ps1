# Build a WordPress-ready plugin ZIP (no dev files, no agency-hub).
$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$slug = "lumina-instagram-feed"
$dist = Join-Path $root "dist"
$staging = Join-Path $dist $slug
$zipPath = Join-Path $dist "$slug.zip"

$include = @(
	"lumina-instagram-feed.php",
	"readme.txt",
	"includes",
	"assets",
	"blocks",
	"templates"
)

if (Test-Path $staging) {
	Remove-Item $staging -Recurse -Force
}
New-Item -ItemType Directory -Path $staging -Force | Out-Null

foreach ($item in $include) {
	$source = Join-Path $root $item
	if (-not (Test-Path $source)) {
		throw "Missing required path: $source"
	}
	Copy-Item -Path $source -Destination $staging -Recurse -Force
}

if (Test-Path $zipPath) {
	Remove-Item $zipPath -Force
}

Compress-Archive -Path $staging -DestinationPath $zipPath -Force

Write-Host "Created $zipPath"
Get-Item $zipPath | Select-Object FullName, Length
