<?php

use PHPUnit\Framework\TestCase;

class SettingsSanitizeTest extends TestCase {
	private function settings_with( array $stored ) {
		lumina_test_reset_options(
			array(
				Lumina_IG_Settings::OPTION_KEY => wp_parse_args(
					$stored,
					Lumina_IG_Settings::get_defaults()
				),
			)
		);

		return new Lumina_IG_Settings();
	}

	public function test_design_save_preserves_feed_settings() {
		$settings = $this->settings_with(
			array(
				'agency_license_key' => 'client-key',
				'post_count'         => 6,
				'feed_mode'          => 'curated',
				'show_caption'       => true,
				'layout'             => 'grid',
			)
		);

		$result = $settings->sanitize(
			array(
				'layout'             => 'masonry',
				'columns'            => 3,
				'agency_license_key' => 'client-key',
			)
		);

		$this->assertSame( 'masonry', $result['layout'] );
		$this->assertSame( 6, $result['post_count'] );
		$this->assertSame( 'curated', $result['feed_mode'] );
		$this->assertSame( 'client-key', $result['agency_license_key'] );
		$this->assertTrue( $result['show_caption'] );
	}

	public function test_settings_save_preserves_design_choices() {
		$settings = $this->settings_with(
			array(
				'layout'       => 'bento',
				'hover_effect' => 'zoom',
				'post_count'   => 12,
			)
		);

		$result = $settings->sanitize(
			array(
				'post_count'    => 6,
				'feed_mode'     => 'curated',
				'show_caption'  => '1',
				'show_image'    => '1',
				'show_date'     => '1',
				'show_link'     => '1',
			)
		);

		$this->assertSame( 6, $result['post_count'] );
		$this->assertSame( 'curated', $result['feed_mode'] );
		$this->assertSame( 'bento', $result['layout'] );
		$this->assertSame( 'zoom', $result['hover_effect'] );
	}
}
