# WordPress Plugin Update - Auto-Refresh Instagram URLs

## Problem Solved

Instagram media URLs expire after ~5-7 days, causing images in curated feeds to break periodically. Users had to manually click "Import from Instagram" every week to fix broken images.

## What's New

### Automatic URL Refresh
- **Auto-refresh on page load**: When visitors view the feed, if URLs are older than 5 days, they're automatically refreshed in the background
- **No selection changes**: URL refresh updates only image URLs and permalinks, keeping your curated selection and order intact
- **Logging**: Refresh events are logged to help monitor the system

### Admin Interface Updates
- **URL age indicator**: Shows how old the current URLs are with visual status (✓ Fresh or ⚠️ Old)
- **Refresh URL button**: Manual button to refresh URLs on demand
- **Warning notice**: Displays when URLs are older than 5 days and need refreshing
- **Success/error messages**: Clear feedback when URLs are refreshed

### API Changes

#### New Methods in `Lumina_IG_Api`

**`refresh_media( $limit )`**
- Calls the new `/v1/refresh` endpoint on the agency hub
- Returns fresh URLs with `fetched_at` timestamp

**Updated: `fetch_media( $limit )`**
- Now returns array with `items` and `fetched_at` instead of just items
- Backward compatible with existing code

#### New Methods in `Lumina_IG_Curated`

**`urls_need_refresh()`**
- Returns true if URLs are older than 5 days

**`get_url_age_days()`**
- Returns the age of current URLs in days

**`refresh_urls( $limit )`**
- Refreshes Instagram media URLs without changing library or selection
- Updates only `image_url` and `permalink` fields
- Returns count of updated posts

**Updated: `sync_library( $limit )`**
- Now stores `fetched_at` timestamp in metadata
- Compatible with new API response format

**Updated: `get_display_feed( $limit )`**
- Auto-refreshes URLs if older than 5 days before returning feed
- Logs refresh events for monitoring

#### New Admin Handler

**`handle_refresh_urls()`**
- Processes manual URL refresh button clicks
- Shows success message with count of updated posts
- Handles errors gracefully

### Template Updates

**`templates/admin/curate.php`**
- URL age status indicator in toolbar
- "Refresh Image URLs" button
- Warning notice for stale URLs
- Success/error messages for URL refresh actions

## Files Changed

```
includes/class-lumina-ig-api.php         - Added refresh_media() method and timestamps
includes/class-lumina-ig-curated.php     - Added URL refresh logic and age checking
includes/class-lumina-ig-admin.php       - Added refresh URL action handler  
templates/admin/curate.php               - Added UI for URL age and refresh button
```

## Backward Compatibility

✅ **100% backward compatible**
- Existing code continues to work
- Old stored data is handled gracefully
- No database migrations needed

## Testing

### 1. Test Auto-Refresh

```php
// In curated mode, set fetched_at to 6 days ago
$meta = lumina_ig()->curated->get_meta();
$meta['fetched_at'] = time() - (6 * DAY_IN_SECONDS);
update_option( Lumina_IG_Curated::META_OPTION, $meta );

// Visit the frontend feed - should auto-refresh and log event
// Check error_log for: "Lumina Instagram Feed: Auto-refreshed X post URLs..."
```

### 2. Test Manual Refresh Button

1. Go to **Lumina Instagram → Curate Feed**
2. Check the URL age indicator
3. Click **"Refresh Image URLs"** button
4. Should see success message: "✓ Instagram media URLs refreshed for X posts"

### 3. Test Warning Notice

1. Set `fetched_at` to 6 days ago (see code above)
2. Go to **Curate Feed** page
3. Should see yellow warning: "Instagram media URLs need refreshing"

### 4. Test URL Age Display

1. Import some posts
2. Check toolbar - should show "Image URLs: ✓ Fresh (0.0 days old)"
3. Set timestamp to 6 days ago
4. Reload - should show "Image URLs: ⚠️ Old (6.0 days old)"

## Configuration

No configuration needed! The auto-refresh happens automatically when URLs are older than 5 days.

To change the refresh threshold, modify:
```php
// In class-lumina-ig-curated.php
const URL_EXPIRATION_DAYS = 5; // Change to your desired days
```

## Monitoring

Check WordPress error logs for automatic refresh events:

```
Lumina Instagram Feed: Auto-refreshed 12 post URLs (age was 5.2 days)
```

If you see frequent refreshes or errors, check:
1. Agency hub `/v1/refresh` endpoint is working
2. License key is valid and active
3. Instagram token hasn't expired

## What Users See

### Before
- Images break after a week
- Must manually click "Import from Instagram" 
- Annoying maintenance task

### After
- Images stay working automatically
- No manual intervention needed
- Optional manual refresh button available
- Clear status indicators for URL freshness

## Agency Hub Requirements

This plugin update requires the updated agency hub with:
- `/v1/refresh` endpoint
- `fetched_at` timestamps in responses

Make sure to deploy the hub updates from the `cursor/fix-instagram-url-expiration` branch first.

## Migration Notes

No migration needed. When upgrading:

1. Deploy updated hub first
2. Deploy updated plugin
3. Existing curated feeds continue working
4. First auto-refresh happens within 24 hours (on next page view)

For immediate refresh after update:
- Go to **Curate Feed** page
- Click **"Refresh Image URLs"**

## Future Enhancements

Potential improvements for future versions:

- [ ] WP-Cron scheduled refresh (instead of on-demand)
- [ ] Dashboard widget showing URL age across all sites
- [ ] Email notifications when URLs need manual refresh
- [ ] Admin bar indicator for URL status
- [ ] Settings page option to configure refresh threshold

---

**Version:** Compatible with hub build `2026-08-19-url-refresh`  
**Tested with:** WordPress 6.0+, PHP 8.1+  
**Breaking changes:** None
