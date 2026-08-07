<?php
/**
 * Main plugin bootstrap.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Plugin
 */
class Lumina_IG_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Lumina_IG_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Settings handler.
	 *
	 * @var Lumina_IG_Settings
	 */
	public $settings;

	/**
	 * API client.
	 *
	 * @var Lumina_IG_Api
	 */
	public $api;

	/**
	 * Cache handler.
	 *
	 * @var Lumina_IG_Cache
	 */
	public $cache;

	/**
	 * Design engine.
	 *
	 * @var Lumina_IG_Design_Engine
	 */
	public $design;

	/**
	 * Curated feed handler.
	 *
	 * @var Lumina_IG_Curated
	 */
	public $curated;

	/**
	 * Feed renderer.
	 *
	 * @var Lumina_IG_Renderer
	 */
	public $renderer;

	/**
	 * Get singleton instance.
	 *
	 * @return Lumina_IG_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = new Lumina_IG_Settings();
		$this->api      = new Lumina_IG_Api( $this->settings );
		$this->cache    = new Lumina_IG_Cache( $this->api );
		$this->design   = new Lumina_IG_Design_Engine( $this->settings );
		$this->curated  = new Lumina_IG_Curated( $this->settings, $this->api );
		$this->renderer = new Lumina_IG_Renderer( $this->cache, $this->design );

		new Lumina_IG_Admin( $this->settings, $this->cache, $this->design );
		new Lumina_IG_Cron( $this->cache );
		new Lumina_IG_Shortcode( $this->renderer );
		new Lumina_IG_Block( $this->renderer, $this->design );

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_feed_assets' ) );
	}

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		Lumina_IG_Cron::schedule_events();

		$defaults = Lumina_IG_Settings::get_defaults();
		$current  = get_option( Lumina_IG_Settings::OPTION_KEY, array() );

		if ( empty( $current ) ) {
			update_option( Lumina_IG_Settings::OPTION_KEY, $defaults );
		}
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate() {
		Lumina_IG_Cron::clear_events();
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'lumina-instagram-feed',
			false,
			dirname( plugin_basename( LUMINA_IG_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Register frontend assets.
	 */
	public function register_assets() {
		wp_register_style(
			'lumina-ig-feed',
			LUMINA_IG_PLUGIN_URL . 'assets/css/feed.css',
			array(),
			LUMINA_IG_VERSION
		);

		wp_register_script(
			'lumina-ig-feed',
			LUMINA_IG_PLUGIN_URL . 'assets/js/feed.js',
			array(),
			LUMINA_IG_VERSION,
			true
		);
	}

	/**
	 * Enqueue feed assets early when the current page contains the feed.
	 */
	public function maybe_enqueue_feed_assets() {
		if ( is_admin() ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$has_feed = has_shortcode( $post->post_content, 'lumina_instagram' );

		if ( ! $has_feed && function_exists( 'has_block' ) ) {
			$has_feed = has_block( 'lumina/instagram-feed', $post );
		}

		if ( $has_feed ) {
			wp_enqueue_style( 'lumina-ig-feed' );
			wp_enqueue_script( 'lumina-ig-feed' );
		}
	}
}
