<?php
/**
 * Plugin settings and feed configuration.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Settings
 */
class Lumina_IG_Settings {

	const OPTION_KEY = 'lumina_ig_settings';

	/**
	 * Cached settings array.
	 *
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'agency_license_key'  => '',
			'feed_mode'           => 'live',
			'curated_fetch_limit' => 50,
			'cache_ttl'           => LUMINA_IG_CACHE_TTL,
			'post_count'         => 12,
			'show_image'         => true,
			'show_caption'       => true,
			'show_date'          => true,
			'show_account'       => false,
			'show_link'          => true,
			'open_in_new_tab'    => true,
			'layout'             => 'grid',
			'columns'            => 4,
			'columns_tablet'     => 3,
			'columns_mobile'     => 2,
			'gap'                => 16,
			'aspect_ratio'       => 'square',
			'border_radius'      => 12,
			'card_style'         => 'elevated',
			'hover_effect'       => 'overlay-slide',
			'image_fit'          => 'cover',
			'color_bg'           => '#ffffff',
			'color_text'         => '#1a1a2e',
			'color_muted'        => '#6b7280',
			'color_accent'       => '#e1306c',
			'color_overlay'      => 'rgba(0,0,0,0.55)',
			'font_caption'       => 'inherit',
			'font_meta'          => 'inherit',
			'caption_size'       => 14,
			'meta_size'          => 12,
			'caption_lines'      => 3,
			'shadow'             => 'md',
			'animation'          => 'stagger-fade',
			'carousel_autoplay'  => false,
			'carousel_speed'     => 4000,
			'bento_pattern'      => 'classic',
			'custom_css'         => '',
		);
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array
	 */
	public function all() {
		if ( null === $this->settings ) {
			$stored         = get_option( self::OPTION_KEY, array() );
			$this->settings = wp_parse_args( is_array( $stored ) ? $stored : array(), self::get_defaults() );
			$this->settings['agency_license_key'] = Lumina_IG_Config::license_key( $this->settings['agency_license_key'] ?? '' );
		}

		return $this->settings;
	}

	/**
	 * Get a single setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		$all = $this->all();

		return isset( $all[ $key ] ) ? $all[ $key ] : $default;
	}

	/**
	 * Update settings.
	 *
	 * @param array $values Settings to merge.
	 * @return bool
	 */
	public function update( $values ) {
		$merged         = wp_parse_args( $values, $this->all() );
		$this->settings = $this->sanitize( $merged );

		return update_option( self::OPTION_KEY, $this->settings );
	}

	/**
	 * Sanitize settings input.
	 *
	 * Each admin form only submits its own fields. Values not present in the
	 * submitted input are preserved from the existing saved settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$defaults = self::get_defaults();
		$stored   = get_option( self::OPTION_KEY, array() );
		$current  = wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
		$current['agency_license_key'] = Lumina_IG_Config::license_key( $current['agency_license_key'] ?? '' );
		$output   = $current;

		$is_settings_save = array_key_exists( 'post_count', $input )
			|| array_key_exists( 'cache_ttl', $input );

		$is_design_save = array_key_exists( 'layout', $input )
			|| array_key_exists( 'custom_css', $input );

		if ( array_key_exists( 'agency_license_key', $input ) && ! Lumina_IG_Config::is_license_locked() ) {
			$output['agency_license_key'] = sanitize_text_field( $input['agency_license_key'] );
		}

		if ( $is_settings_save ) {
			if ( array_key_exists( 'cache_ttl', $input ) ) {
				$output['cache_ttl'] = max( 300, absint( $input['cache_ttl'] ) );
			}

			if ( array_key_exists( 'post_count', $input ) ) {
				$output['post_count'] = min( 50, max( 1, absint( $input['post_count'] ) ) );
			}

			if ( array_key_exists( 'feed_mode', $input ) ) {
				$modes = array( 'live', 'curated' );
				$output['feed_mode'] = in_array( $input['feed_mode'], $modes, true ) ? $input['feed_mode'] : 'live';
			}

			if ( array_key_exists( 'curated_fetch_limit', $input ) ) {
				$output['curated_fetch_limit'] = min( 50, max( 1, absint( $input['curated_fetch_limit'] ) ) );
			}

			$feed_bool_keys = array( 'show_image', 'show_caption', 'show_date', 'show_account', 'show_link', 'open_in_new_tab' );
			foreach ( $feed_bool_keys as $key ) {
				$output[ $key ] = ! empty( $input[ $key ] );
			}
		}

		if ( $is_design_save ) {
			$layouts = array( 'grid', 'masonry', 'carousel', 'showcase', 'bento', 'mosaic' );
			if ( array_key_exists( 'layout', $input ) && in_array( $input['layout'], $layouts, true ) ) {
				$output['layout'] = $input['layout'];
			}

			$bento_patterns = array( 'classic', 'hero', 'wave' );
			if ( array_key_exists( 'bento_pattern', $input ) && in_array( $input['bento_pattern'], $bento_patterns, true ) ) {
				$output['bento_pattern'] = $input['bento_pattern'];
			}

			$int_design_fields = array(
				'columns'        => array( 1, 8 ),
				'columns_tablet' => array( 1, 6 ),
				'columns_mobile' => array( 1, 4 ),
				'gap'            => array( 0, 64 ),
				'border_radius'  => array( 0, 50 ),
				'caption_size'   => array( 10, 32 ),
				'meta_size'      => array( 10, 24 ),
				'caption_lines'  => array( 1, 10 ),
				'carousel_speed' => array( 2000, 15000 ),
			);

			foreach ( $int_design_fields as $key => $range ) {
				if ( array_key_exists( $key, $input ) ) {
					$output[ $key ] = min( $range[1], max( $range[0], absint( $input[ $key ] ) ) );
				}
			}

			$aspect_ratios = array( 'square', 'portrait', 'landscape', 'original' );
			if ( array_key_exists( 'aspect_ratio', $input ) && in_array( $input['aspect_ratio'], $aspect_ratios, true ) ) {
				$output['aspect_ratio'] = $input['aspect_ratio'];
			}

			$image_fits = array( 'cover', 'contain' );
			if ( array_key_exists( 'image_fit', $input ) && in_array( $input['image_fit'], $image_fits, true ) ) {
				$output['image_fit'] = $input['image_fit'];
			}

			$card_styles = array( 'flat', 'elevated', 'bordered', 'glass' );
			if ( array_key_exists( 'card_style', $input ) && in_array( $input['card_style'], $card_styles, true ) ) {
				$output['card_style'] = $input['card_style'];
			}

			$hover_effects = array( 'none', 'zoom', 'fade', 'overlay-slide', 'grayscale', 'tilt' );
			if ( array_key_exists( 'hover_effect', $input ) && in_array( $input['hover_effect'], $hover_effects, true ) ) {
				$output['hover_effect'] = $input['hover_effect'];
			}

			$shadows = array( 'none', 'sm', 'md', 'lg' );
			if ( array_key_exists( 'shadow', $input ) && in_array( $input['shadow'], $shadows, true ) ) {
				$output['shadow'] = $input['shadow'];
			}

			$animations = array( 'none', 'stagger-fade', 'stagger-scale' );
			if ( array_key_exists( 'animation', $input ) && in_array( $input['animation'], $animations, true ) ) {
				$output['animation'] = $input['animation'];
			}

			$color_keys = array( 'color_bg', 'color_text', 'color_muted', 'color_accent' );
			foreach ( $color_keys as $key ) {
				if ( array_key_exists( $key, $input ) ) {
					$output[ $key ] = sanitize_hex_color( $input[ $key ] ) ?: $defaults[ $key ];
				}
			}

			if ( array_key_exists( 'color_overlay', $input ) ) {
				$output['color_overlay'] = sanitize_text_field( $input['color_overlay'] );
			}

			if ( array_key_exists( 'font_caption', $input ) ) {
				$output['font_caption'] = sanitize_text_field( $input['font_caption'] );
			}

			if ( array_key_exists( 'font_meta', $input ) ) {
				$output['font_meta'] = sanitize_text_field( $input['font_meta'] );
			}

			if ( array_key_exists( 'custom_css', $input ) ) {
				$output['custom_css'] = wp_strip_all_tags( $input['custom_css'] );
			}

			$output['carousel_autoplay'] = ! empty( $input['carousel_autoplay'] );
		}

		$this->settings = $output;

		return $output;
	}

	/**
	 * Parse shortcode/block overrides into a config array.
	 *
	 * @param array $overrides Attribute overrides.
	 * @return array
	 */
	public function resolve_config( $overrides = array() ) {
		$config = $this->all();

		if ( empty( $overrides ) ) {
			return $config;
		}

		$map = array(
			'count'          => 'post_count',
			'columns'        => 'columns',
			'columns_tablet' => 'columns_tablet',
			'columns_mobile' => 'columns_mobile',
		);

		foreach ( $overrides as $key => $value ) {
			if ( '' === $value || null === $value ) {
				continue;
			}

			$target = $map[ $key ] ?? $key;

			if ( in_array( $target, array( 'post_count', 'columns', 'columns_tablet', 'columns_mobile' ), true ) && 0 === (int) $value ) {
				continue;
			}

			if ( in_array( $target, array( 'show_image', 'show_caption', 'show_date', 'show_account', 'show_link', 'open_in_new_tab', 'carousel_autoplay' ), true ) ) {
				$config[ $target ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				continue;
			}

			if ( 'post_count' === $target ) {
				$config[ $target ] = min( 50, max( 1, absint( $value ) ) );
				continue;
			}

			$config[ $target ] = $value;
		}

		return $this->sanitize( $config );
	}

	/**
	 * Whether a license key is configured.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return ! empty( $this->get( 'agency_license_key' ) );
	}
}
