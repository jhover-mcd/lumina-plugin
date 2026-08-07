<?php
/**
 * Minimal WordPress stubs for plugin unit tests.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! defined( 'LUMINA_IG_CACHE_TTL' ) ) {
	define( 'LUMINA_IG_CACHE_TTL', 3600 );
}

if ( ! defined( 'LUMINA_IG_PLUGIN_DIR' ) ) {
	define( 'LUMINA_IG_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'LUMINA_IG_AGENCY_HUB_URL' ) ) {
	define( 'LUMINA_IG_AGENCY_HUB_URL', 'https://feeds.example.com' );
}

$GLOBALS['lumina_test_options'] = array();

function lumina_test_reset_options( array $options = array() ) {
	$GLOBALS['lumina_test_options'] = $options;
}

function get_option( $option, $default = false ) {
	return array_key_exists( $option, $GLOBALS['lumina_test_options'] )
		? $GLOBALS['lumina_test_options'][ $option ]
		: $default;
}

function update_option( $option, $value, $autoload = null ) {
	$GLOBALS['lumina_test_options'][ $option ] = $value;
	return true;
}

function delete_option( $option ) {
	unset( $GLOBALS['lumina_test_options'][ $option ] );
	return true;
}

function wp_parse_args( $args, $defaults = array() ) {
	if ( ! is_array( $args ) ) {
		return $defaults;
	}

	return array_merge( $defaults, $args );
}

function absint( $value ) {
	return abs( (int) $value );
}

function sanitize_text_field( $value ) {
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

function sanitize_hex_color( $value ) {
	$value = (string) $value;
	return preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $value ) ? $value : false;
}

function wp_strip_all_tags( $value ) {
	return strip_tags( (string) $value );
}

function current_time( $type ) {
	return 'mysql' === $type ? '2026-06-15 12:00:00' : time();
}

function esc_url_raw( $url ) {
	return (string) $url;
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function wp_kses_post( $value ) {
	return (string) $value;
}

function date_i18n( $format, $timestamp = false ) {
	return gmdate( $format, $timestamp ? (int) $timestamp : time() );
}

function __ ( $text, $domain = null ) {
	return $text;
}

function _e( $text, $domain = null ) {
	echo $text;
}

class WP_Error {
	private $code;
	private $message;

	public function __construct( $code = '', $message = '', $data = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}

	public function get_error_message() {
		return $this->message;
	}
}

function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

$GLOBALS['lumina_test_cron'] = array();

function wp_next_scheduled( $hook ) {
	return $GLOBALS['lumina_test_cron'][ $hook ] ?? false;
}

function wp_schedule_event( $timestamp, $recurrence, $hook ) {
	$GLOBALS['lumina_test_cron'][ $hook ] = $timestamp;
	return true;
}

function wp_unschedule_event( $timestamp, $hook ) {
	unset( $GLOBALS['lumina_test_cron'][ $hook ] );
	return true;
}

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	return true;
}

function lumina_test_set_plugin( Lumina_IG_Settings $settings, Lumina_IG_Api $api ) {
	$GLOBALS['lumina_test_plugin'] = (object) array(
		'settings' => $settings,
		'curated'  => new Lumina_IG_Curated( $settings, $api ),
	);
}

function lumina_ig() {
	if ( empty( $GLOBALS['lumina_test_plugin'] ) ) {
		throw new RuntimeException( 'lumina_test_set_plugin() must be called before lumina_ig().' );
	}

	return $GLOBALS['lumina_test_plugin'];
}

require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-config.php';
require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-cache.php';
require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-cron.php';
require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-settings.php';
require_once LUMINA_IG_PLUGIN_DIR . 'includes/class-lumina-ig-curated.php';

class Lumina_IG_Api {
	public function fetch_media( $limit = 12 ) {
		return array();
	}
}

class Lumina_Test_Api_With_Media extends Lumina_IG_Api {
	public $called = false;

	/**
	 * @var array
	 */
	private $media;

	public function __construct( array $media ) {
		$this->media = $media;
	}

	public function fetch_media( $limit = 12 ) {
		$this->called = true;
		return $this->media;
	}
}
