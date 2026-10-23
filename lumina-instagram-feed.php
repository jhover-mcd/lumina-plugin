<?php
/**
 * Plugin Name:       Lumina Instagram Feed
 * Plugin URI:        https://github.com/jhover-mcd/lumina-plugin
 * Description:       A fully customizable Instagram feed with a powerful design engine, hourly API caching, and flexible field controls.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Lumina
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lumina-instagram-feed
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

define( 'LUMINA_IG_VERSION', '1.1.0' );
define( 'LUMINA_IG_PLUGIN_FILE', __FILE__ );
define( 'LUMINA_IG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LUMINA_IG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LUMINA_IG_CACHE_TTL', HOUR_IN_SECONDS );

/**
 * Agency hub URL — edit the default below before distributing to client sites.
 * Local dev can use agency-hub-url.local.php (see lumina-wp-local in this repo).
 */
if ( file_exists( LUMINA_IG_PLUGIN_DIR . 'agency-hub-url.local.php' ) ) {
	require LUMINA_IG_PLUGIN_DIR . 'agency-hub-url.local.php';
}

if ( ! defined( 'LUMINA_IG_AGENCY_HUB_URL' ) ) {
	$lumina_ig_hub_url = getenv( 'LUMINA_IG_AGENCY_HUB_URL' );

	if ( ! $lumina_ig_hub_url && ! empty( $_SERVER['LUMINA_IG_AGENCY_HUB_URL'] ) ) {
		$lumina_ig_hub_url = $_SERVER['LUMINA_IG_AGENCY_HUB_URL'];
	}

	define( 'LUMINA_IG_AGENCY_HUB_URL', $lumina_ig_hub_url ? $lumina_ig_hub_url : 'https://lumina.mcddigital.biz' );
}

require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-autoloader.php';
Lumina_IG_Autoloader::register();

// Initialize GitHub updater for automatic updates
if ( is_admin() ) {
	require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-github-updater.php';
	new Lumina_IG_GitHub_Updater( __FILE__ );
}

/**
 * Returns the main plugin instance.
 *
 * @return Lumina_IG_Plugin
 */
function lumina_ig() {
	return Lumina_IG_Plugin::instance();
}

lumina_ig();

register_activation_hook( __FILE__, array( 'Lumina_IG_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Lumina_IG_Plugin', 'deactivate' ) );
