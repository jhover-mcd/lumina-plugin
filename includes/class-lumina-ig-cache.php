<?php
/**
 * Feed caching layer.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Cache
 */
class Lumina_IG_Cache {

	const TRANSIENT_PREFIX = 'lumina_ig_feed_';
	const META_KEY           = 'lumina_ig_cache_meta';

	/**
	 * API client.
	 *
	 * @var Lumina_IG_Api
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Api $api API client.
	 */
	public function __construct( Lumina_IG_Api $api ) {
		$this->api = $api;
	}

	/**
	 * Get cached feed or fetch fresh data.
	 *
	 * @param int  $limit   Number of posts.
	 * @param bool $force   Force refresh from API.
	 * @return array|WP_Error
	 */
	public function get_feed( $limit = 12, $force = false ) {
		if ( lumina_ig()->curated->is_active() ) {
			return lumina_ig()->curated->get_display_feed( $limit );
		}

		$key = $this->get_cache_key( $limit );

		if ( ! $force ) {
			$cached = get_transient( $key );

			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		}

		return $this->refresh( $limit );
	}

	/**
	 * Force refresh feed from API and store in cache.
	 *
	 * @param int $limit Number of posts.
	 * @return array|WP_Error
	 */
	public function refresh( $limit = 12 ) {
		$settings = lumina_ig()->settings;
		$limit    = min( 50, max( 1, absint( $limit ) ) );
		$result   = $this->api->fetch_media( $limit );

		if ( is_wp_error( $result ) ) {
			$this->update_meta(
				array(
					'last_error'   => $result->get_error_message(),
					'last_refresh' => current_time( 'mysql' ),
					'status'       => 'error',
				)
			);

			return $result;
		}

		// Handle new API response format with 'items' and 'fetched_at'
		$items      = isset( $result['items'] ) ? $result['items'] : $result;
		$fetched_at = isset( $result['fetched_at'] ) ? $result['fetched_at'] : time();
		
		$ttl = absint( $settings->get( 'cache_ttl', LUMINA_IG_CACHE_TTL ) );
		$key = $this->get_cache_key( $limit );

		// Cache just the items array for backward compatibility
		set_transient( $key, $items, $ttl );

		$this->update_meta(
			array(
				'last_refresh' => current_time( 'mysql' ),
				'next_refresh' => date_i18n( 'Y-m-d H:i:s', time() + $ttl ),
				'fetched_at'   => $fetched_at,
				'item_count'   => count( $items ),
				'status'       => 'ok',
				'last_error'   => '',
			)
		);

		return $items;
	}

	/**
	 * Clear all feed transients.
	 */
	public function flush() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_' . self::TRANSIENT_PREFIX . '%',
				'_transient_timeout_' . self::TRANSIENT_PREFIX . '%'
			)
		);

		delete_option( self::META_KEY );
	}

	/**
	 * Get cache metadata.
	 *
	 * @return array
	 */
	public function get_meta() {
		$meta = get_option( self::META_KEY, array() );

		return wp_parse_args(
			is_array( $meta ) ? $meta : array(),
			array(
				'last_refresh' => '',
				'next_refresh' => '',
				'fetched_at'   => 0,
				'item_count'   => 0,
				'status'       => 'unknown',
				'last_error'   => '',
			)
		);
	}

	/**
	 * Update cache metadata.
	 *
	 * @param array $data Metadata values.
	 */
	private function update_meta( $data ) {
		$meta = $this->get_meta();
		update_option( self::META_KEY, array_merge( $meta, $data ), false );
	}

	/**
	 * Build transient key for a given limit.
	 *
	 * @param int $limit Post count.
	 * @return string
	 */
	private function get_cache_key( $limit ) {
		$license = lumina_ig()->settings->get( 'agency_license_key', 'default' );

		return self::TRANSIENT_PREFIX . md5( $license . '_' . absint( $limit ) );
	}
}
