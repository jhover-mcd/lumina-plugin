# Build a WordPress-ready plugin ZIP (files only — no empty directory entries).
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

$python = @"
import os
import zipfile

staging = r"$staging"
zip_path = r"$zipPath"
slug = "$slug"

with zipfile.ZipFile(zip_path, "w", compression=zipfile.ZIP_DEFLATED) as zf:
    for dirpath, dirnames, filenames in os.walk(staging):
        for filename in filenames:
            full_path = os.path.join(dirpath, filename)
            rel_path = os.path.relpath(full_path, staging).replace("\\\\", "/")
            arcname = f"{slug}/{rel_path}"
            zf.write(full_path, arcname)

zero_byte = [i.filename for i in zipfile.ZipFile(zip_path).infolist() if i.file_size == 0]
if zero_byte:
    raise SystemExit("ZIP contains zero-byte entries: " + ", ".join(zero_byte))

print(f"Created {zip_path}")
"@

$python | python -

if ($LASTEXITCODE -ne 0) {
	throw "Failed to build plugin ZIP."
}

Get-Item $zipPath | Select-Object FullName, Length
