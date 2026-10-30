<?php
/**
 * GitHub Plugin Updater
 * 
 * Checks GitHub releases for plugin updates and integrates with WordPress update system.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_GitHub_Updater
 */
class Lumina_IG_GitHub_Updater {

	/**
	 * GitHub repository owner/name.
	 *
	 * @var string
	 */
	private $repo = 'jhover-mcd/lumina-plugin';

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Plugin data.
	 *
	 * @var array
	 */
	private $plugin_data;

	/**
	 * GitHub API response cache.
	 *
	 * @var object|null
	 */
	private $github_response;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file Plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = plugin_basename( $plugin_file );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Get plugin data.
	 *
	 * @return array
	 */
	private function get_plugin_data() {
		if ( ! $this->plugin_data ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$this->plugin_data = get_plugin_data( $this->plugin_file );
		}

		return $this->plugin_data;
	}

	/**
	 * Get latest release info from GitHub.
	 *
	 * @return object|false
	 */
	private function get_github_response() {
		if ( null !== $this->github_response ) {
			return $this->github_response;
		}

		$transient_key = 'lumina_ig_github_release';
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			$this->github_response = $cached;
			return $cached;
		}

		$url      = "https://api.github.com/repos/{$this->repo}/releases/latest";
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/vnd.github.v3+json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->github_response = false;
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( ! $data || ! isset( $data->tag_name ) ) {
			$this->github_response = false;
			return false;
		}

		set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );
		$this->github_response = $data;

		return $data;
	}

	/**
	 * Check for plugin updates.
	 *
	 * @param object $transient Update transient.
	 * @return object
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_github_response();

		if ( ! $release ) {
			return $transient;
		}

		$plugin_data    = $this->get_plugin_data();
		$current_version = $plugin_data['Version'];
		$new_version     = ltrim( $release->tag_name, 'v' );

		if ( version_compare( $current_version, $new_version, '<' ) ) {
			$plugin = array(
				'slug'        => dirname( $this->plugin_slug ),
				'plugin'      => $this->plugin_slug,
				'new_version' => $new_version,
				'url'         => $plugin_data['PluginURI'],
				'package'     => $this->get_download_url( $release ),
				'tested'      => $this->get_tested_version(),
				'icons'       => array(),
			);

			$transient->response[ $this->plugin_slug ] = (object) $plugin;
		}

		return $transient;
	}

	/**
	 * Get download URL for the release.
	 *
	 * @param object $release GitHub release object.
	 * @return string
	 */
	private function get_download_url( $release ) {
		// Check for attached .zip asset first
		if ( ! empty( $release->assets ) ) {
			foreach ( $release->assets as $asset ) {
				if ( preg_match( '/\.zip$/i', $asset->name ) ) {
					return $asset->browser_download_url;
				}
			}
		}

		// Fallback to zipball URL
		return $release->zipball_url;
	}

	/**
	 * Get tested WordPress version.
	 *
	 * @return string
	 */
	private function get_tested_version() {
		global $wp_version;
		return $wp_version;
	}

	/**
	 * Provide plugin information for the view details modal.
	 *
	 * @param false|object|array $result Result object.
	 * @param string             $action Action type.
	 * @param object             $args   Query arguments.
	 * @return false|object
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || $args->slug !== dirname( $this->plugin_slug ) ) {
			return $result;
		}

		$release = $this->get_github_response();

		if ( ! $release ) {
			return $result;
		}

		$plugin_data = $this->get_plugin_data();

		$info = array(
			'name'          => $plugin_data['Name'],
			'slug'          => dirname( $this->plugin_slug ),
			'version'       => ltrim( $release->tag_name, 'v' ),
			'author'        => $plugin_data['Author'],
			'homepage'      => $plugin_data['PluginURI'],
			'requires'      => $plugin_data['RequiresWP'] ?? '6.0',
			'tested'        => $this->get_tested_version(),
			'downloaded'    => 0,
			'last_updated'  => $release->published_at,
			'sections'      => array(
				'description' => $plugin_data['Description'],
				'changelog'   => $this->parse_changelog( $release->body ),
			),
			'download_link' => $this->get_download_url( $release ),
		);

		return (object) $info;
	}

	/**
	 * Parse changelog from release notes.
	 *
	 * @param string $body Release body text.
	 * @return string
	 */
	private function parse_changelog( $body ) {
		if ( empty( $body ) ) {
			return '<p>See <a href="https://github.com/' . esc_attr( $this->repo ) . '/releases" target="_blank">GitHub releases</a> for details.</p>';
		}

		// Convert markdown to basic HTML
		$html = wpautop( $body );
		$html = preg_replace( '/^### (.+)$/m', '<h3>$1</h3>', $html );
		$html = preg_replace( '/^## (.+)$/m', '<h2>$1</h2>', $html );
		$html = preg_replace( '/^\* (.+)$/m', '<li>$1</li>', $html );
		$html = preg_replace( '/(<li>.*<\/li>)+/s', '<ul>$0</ul>', $html );

		return $html;
	}

	/**
	 * Clean up after installation.
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments.
	 * @param array $result     Installation result.
	 * @return bool
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
			return $response;
		}

		// Move files from subdirectory if needed (GitHub zips have a top-level folder)
		$plugin_folder = WP_PLUGIN_DIR . '/' . dirname( $this->plugin_slug );
		$wp_filesystem->move( $result['destination'], $plugin_folder, true );
		$result['destination'] = $plugin_folder;

		activate_plugin( $this->plugin_slug );

		return $response;
	}
}
