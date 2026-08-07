<?php
/**
 * PSR-4 style autoloader for Lumina Instagram Feed.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Autoloader
 */
class Lumina_IG_Autoloader {

	/**
	 * Register the autoloader.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload plugin classes.
	 *
	 * @param string $class Class name.
	 */
	public static function autoload( $class ) {
		if ( 0 !== strpos( $class, 'Lumina_IG_' ) ) {
			return;
		}

		$file = LUMINA_IG_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
