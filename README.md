# Lumina Instagram Feed

WordPress plugin for agency-managed Instagram feeds. Client sites connect with a **license key** only; Instagram API credentials stay on your [Lumina Agency Hub](https://github.com/jhover-mcd/lumina-agency-hub).

## Install on a WordPress site

### Option A — Download ZIP from GitHub

1. Open [Releases](https://github.com/jhover-mcd/lumina-plugin/releases) (or run `scripts/package-plugin.ps1` locally).
2. Download **`lumina-instagram-feed.zip`**.
3. In WordPress admin go to **Plugins → Add New → Upload Plugin**.
4. Upload the ZIP, activate, then open **Lumina Instagram → Settings**.
5. Enter your **License Key** and click **Test Connection**.

### Option B — Clone for development

```bash
git clone https://github.com/jhover-mcd/lumina-plugin.git lumina-instagram-feed
```

Copy the folder into `wp-content/plugins/` (the folder name must be `lumina-instagram-feed`).

## Client configuration

| Setting | Where |
|---------|--------|
| License key | WordPress → Lumina Instagram → Settings |
| Instagram token & User ID | Agency hub → `/manage` |
| Hub URL | Baked into plugin (`https://lumina.mcddigital.biz`) |

Optional — lock the license in `wp-config.php`:

```php
define( 'LUMINA_IG_LICENSE_KEY', 'your-site-license-key' );
```

## Features

- Live and curated feed modes
- Design Studio (layouts, colors, typography)
- Shortcode `[lumina_instagram]` and Gutenberg block
- Hourly cache refresh (live mode) and weekly library refresh (curated mode)

## Development

```bash
composer install
composer test
```

For local hub + WordPress, see `docs/agency-setup.md`.

## Build a release ZIP

```powershell
.\scripts\package-plugin.ps1
```

Output: `dist/lumina-instagram-feed.zip` — ready for WordPress upload.
