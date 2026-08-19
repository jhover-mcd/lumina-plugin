<?php
/**
 * Curated (hand-picked) feed library and selection.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Curated
 */
class Lumina_IG_Curated {

	const LIBRARY_OPTION   = 'lumina_ig_curated_library';
	const SELECTION_OPTION = 'lumina_ig_curated_selection';
	const META_OPTION      = 'lumina_ig_curated_meta';

	const URL_EXPIRATION_DAYS = 5;

	/**
	 * Settings handler.
	 *
	 * @var Lumina_IG_Settings
	 */
	private $settings;

	/**
	 * API client.
	 *
	 * @var Lumina_IG_Api
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Settings $settings Settings handler.
	 * @param Lumina_IG_Api      $api      API client.
	 */
	public function __construct( Lumina_IG_Settings $settings, Lumina_IG_Api $api ) {
		$this->settings = $settings;
		$this->api      = $api;

		self::migrate_legacy_selection();
	}

	/**
	 * Move legacy curated selection out of main settings into its own option.
	 */
	public static function migrate_legacy_selection() {
		$settings = get_option( Lumina_IG_Settings::OPTION_KEY, array() );

		if ( ! is_array( $settings ) || empty( $settings['curated_selected_ids'] ) ) {
			return;
		}

		$legacy = array_values( array_map( 'strval', (array) $settings['curated_selected_ids'] ) );

		if ( null === get_option( self::SELECTION_OPTION, null ) && ! empty( $legacy ) ) {
			update_option( self::SELECTION_OPTION, $legacy, false );
		}

		unset( $settings['curated_selected_ids'] );
		update_option( Lumina_IG_Settings::OPTION_KEY, $settings, false );
	}

	/**
	 * Whether curated mode is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return 'curated' === $this->settings->get( 'feed_mode', 'live' );
	}

	/**
	 * Import posts from the agency hub into the local photo library.
	 *
	 * @param int|null $limit Optional override for import count.
	 * @return array|WP_Error
	 */
	public function sync_library( $limit = null ) {
		if ( null === $limit ) {
			$limit = absint( $this->settings->get( 'curated_fetch_limit', 50 ) );
		}

		$limit  = min( 50, max( 1, absint( $limit ) ) );
		$result = $this->api->fetch_media( $limit );

		if ( is_wp_error( $result ) ) {
			$this->update_meta(
				array(
					'last_sync'  => current_time( 'mysql' ),
					'last_error' => $result->get_error_message(),
					'status'     => 'error',
				)
			);

			return $result;
		}

		$items      = isset( $result['items'] ) ? $result['items'] : $result;
		$fetched_at = isset( $result['fetched_at'] ) ? $result['fetched_at'] : time();
		$library    = $this->get_library();

		foreach ( $items as $item ) {
			$id = isset( $item['id'] ) ? (string) $item['id'] : '';

			if ( '' === $id ) {
				continue;
			}

			$item['id']    = $id;
			$library[ $id ] = $item;
		}

		update_option( self::LIBRARY_OPTION, $library, false );

		$this->update_meta(
			array(
				'last_sync'  => current_time( 'mysql' ),
				'fetched_at' => $fetched_at,
				'item_count' => count( $library ),
				'status'     => 'ok',
				'last_error' => '',
			)
		);

		return $library;
	}

	/**
	 * Get the stored photo library.
	 *
	 * @return array<string, array>
	 */
	public function get_library() {
		$library = get_option( self::LIBRARY_OPTION, array() );

		if ( ! is_array( $library ) ) {
			return array();
		}

		$normalized = array();

		foreach ( $library as $key => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$id = isset( $item['id'] ) ? (string) $item['id'] : (string) $key;

			if ( '' === $id ) {
				continue;
			}

			$item['id'] = $id;
			$normalized[ $id ] = $item;
		}

		return $normalized;
	}

	/**
	 * Get library metadata.
	 *
	 * @return array
	 */
	public function get_meta() {
		$meta = get_option( self::META_OPTION, array() );

		return wp_parse_args(
			is_array( $meta ) ? $meta : array(),
			array(
				'last_sync'  => '',
				'fetched_at' => 0,
				'item_count' => 0,
				'status'     => 'unknown',
				'last_error' => '',
			)
		);
	}

	/**
	 * Check if Instagram URLs need refreshing.
	 *
	 * @return bool
	 */
	public function urls_need_refresh() {
		$meta       = $this->get_meta();
		$fetched_at = isset( $meta['fetched_at'] ) ? absint( $meta['fetched_at'] ) : 0;

		if ( 0 === $fetched_at ) {
			return false;
		}

		$age_days = ( time() - $fetched_at ) / DAY_IN_SECONDS;

		return $age_days > self::URL_EXPIRATION_DAYS;
	}

	/**
	 * Get URL age in days.
	 *
	 * @return float
	 */
	public function get_url_age_days() {
		$meta       = $this->get_meta();
		$fetched_at = isset( $meta['fetched_at'] ) ? absint( $meta['fetched_at'] ) : 0;

		if ( 0 === $fetched_at ) {
			return 0;
		}

		return ( time() - $fetched_at ) / DAY_IN_SECONDS;
	}

	/**
	 * Refresh Instagram media URLs without changing the photo library or selection.
	 *
	 * @param int|null $limit Optional override for import count.
	 * @return array|WP_Error Array with count of updated posts or WP_Error.
	 */
	public function refresh_urls( $limit = null ) {
		if ( null === $limit ) {
			$limit = absint( $this->settings->get( 'curated_fetch_limit', 50 ) );
		}

		$limit  = min( 50, max( 1, absint( $limit ) ) );
		$result = $this->api->refresh_media( $limit );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$fresh_items = isset( $result['items'] ) ? $result['items'] : $result;
		$fetched_at  = isset( $result['fetched_at'] ) ? $result['fetched_at'] : time();
		$library     = $this->get_library();
		$updated     = 0;

		foreach ( $fresh_items as $fresh_item ) {
			$id = isset( $fresh_item['id'] ) ? (string) $fresh_item['id'] : '';

			if ( '' === $id || ! isset( $library[ $id ] ) ) {
				continue;
			}

			$library[ $id ]['image_url'] = $fresh_item['image_url'];
			$library[ $id ]['permalink'] = $fresh_item['permalink'];
			$updated++;
		}

		if ( $updated > 0 ) {
			update_option( self::LIBRARY_OPTION, $library, false );
		}

		$this->update_meta(
			array(
				'fetched_at'    => $fetched_at,
				'last_url_sync' => current_time( 'mysql' ),
			)
		);

		return array(
			'updated'    => $updated,
			'fetched_at' => $fetched_at,
		);
	}

	/**
	 * Save the user's selected post IDs in display order.
	 *
	 * @param array $ids Post IDs.
	 * @return bool
	 */
	public function save_selection( array $ids ) {
		$library = $this->get_library();
		$clean   = array();

		foreach ( $ids as $id ) {
			$id = (string) sanitize_text_field( $id );

			if ( '' === $id || ! isset( $library[ $id ] ) ) {
				continue;
			}

			if ( ! in_array( $id, $clean, true ) ) {
				$clean[] = $id;
			}
		}

		return update_option( self::SELECTION_OPTION, $clean, false );
	}

	/**
	 * Get selected post IDs.
	 *
	 * @return array
	 */
	public function get_selected_ids() {
		$ids = get_option( self::SELECTION_OPTION, array() );

		if ( ! is_array( $ids ) ) {
			return array();
		}

		return array_values( array_map( 'strval', $ids ) );
	}

	/**
	 * Build the frontend feed from the curated selection.
	 *
	 * @param int $limit Maximum posts to return.
	 * @return array|WP_Error
	 */
	public function get_display_feed( $limit = 12 ) {
		// Auto-refresh URLs if they're getting stale
		if ( $this->urls_need_refresh() ) {
			$refresh_result = $this->refresh_urls();

			if ( ! is_wp_error( $refresh_result ) && isset( $refresh_result['updated'] ) ) {
				error_log(
					sprintf(
						'Lumina Instagram Feed: Auto-refreshed %d post URLs (age was %.1f days)',
						$refresh_result['updated'],
						$this->get_url_age_days()
					)
				);
			}
		}

		$library   = $this->get_library();
		$selected  = $this->get_selected_ids();
		$limit     = min( 50, max( 1, absint( $limit ) ) );
		$items     = array();

		if ( empty( $selected ) ) {
			return new WP_Error(
				'lumina_ig_curated_empty',
				__( 'No photos selected for the curated feed. Open Curate Feed to choose posts.', 'lumina-instagram-feed' )
			);
		}

		foreach ( $selected as $id ) {
			$id = (string) $id;

			if ( ! isset( $library[ $id ] ) ) {
				continue;
			}

			$items[] = $library[ $id ];

			if ( count( $items ) >= $limit ) {
				break;
			}
		}

		if ( empty( $items ) ) {
			return new WP_Error(
				'lumina_ig_curated_stale',
				__( 'Selected photos are no longer in the library. Import from Instagram again on the Curate Feed page.', 'lumina-instagram-feed' )
			);
		}

		return $items;
	}

	/**
	 * Remove selected IDs that no longer exist in the library.
	 *
	 * @param array $valid_ids Valid library IDs.
	 */
	private function prune_selection( array $valid_ids ) {
		$selected = $this->get_selected_ids();
		$valid    = array_flip( array_map( 'strval', $valid_ids ) );
		$pruned   = array();

		foreach ( $selected as $id ) {
			$id = (string) $id;

			if ( isset( $valid[ $id ] ) ) {
				$pruned[] = $id;
			}
		}

		if ( $pruned !== $selected ) {
			update_option( self::SELECTION_OPTION, $pruned, false );
		}
	}

	/**
	 * Update curated library metadata.
	 *
	 * @param array $data Metadata values.
	 */
	private function update_meta( array $data ) {
		$meta = $this->get_meta();
		update_option( self::META_OPTION, array_merge( $meta, $data ), false );
	}
}
