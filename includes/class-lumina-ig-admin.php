<?php
/**
 * Admin settings pages and actions.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Admin
 */
class Lumina_IG_Admin {

	/**
	 * Settings handler.
	 *
	 * @var Lumina_IG_Settings
	 */
	private $settings;

	/**
	 * Cache handler.
	 *
	 * @var Lumina_IG_Cache
	 */
	private $cache;

	/**
	 * Design engine.
	 *
	 * @var Lumina_IG_Design_Engine
	 */
	private $design;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Settings      $settings Settings handler.
	 * @param Lumina_IG_Cache         $cache    Cache handler.
	 * @param Lumina_IG_Design_Engine $design   Design engine.
	 */
	public function __construct( Lumina_IG_Settings $settings, Lumina_IG_Cache $cache, Lumina_IG_Design_Engine $design ) {
		$this->settings = $settings;
		$this->cache    = $cache;
		$this->design   = $design;

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_lumina_ig_refresh_cache', array( $this, 'handle_refresh' ) );
		add_action( 'admin_post_lumina_ig_sync_library', array( $this, 'handle_sync_library' ) );
		add_action( 'admin_post_lumina_ig_save_curated', array( $this, 'handle_save_curated' ) );
		add_action( 'admin_post_lumina_ig_test_connection', array( $this, 'handle_test_connection' ) );
		add_action( 'wp_ajax_lumina_ig_preview', array( $this, 'ajax_preview' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Lumina Instagram', 'lumina-instagram-feed' ),
			__( 'Lumina Instagram', 'lumina-instagram-feed' ),
			'manage_options',
			'lumina-instagram-feed',
			array( $this, 'render_settings_page' ),
			'dashicons-instagram',
			58
		);

		add_submenu_page(
			'lumina-instagram-feed',
			__( 'Settings', 'lumina-instagram-feed' ),
			__( 'Settings', 'lumina-instagram-feed' ),
			'manage_options',
			'lumina-instagram-feed',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'lumina-instagram-feed',
			__( 'Curate Feed', 'lumina-instagram-feed' ),
			__( 'Curate Feed', 'lumina-instagram-feed' ),
			'manage_options',
			'lumina-instagram-curate',
			array( $this, 'render_curate_page' )
		);

		add_submenu_page(
			'lumina-instagram-feed',
			__( 'Design Studio', 'lumina-instagram-feed' ),
			__( 'Design Studio', 'lumina-instagram-feed' ),
			'manage_options',
			'lumina-instagram-design',
			array( $this, 'render_design_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'lumina_ig_settings_group',
			Lumina_IG_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => Lumina_IG_Settings::get_defaults(),
			)
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'lumina-instagram' ) ) {
			return;
		}

		wp_enqueue_style(
			'lumina-ig-admin',
			LUMINA_IG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			LUMINA_IG_VERSION
		);

		wp_enqueue_style( 'lumina-ig-feed', LUMINA_IG_PLUGIN_URL . 'assets/css/feed.css', array(), LUMINA_IG_VERSION );

		wp_enqueue_script(
			'lumina-ig-admin',
			LUMINA_IG_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			LUMINA_IG_VERSION,
			true
		);

		wp_localize_script(
			'lumina-ig-admin',
			'luminaIgAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'lumina_ig_preview' ),
			)
		);

		if ( false !== strpos( $hook, 'lumina-instagram-curate' ) ) {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script(
				'lumina-ig-curate',
				LUMINA_IG_PLUGIN_URL . 'assets/js/curate.js',
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ),
				LUMINA_IG_VERSION,
				true
			);
		}
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->settings->all();
		$meta     = $this->cache->get_meta();
		$notice   = isset( $_GET['lumina_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['lumina_notice'] ) ) : '';

		include LUMINA_IG_PLUGIN_DIR . 'templates/admin/settings.php';
	}

	/**
	 * Render curated feed picker page.
	 */
	public function render_curate_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings  = $this->settings->all();
		$library   = lumina_ig()->curated->get_library();
		$selected  = lumina_ig()->curated->get_selected_ids();
		$meta      = lumina_ig()->curated->get_meta();
		$notice    = isset( $_GET['lumina_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['lumina_notice'] ) ) : '';

		include LUMINA_IG_PLUGIN_DIR . 'templates/admin/curate.php';
	}

	/**
	 * Render design studio page.
	 */
	public function render_design_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->settings->all();
		$layouts  = $this->design->get_layouts();
		$groups   = $this->design->get_design_fields();

		include LUMINA_IG_PLUGIN_DIR . 'templates/admin/design.php';
	}

	/**
	 * Handle manual cache refresh.
	 */
	public function handle_refresh() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'lumina-instagram-feed' ) );
		}

		check_admin_referer( 'lumina_ig_refresh_cache' );

		if ( lumina_ig()->curated->is_active() ) {
			lumina_ig()->curated->sync_library();
			$redirect_page = 'lumina-instagram-curate';
			$notice        = 'library_synced';
		} else {
			$limit = absint( $this->settings->get( 'post_count', 12 ) );
			$this->cache->refresh( $limit );
			$redirect_page = 'lumina-instagram-feed';
			$notice        = 'refreshed';
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => $redirect_page,
					'lumina_notice' => $notice,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Import Instagram posts into the curated library.
	 */
	public function handle_sync_library() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'lumina-instagram-feed' ) );
		}

		check_admin_referer( 'lumina_ig_sync_library' );

		$result = lumina_ig()->curated->sync_library();
		$notice = is_wp_error( $result ) ? 'library_sync_failed' : 'library_synced';

		if ( is_wp_error( $result ) ) {
			set_transient( 'lumina_ig_library_error', $result->get_error_message(), 60 );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => 'lumina-instagram-curate',
					'lumina_notice' => $notice,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Save curated photo selection.
	 */
	public function handle_save_curated() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'lumina-instagram-feed' ) );
		}

		check_admin_referer( 'lumina_ig_save_curated' );

		if ( ! lumina_ig()->curated->is_active() ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'          => 'lumina-instagram-curate',
						'lumina_notice' => 'curated_mode_required',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		$ids = isset( $_POST['curated_selected'] ) ? wp_unslash( $_POST['curated_selected'] ) : array();

		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		lumina_ig()->curated->save_selection( $ids );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => 'lumina-instagram-curate',
					'lumina_notice' => 'curated_saved',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Handle API connection test.
	 */
	public function handle_test_connection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'lumina-instagram-feed' ) );
		}

		check_admin_referer( 'lumina_ig_test_connection' );

		$result = lumina_ig()->api->test_connection();

		if ( is_wp_error( $result ) ) {
			$notice = 'test_failed';
			set_transient( 'lumina_ig_test_error', $result->get_error_message(), 60 );
		} else {
			$notice = 'test_ok';
			set_transient( 'lumina_ig_test_result', $result, 60 );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => 'lumina-instagram-feed',
					'lumina_notice' => $notice,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * AJAX live preview for design studio.
	 */
	public function ajax_preview() {
		check_ajax_referer( 'lumina_ig_preview', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'lumina-instagram-feed' ) ) );
		}

		$overrides = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();

		if ( ! is_array( $overrides ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid preview data.', 'lumina-instagram-feed' ) ) );
		}

		$html = lumina_ig()->renderer->render( $overrides );

		wp_send_json_success( array( 'html' => $html ) );
	}
}
