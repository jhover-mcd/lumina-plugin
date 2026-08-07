<?php

/**

 * Scheduled cache and curated library refresh.

 *

 * @package LuminaInstagramFeed

 */



defined( 'ABSPATH' ) || exit;



/**

 * Class Lumina_IG_Cron

 */

class Lumina_IG_Cron {



	const HOOK          = 'lumina_ig_hourly_refresh';

	const CURATED_HOOK  = 'lumina_ig_weekly_curated_import';



	/**

	 * Cache handler.

	 *

	 * @var Lumina_IG_Cache

	 */

	private $cache;



	/**

	 * Constructor.

	 *

	 * @param Lumina_IG_Cache $cache Cache handler.

	 */

	public function __construct( Lumina_IG_Cache $cache ) {

		$this->cache = $cache;

		add_action( self::HOOK, array( $this, 'run_live_refresh' ) );

		add_action( self::CURATED_HOOK, array( $this, 'run_curated_import' ) );

		add_action( 'init', array( __CLASS__, 'ensure_scheduled' ) );

	}



	/**

	 * Ensure cron events are registered on existing installs.

	 */

	public static function ensure_scheduled() {

		if ( ! wp_next_scheduled( self::HOOK ) ) {

			wp_schedule_event( time(), 'hourly', self::HOOK );

		}



		if ( ! wp_next_scheduled( self::CURATED_HOOK ) ) {

			wp_schedule_event( time(), 'weekly', self::CURATED_HOOK );

		}

	}



	/**

	 * Schedule all cron events.

	 */

	public static function schedule_events() {

		self::ensure_scheduled();

	}



	/**

	 * Clear all cron events.

	 */

	public static function clear_events() {

		$hooks = array( self::HOOK, self::CURATED_HOOK );



		foreach ( $hooks as $hook ) {

			$timestamp = wp_next_scheduled( $hook );



			while ( $timestamp ) {

				wp_unschedule_event( $timestamp, $hook );

				$timestamp = wp_next_scheduled( $hook );

			}

		}

	}



	/**

	 * Refresh live feed cache on schedule.

	 */

	public function run_live_refresh() {

		$settings = lumina_ig()->settings;



		if ( ! $settings->is_configured() || lumina_ig()->curated->is_active() ) {

			return;

		}



		$limit = absint( $settings->get( 'post_count', 12 ) );

		$this->cache->refresh( $limit );

	}



	/**

	 * Refresh curated library image URLs on schedule.

	 */

	public function run_curated_import() {

		$settings = lumina_ig()->settings;



		if ( ! $settings->is_configured() || ! lumina_ig()->curated->is_active() ) {

			return;

		}



		lumina_ig()->curated->sync_library();

	}

}


