<?php
/**
 * Gutenberg block registration.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Block
 */
class Lumina_IG_Block {

	/**
	 * Feed renderer.
	 *
	 * @var Lumina_IG_Renderer
	 */
	private $renderer;

	/**
	 * Design engine.
	 *
	 * @var Lumina_IG_Design_Engine
	 */
	private $design;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Renderer      $renderer Feed renderer.
	 * @param Lumina_IG_Design_Engine $design   Design engine.
	 */
	public function __construct( Lumina_IG_Renderer $renderer, Lumina_IG_Design_Engine $design ) {
		$this->renderer = $renderer;
		$this->design   = $design;

		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register block type.
	 */
	public function register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			LUMINA_IG_PLUGIN_DIR . 'blocks/feed',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Server-side block render.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render( $attributes ) {
		$overrides = array();

		$keys = array(
			'count',
			'layout',
			'columns',
			'columns_tablet',
			'columns_mobile',
			'show_image',
			'show_caption',
			'show_date',
			'show_account',
			'show_link',
		);

		foreach ( $keys as $key ) {
			if ( isset( $attributes[ $key ] ) && '' !== $attributes[ $key ] ) {
				$overrides[ $key ] = $attributes[ $key ];
			}
		}

		if ( ! empty( $attributes['useGlobalDesign'] ) ) {
			return $this->renderer->render( $overrides );
		}

		return $this->renderer->render( array_merge( lumina_ig()->settings->all(), $overrides ) );
	}
}
