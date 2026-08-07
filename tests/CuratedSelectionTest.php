<?php

use PHPUnit\Framework\TestCase;

class CuratedSelectionTest extends TestCase {
	private function curated_with( array $library, array $settings = array(), array $selection = array() ) {
		lumina_test_reset_options(
			array(
				Lumina_IG_Settings::OPTION_KEY        => wp_parse_args( $settings, Lumina_IG_Settings::get_defaults() ),
				Lumina_IG_Curated::LIBRARY_OPTION     => $library,
				Lumina_IG_Curated::SELECTION_OPTION   => $selection,
			)
		);

		$settings_obj = new Lumina_IG_Settings();
		$api          = new Lumina_IG_Api();

		return new Lumina_IG_Curated( $settings_obj, $api );
	}

	private function sample_item( $id ) {
		return array(
			'id'         => (string) $id,
			'caption'    => 'Caption ' . $id,
			'media_type' => 'IMAGE',
			'image_url'  => 'https://example.com/' . $id . '.jpg',
			'permalink'  => 'https://instagram.com/p/' . $id,
			'timestamp'  => '2026-06-01T12:00:00+0000',
			'username'   => 'demo_user',
			'date'       => 'Jun 1, 2026',
		);
	}

	public function test_save_and_load_selection_preserves_order() {
		$library = array(
			'111' => $this->sample_item( '111' ),
			'222' => $this->sample_item( '222' ),
			'333' => $this->sample_item( '333' ),
			'444' => $this->sample_item( '444' ),
		);

		$curated = $this->curated_with( $library, array( 'feed_mode' => 'curated' ) );
		$curated->save_selection( array( '444', '111', '333' ) );

		$this->assertSame(
			array( '444', '111', '333' ),
			$curated->get_selected_ids()
		);
	}

	public function test_display_feed_respects_post_limit() {
		$library = array(
			'111' => $this->sample_item( '111' ),
			'222' => $this->sample_item( '222' ),
			'333' => $this->sample_item( '333' ),
			'444' => $this->sample_item( '444' ),
		);

		$curated = $this->curated_with(
			$library,
			array( 'feed_mode' => 'curated', 'post_count' => 6 ),
			array( '444', '111', '333', '222' )
		);

		$feed = $curated->get_display_feed( 2 );

		$this->assertIsArray( $feed );
		$this->assertCount( 2, $feed );
		$this->assertSame( '444', $feed[0]['id'] );
		$this->assertSame( '111', $feed[1]['id'] );
	}

	public function test_large_instagram_ids_remain_strings() {
		$id      = '17938725363249516';
		$library = array( $id => $this->sample_item( $id ) );
		$curated = $this->curated_with( $library, array( 'feed_mode' => 'curated' ) );

		$curated->save_selection( array( $id ) );

		$this->assertSame( array( $id ), $curated->get_selected_ids() );
		$this->assertArrayHasKey( $id, $curated->get_library() );
	}

	public function test_sync_merges_library_instead_of_replacing() {
		$existing = array(
			'111' => $this->sample_item( '111' ),
			'222' => $this->sample_item( '222' ),
		);

		$curated = $this->curated_with( $existing, array( 'feed_mode' => 'curated' ) );

		$api = new Lumina_Test_Api_With_Media(
			array(
				array(
					'id'         => '333',
					'caption'    => 'New post',
					'media_type' => 'IMAGE',
					'image_url'  => 'https://example.com/333.jpg',
					'permalink'  => 'https://instagram.com/p/333',
					'timestamp'  => '2026-06-02T12:00:00+0000',
					'username'   => 'demo_user',
					'date'       => 'Jun 2, 2026',
				),
			)
		);

		$settings_obj = new Lumina_IG_Settings();
		$curated      = new Lumina_IG_Curated( $settings_obj, $api );
		lumina_test_reset_options(
			array(
				Lumina_IG_Settings::OPTION_KEY    => array( 'feed_mode' => 'curated', 'curated_fetch_limit' => 50 ),
				Lumina_IG_Curated::LIBRARY_OPTION => $existing,
			)
		);

		$library = $curated->sync_library();

		$this->assertArrayHasKey( '111', $library );
		$this->assertArrayHasKey( '222', $library );
		$this->assertArrayHasKey( '333', $library );
	}

	public function test_legacy_selection_migrates_out_of_settings() {
		lumina_test_reset_options(
			array(
				Lumina_IG_Settings::OPTION_KEY => wp_parse_args(
					array(
						'curated_selected_ids' => array( '111', '222' ),
					),
					Lumina_IG_Settings::get_defaults()
				),
			)
		);

		Lumina_IG_Curated::migrate_legacy_selection();

		$this->assertSame(
			array( '111', '222' ),
			get_option( Lumina_IG_Curated::SELECTION_OPTION )
		);
		$this->assertArrayNotHasKey(
			'curated_selected_ids',
			get_option( Lumina_IG_Settings::OPTION_KEY )
		);
	}
}
