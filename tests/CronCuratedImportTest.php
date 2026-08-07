<?php

use PHPUnit\Framework\TestCase;

class CronCuratedImportTest extends TestCase {
	public function test_weekly_import_runs_only_in_curated_mode() {
		lumina_test_reset_options(
			array(
				Lumina_IG_Settings::OPTION_KEY => wp_parse_args(
					array(
						'agency_license_key'  => 'client-key',
						'feed_mode'           => 'curated',
						'curated_fetch_limit' => 50,
					),
					Lumina_IG_Settings::get_defaults()
				),
			)
		);

		$api = new Lumina_Test_Api_With_Media(
			array(
				array(
					'id'         => '111',
					'caption'    => 'Weekly refresh',
					'media_type' => 'IMAGE',
					'image_url'  => 'https://example.com/111.jpg',
					'permalink'  => 'https://instagram.com/p/111',
					'timestamp'  => '2026-06-01T12:00:00+0000',
					'username'   => 'demo_user',
					'date'       => 'Jun 1, 2026',
				),
			)
		);

		$settings = new Lumina_IG_Settings();
		lumina_test_set_plugin( $settings, $api );
		$cache    = new Lumina_IG_Cache( $api );
		$cron     = new Lumina_IG_Cron( $cache );

		$cron->run_curated_import();

		$this->assertTrue( $api->called );
		$this->assertArrayHasKey( '111', lumina_ig()->curated->get_library() );
	}

	public function test_weekly_import_skips_live_mode() {
		lumina_test_reset_options(
			array(
				Lumina_IG_Settings::OPTION_KEY => wp_parse_args(
					array(
						'agency_license_key' => 'client-key',
						'feed_mode'          => 'live',
					),
					Lumina_IG_Settings::get_defaults()
				),
			)
		);

		$api      = new Lumina_Test_Api_With_Media( array() );
		$settings = new Lumina_IG_Settings();
		lumina_test_set_plugin( $settings, $api );
		$cache = new Lumina_IG_Cache( $api );
		$cron  = new Lumina_IG_Cron( $cache );
		$cron->run_curated_import();

		$this->assertFalse( $api->called );
	}

	public function test_ensure_scheduled_registers_weekly_hook() {
		$GLOBALS['lumina_test_cron'] = array();
		Lumina_IG_Cron::ensure_scheduled();

		$this->assertNotFalse( wp_next_scheduled( Lumina_IG_Cron::CURATED_HOOK ) );
		$this->assertNotFalse( wp_next_scheduled( Lumina_IG_Cron::HOOK ) );
	}
}
