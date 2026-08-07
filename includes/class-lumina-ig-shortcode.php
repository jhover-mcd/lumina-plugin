<?php
/**
 * Shortcode handler.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Shortcode
 */
class Lumina_IG_Shortcode {

	/**
	 * Feed renderer.
	 *
	 * @var Lumina_IG_Renderer
	 */
	private $renderer;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Renderer $renderer Feed renderer.
	 */
	public function __construct( Lumina_IG_Renderer $renderer ) {
		$this->renderer = $renderer;
		add_shortcode( 'lumina_instagram', array( $this, 'render' ) );
	}

	/**
	 * Render shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'count'          => '',
				'layout'         => '',
				'columns'        => '',
				'columns_tablet' => '',
				'columns_mobile' => '',
				'show_image'     => '',
				'show_caption'   => '',
				'show_date'      => '',
				'show_account'   => '',
				'show_link'      => '',
			),
			$atts,
			'lumina_instagram'
		);

		return $this->renderer->render( $atts );
	}
}
