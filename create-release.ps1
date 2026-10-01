# Release Script for Lumina Instagram Feed Plugin
# Usage: .\create-release.ps1 -Version "1.1.0" -Message "Auto-refresh URLs and bug fixes"

param(
    [Parameter(Mandatory=$true)]
    [string]$Version,
    
    [Parameter(Mandatory=$false)]
    [string]$Message = "Version $Version"
)

$ErrorActionPreference = "Stop"

Write-Host "Creating Release v$Version" -ForegroundColor Cyan
Write-Host "=========================" -ForegroundColor Cyan
Write-Host ""

# Validate we're in the right directory
if (!(Test-Path "lumina-instagram-feed.php")) {
    Write-Host "❌ Error: Not in plugin directory" -ForegroundColor Red
    Write-Host "Run this script from: C:\Users\jhover\Projects\lumina-plugin" -ForegroundColor Yellow
    exit 1
}

# Check for uncommitted changes
$status = git status --porcelain
if ($status) {
    Write-Host "⚠️  Warning: You have uncommitted changes:" -ForegroundColor Yellow
    Write-Host $status
    $continue = Read-Host "Continue anyway? (y/n)"
    if ($continue -ne "y") {
        exit 1
    }
}

# Update version in plugin file
Write-Host "1. Updating version number..." -ForegroundColor Green
$pluginFile = Get-Content "lumina-instagram-feed.php" -Raw
$pluginFile = $pluginFile -replace "Version:\s+[\d\.]+", "Version:           $Version"
$pluginFile = $pluginFile -replace "define\( 'LUMINA_IG_VERSION', '[\d\.]+' \)", "define( 'LUMINA_IG_VERSION', '$Version' )"
$pluginFile | Set-Content "lumina-instagram-feed.php" -NoNewline
Write-Host "   ✓ Version updated to $Version" -ForegroundColor Gray

# Commit version change
Write-Host "2. Committing version change..." -ForegroundColor Green
git add lumina-instagram-feed.php
git commit -m "Bump version to $Version"
Write-Host "   ✓ Committed" -ForegroundColor Gray

# Push to main
Write-Host "3. Pushing to GitHub..." -ForegroundColor Green
git push origin main
Write-Host "   ✓ Pushed to main" -ForegroundColor Gray

# Create and push tag
Write-Host "4. Creating git tag..." -ForegroundColor Green
git tag -a "v$Version" -m "$Message"
git push origin "v$Version"
Write-Host "   ✓ Tag v$Version created and pushed" -ForegroundColor Gray

# Create deployment zip
Write-Host "5. Creating deployment zip..." -ForegroundColor Green
$zipName = "lumina-instagram-feed-v$Version.zip"
git archive --format=zip --output="../$zipName" HEAD
Write-Host "   ✓ Created: $zipName" -ForegroundColor Gray

# Check if GitHub CLI is installed
Write-Host "6. Creating GitHub release..." -ForegroundColor Green
$ghInstalled = Get-Command gh -ErrorAction SilentlyContinue

if ($ghInstalled) {
    # Create release with GitHub CLI
    $releaseNotes = @"
## Lumina Instagram Feed v$Version

$Message

### Installation

Download the attached zip file and upload via WordPress dashboard, or click "Update Now" if you already have the plugin installed.

### Requirements

- WordPress 6.0+
- PHP 7.4+
- Lumina Agency Hub v1.1.0+

### Support

For issues or questions, contact your agency administrator.
"@

    $releaseNotes | Out-File -FilePath "../release-notes.txt" -Encoding UTF8
    
    gh release create "v$Version" `
        --title "Version $Version" `
        --notes-file "../release-notes.txt" `
        "../$zipName"
    
    Remove-Item "../release-notes.txt"
    Write-Host "   ✓ GitHub release created with attached zip" -ForegroundColor Gray
    
} else {
    Write-Host "   ⚠️  GitHub CLI not installed" -ForegroundColor Yellow
    Write-Host "   Manual step: Create release at https://github.com/jhover-mcd/lumina-plugin/releases" -ForegroundColor Yellow
    Write-Host "   Attach file: ..\$zipName" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "✅ Release Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Verify release: https://github.com/jhover-mcd/lumina-plugin/releases/tag/v$Version" -ForegroundColor Gray
Write-Host "  2. Test update on a staging WordPress site" -ForegroundColor Gray
Write-Host "  3. Notify clients or wait for auto-update" -ForegroundColor Gray
Write-Host ""
Write-Host "Deployment zip saved to: ..\$zipName" -ForegroundColor Gray
