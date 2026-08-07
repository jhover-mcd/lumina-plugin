# Agency Deployment Guide

## Client site setup

1. Install and activate the plugin
2. Go to **Lumina Instagram → Settings**
3. Enter the **License Key** for this site
4. Save and click **Test Connection**

That is the only connection step on the client site. No access token, hub URL, or username field.

## Agency hub setup

1. Host `agency-hub/` on your server
2. Put your Instagram access token in `config.php`
3. Open `/manage` and create a license key per client site
4. Assign the Instagram User ID for each license on the hub

## Hardcoded hub URL

Edit one line in `lumina-instagram-feed.php` before distributing the plugin:

```php
define( 'LUMINA_IG_AGENCY_HUB_URL', 'https://feeds.youragency.com' );
```

Clients never see or configure this URL.

## Optional: lock the license key via wp-config

```php
define( 'LUMINA_IG_LICENSE_KEY', 'client-site-license-key' );
```

## API authentication

Client plugin requests send the license key in the `X-Lumina-License` header. License keys are not accepted in URL query strings.

## Kill switch

Revoke the license in your hub `/manage` panel. The client feed stops on the next cache refresh.
