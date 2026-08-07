<?php
/**
 * Design engine — layouts, tokens, and CSS generation.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Design_Engine
 */
class Lumina_IG_Design_Engine {

	/**
	 * Settings handler.
	 *
	 * @var Lumina_IG_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Settings $settings Settings handler.
	 */
	public function __construct( Lumina_IG_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Available layout definitions.
	 *
	 * @return array
	 */
	public function get_layouts() {
		return array(
			'grid'      => __( 'Grid', 'lumina-instagram-feed' ),
			'masonry'   => __( 'Masonry', 'lumina-instagram-feed' ),
			'carousel'  => __( 'Carousel', 'lumina-instagram-feed' ),
			'showcase'  => __( 'Showcase Hero', 'lumina-instagram-feed' ),
			'bento'     => __( 'Bento Box', 'lumina-instagram-feed' ),
			'mosaic'    => __( 'Mosaic', 'lumina-instagram-feed' ),
		);
	}

	/**
	 * Build CSS custom properties and scoped styles for a feed instance.
	 *
	 * @param array  $config Feed configuration.
	 * @param string $id     Unique feed instance ID.
	 * @return string CSS string.
	 */
	public function generate_css( $config, $id ) {
		$shadows = array(
			'none' => 'none',
			'sm'   => '0 2px 8px rgba(0,0,0,0.08)',
			'md'   => '0 8px 24px rgba(0,0,0,0.12)',
			'lg'   => '0 16px 40px rgba(0,0,0,0.18)',
		);

		$ratios = array(
			'square'    => '1 / 1',
			'portrait'  => '4 / 5',
			'landscape' => '16 / 9',
			'original'  => 'auto',
		);

		$selector = '#lumina-ig-' . esc_attr( $id );

		$vars = array(
			'--lumina-cols'        => absint( $config['columns'] ),
			'--lumina-cols-tablet' => absint( $config['columns_tablet'] ),
			'--lumina-cols-mobile' => absint( $config['columns_mobile'] ),
			'--lumina-gap'         => absint( $config['gap'] ) . 'px',
			'--lumina-radius'      => absint( $config['border_radius'] ) . 'px',
			'--lumina-bg'          => $config['color_bg'],
			'--lumina-text'        => $config['color_text'],
			'--lumina-muted'       => $config['color_muted'],
			'--lumina-accent'      => $config['color_accent'],
			'--lumina-overlay'     => $config['color_overlay'],
			'--lumina-caption-font' => $config['font_caption'],
			'--lumina-meta-font'   => $config['font_meta'],
			'--lumina-caption-size' => absint( $config['caption_size'] ) . 'px',
			'--lumina-meta-size'   => absint( $config['meta_size'] ) . 'px',
			'--lumina-caption-lines' => absint( $config['caption_lines'] ),
			'--lumina-shadow'      => $shadows[ $config['shadow'] ] ?? $shadows['md'],
			'--lumina-aspect'      => $ratios[ $config['aspect_ratio'] ] ?? $ratios['square'],
			'--lumina-image-fit'   => $config['image_fit'],
		);

		$css  = $selector . " {\n";
		foreach ( $vars as $name => $value ) {
			$css .= "\t{$name}: {$value};\n";
		}
		$css .= "}\n";

		if ( ! empty( $config['custom_css'] ) ) {
			$custom = str_replace( '{{feed}}', $selector, $config['custom_css'] );
			$css   .= "\n" . $custom;
		}

		return $css;
	}

	/**
	 * Build feed wrapper classes.
	 *
	 * @param array $config Feed configuration.
	 * @return string Space-separated class list.
	 */
	public function get_wrapper_classes( $config ) {
		$classes = array(
			'lumina-ig-feed',
			'lumina-ig-layout-' . sanitize_html_class( $config['layout'] ),
			'lumina-ig-card-' . sanitize_html_class( $config['card_style'] ),
			'lumina-ig-hover-' . sanitize_html_class( $config['hover_effect'] ),
			'lumina-ig-anim-' . sanitize_html_class( $config['animation'] ),
		);

		if ( 'bento' === $config['layout'] ) {
			$classes[] = 'lumina-ig-bento-' . sanitize_html_class( $config['bento_pattern'] );
		}

		if ( ! empty( $config['carousel_autoplay'] ) && 'carousel' === $config['layout'] ) {
			$classes[] = 'lumina-ig-carousel-autoplay';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Build data attributes for JS-enhanced layouts.
	 *
	 * @param array $config Feed configuration.
	 * @return array
	 */
	public function get_data_attributes( $config ) {
		$attrs = array(
			'data-layout' => $config['layout'],
		);

		if ( 'carousel' === $config['layout'] ) {
			$attrs['data-autoplay'] = ! empty( $config['carousel_autoplay'] ) ? '1' : '0';
			$attrs['data-speed']    = absint( $config['carousel_speed'] );
		}

		return $attrs;
	}

	/**
	 * Get design option groups for admin UI.
	 *
	 * @return array
	 */
	public function get_design_fields() {
		return array(
			'layout' => array(
				'label'   => __( 'Layout', 'lumina-instagram-feed' ),
				'fields'  => array( 'layout', 'bento_pattern', 'columns', 'columns_tablet', 'columns_mobile', 'gap' ),
			),
			'media' => array(
				'label'  => __( 'Media', 'lumina-instagram-feed' ),
				'fields' => array( 'aspect_ratio', 'image_fit', 'border_radius' ),
			),
			'card' => array(
				'label'  => __( 'Card Style', 'lumina-instagram-feed' ),
				'fields' => array( 'card_style', 'shadow', 'hover_effect' ),
			),
			'typography' => array(
				'label'  => __( 'Typography', 'lumina-instagram-feed' ),
				'fields' => array( 'font_caption', 'font_meta', 'caption_size', 'meta_size', 'caption_lines' ),
			),
			'colors' => array(
				'label'  => __( 'Colors', 'lumina-instagram-feed' ),
				'fields' => array( 'color_bg', 'color_text', 'color_muted', 'color_accent', 'color_overlay' ),
			),
			'motion' => array(
				'label'  => __( 'Motion', 'lumina-instagram-feed' ),
				'fields' => array( 'animation', 'carousel_autoplay', 'carousel_speed' ),
			),
			'advanced' => array(
				'label'  => __( 'Advanced', 'lumina-instagram-feed' ),
				'fields' => array( 'custom_css' ),
			),
		);
	}
}
