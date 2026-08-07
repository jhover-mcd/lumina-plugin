<?php
/**
 * Agency hub URL and optional locked license key.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Config
 */
class Lumina_IG_Config {

	/**
	 * Hardcoded agency hub URL for all client sites.
	 *
	 * Change this once when you distribute the plugin to clients.
	 * Local Docker can override via the LUMINA_IG_AGENCY_HUB_URL environment variable.
	 *
	 * @return string
	 */
	public static function hub_url() {
		if ( defined( 'LUMINA_IG_AGENCY_HUB_URL' ) ) {
			return (string) LUMINA_IG_AGENCY_HUB_URL;
		}

		$from_env = getenv( 'LUMINA_IG_AGENCY_HUB_URL' );

		if ( $from_env ) {
			return (string) $from_env;
		}

		return 'https://feeds.youragency.com';
	}

	/**
	 * Resolve license key from wp-config lock or saved settings.
	 *
	 * @param string $stored Stored option value.
	 * @return string
	 */
	public static function license_key( $stored = '' ) {
		if ( defined( 'LUMINA_IG_LICENSE_KEY' ) ) {
			return (string) LUMINA_IG_LICENSE_KEY;
		}

		return $stored;
	}

	/**
	 * Whether the license key is locked via wp-config.
	 *
	 * @return bool
	 */
	public static function is_license_locked() {
		return defined( 'LUMINA_IG_LICENSE_KEY' );
	}
}
