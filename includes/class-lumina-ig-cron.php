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

	const HOOK             = 'lumina_ig_hourly_refresh';
	const CURATED_HOOK     = 'lumina_ig_weekly_curated_import';
	const TOKEN_HOOK       = 'lumina_ig_daily_token_keepalive';
	const CURATED_SCHEDULE = 'lumina_ig_three_days';

	/**
	 * Three days. Instagram CDN URLs expire in about five days, so refresh before that.
	 */
	const CURATED_INTERVAL = 259200;

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

		add_filter( 'cron_schedules', array( __CLASS__, 'add_schedules' ) );
		add_action( self::HOOK, array( $this, 'run_live_refresh' ) );
		add_action( self::CURATED_HOOK, array( $this, 'run_curated_import' ) );
		add_action( self::TOKEN_HOOK, array( $this, 'run_token_keepalive' ) );
		add_action( 'init', array( __CLASS__, 'ensure_scheduled' ) );
	}

	/**
	 * Register the curated-feed refresh interval.
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array
	 */
	public static function add_schedules( $schedules ) {
		if ( ! is_array( $schedules ) ) {
			$schedules = array();
		}

		$schedules[ self::CURATED_SCHEDULE ] = array(
			'interval' => self::CURATED_INTERVAL,
			'display'  => __( 'Every 3 days', 'lumina-instagram-feed' ),
		);

		return $schedules;
	}

	/**
	 * Ensure cron events are registered on existing installs.
	 */
	public static function ensure_scheduled() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), 'hourly', self::HOOK );
		}

		if ( ! wp_next_scheduled( self::TOKEN_HOOK ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'daily', self::TOKEN_HOOK );
		}

		self::ensure_curated_scheduled();
	}

	/**
	 * Keep the curated import on a 3-day interval.
	 *
	 * Sites that already have the old weekly event are rescheduled immediately
	 * so image URLs are refreshed on the next visit instead of waiting out the week.
	 */
	public static function ensure_curated_scheduled() {
		$next     = wp_next_scheduled( self::CURATED_HOOK );
		$schedule = wp_get_schedule( self::CURATED_HOOK );

		if ( $next && self::CURATED_SCHEDULE === $schedule ) {
			return;
		}

		while ( $next ) {
			wp_unschedule_event( $next, self::CURATED_HOOK );
			$next = wp_next_scheduled( self::CURATED_HOOK );
		}

		wp_schedule_event( time(), self::CURATED_SCHEDULE, self::CURATED_HOOK );
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
		$hooks = array( self::HOOK, self::CURATED_HOOK, self::TOKEN_HOOK );

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
	 * Ask the hub to renew this site's Instagram token.
	 *
	 * Live feeds already hit the hub hourly. Curated sites may not, so this
	 * daily ping keeps the long-lived token from expiring after 60 days.
	 */
	public function run_token_keepalive() {
		if ( ! lumina_ig()->settings->is_configured() ) {
			return;
		}

		lumina_ig()->api->test_connection();
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
