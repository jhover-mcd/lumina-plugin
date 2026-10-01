# Auto-Update System Setup

The Lumina Instagram Feed plugin now includes automatic updates from GitHub releases!

## How It Works

The plugin checks GitHub for new releases and displays update notifications in WordPress, just like plugins from the official WordPress repository.

## For Plugin Developers (You)

### Step 1: Create a GitHub Release

When you're ready to deploy an update:

```bash
# Make sure all changes are committed
cd C:\Users\jhover\Projects\lumina-plugin
git add -A
git commit -m "Your update description"
git push origin main

# Create and push a version tag
git tag -a v1.1.0 -m "Version 1.1.0: Auto-refresh URLs and bug fixes"
git push origin v1.1.0
```

### Step 2: Create GitHub Release with Attached Zip

**Option A: Via GitHub Web Interface (Recommended)**

1. Go to: https://github.com/jhover-mcd/lumina-plugin/releases
2. Click **"Draft a new release"**
3. Choose tag: `v1.1.0`
4. Release title: `Version 1.1.0`
5. Description (this appears in WordPress):
   ```markdown
   ## What's New
   
   - ✅ Auto-refresh Instagram URLs to prevent broken images
   - ✅ Fixed X button clickability in curated feed selection
   - ✅ Fixed cache handling for live feeds
   - ✅ Added GitHub auto-updater for easy updates
   
   ## Deployment
   
   Deploy this update by clicking "Update Now" in your WordPress plugins page.
   
   ## Requirements
   
   - Requires agency hub v1.1.0 or later
   - WordPress 6.0+
   - PHP 7.4+
   ```
6. **Important:** Upload the plugin zip file:
   ```bash
   # Create deployment zip first:
   git archive --format=zip --output=lumina-instagram-feed-v1.1.0.zip HEAD
   ```
7. Drag `lumina-instagram-feed-v1.1.0.zip` to the release attachments
8. Click **"Publish release"**

**Option B: Via GitHub CLI**

```bash
# Install GitHub CLI if needed
winget install GitHub.cli

# Create release with attached zip
git archive --format=zip --output=lumina-instagram-feed-v1.1.0.zip HEAD

gh release create v1.1.0 \
  --title "Version 1.1.0" \
  --notes "See CHANGELOG.md for details" \
  lumina-instagram-feed-v1.1.0.zip
```

### Step 3: WordPress Sites Automatically Detect Update

Within 6 hours (or immediately if admin clicks "Check for updates"):
- WordPress will see the new version available
- An update notification appears on the Plugins page
- Users can click "Update Now" to install

## For WordPress Site Admins (Your Clients)

### Checking for Updates

1. Go to **Dashboard → Updates**
2. Click **"Check Again"** to force check
3. If a new version is available, it will appear in the list

### Installing Updates

**Automatic (One-Click):**
1. Go to **Plugins → Installed Plugins**
2. Find "Lumina Instagram Feed"
3. Click **"Update Now"**
4. WordPress downloads and installs automatically

**Manual (If automatic fails):**
1. Download the latest zip from GitHub releases
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the zip file
4. Activate after installation

## Update Notifications

The plugin checks for updates:
- ✅ Every 12 hours automatically
- ✅ When you visit the Plugins page
- ✅ When you click "Check for updates"
- ✅ When you visit Dashboard → Updates

## Version Numbering

Follow semantic versioning:

- **Major (2.0.0):** Breaking changes, requires manual migration
- **Minor (1.1.0):** New features, backward compatible
- **Patch (1.0.1):** Bug fixes only

## Testing Updates

Before releasing to all clients:

1. **Create a pre-release** on GitHub (check "This is a pre-release")
2. Test on your staging site
3. If successful, edit the release and uncheck "pre-release"
4. Now all sites will see it

## Troubleshooting

### "Update failed" Error

If WordPress can't download from GitHub:

1. Check GitHub release has an attached `.zip` file
2. Make sure the release is public (not draft)
3. Try manual upload instead

### Updates Not Showing

1. Go to **Plugins** → Click "Check for updates"
2. Clear WordPress transients:
   ```php
   delete_site_transient('update_plugins');
   ```
3. Check error logs for API issues

### Wrong File Structure After Update

If the zip contains nested folders:
- Ensure the zip has the plugin files at root level
- Use `git archive` as shown above (creates correct structure)

## Quick Release Checklist

- [ ] Update version in `lumina-instagram-feed.php`
- [ ] Update `LUMINA_IG_VERSION` constant
- [ ] Commit and push to main
- [ ] Create git tag: `git tag -a v1.1.0 -m "Version 1.1.0"`
- [ ] Push tag: `git push origin v1.1.0`
- [ ] Create GitHub release
- [ ] Attach plugin zip to release
- [ ] Publish release
- [ ] Test on one site first
- [ ] Monitor for issues

## Rollback

If an update causes issues:

**For developers:**
1. Delete the problematic release from GitHub
2. Create a new patch release with the fix

**For site admins:**
1. Use a backup/rollback plugin (UpdraftPlus, etc.)
2. Or manually install previous version from GitHub releases

## Security

- Updates are served directly from GitHub
- WordPress verifies file integrity
- Only admins can install updates
- SSL/TLS encrypted downloads

## Benefits

✅ **For You:**
- Push updates to all client sites instantly
- No FTP/SSH access needed to client servers
- Track which sites have which versions
- Easy rollback if needed

✅ **For Clients:**
- One-click updates like WordPress.org plugins
- Update notifications in WordPress dashboard
- No technical knowledge required
- Automatic security and bug fixes

---

**Next Steps:**
1. Commit the updater code
2. Push to GitHub
3. Create your first release (v1.1.0)
4. Test on one WordPress site
5. Roll out to all sites!
