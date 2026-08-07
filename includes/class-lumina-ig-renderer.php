<?php
/**
 * Feed HTML renderer.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Renderer
 */
class Lumina_IG_Renderer {

	/**
	 * Cache handler.
	 *
	 * @var Lumina_IG_Cache
	 */
	private $cache;

	/**
	 * Design engine.
	 *
	 * @var Lumina_IG_Design_Engine
	 */
	private $design;

	/**
	 * Constructor.
	 *
	 * @param Lumina_IG_Cache         $cache  Cache handler.
	 * @param Lumina_IG_Design_Engine $design Design engine.
	 */
	public function __construct( Lumina_IG_Cache $cache, Lumina_IG_Design_Engine $design ) {
		$this->cache  = $cache;
		$this->design = $design;
	}

	/**
	 * Render a complete feed.
	 *
	 * @param array $overrides Shortcode/block overrides.
	 * @return string HTML output.
	 */
	public function render( $overrides = array() ) {
		$config = lumina_ig()->settings->resolve_config( $overrides );
		$limit  = absint( $config['post_count'] );
		$items  = $this->cache->get_feed( $limit );

		if ( is_wp_error( $items ) ) {
			return $this->render_error( $items->get_error_message() );
		}

		if ( empty( $items ) ) {
			return $this->render_error( __( 'No Instagram posts to display.', 'lumina-instagram-feed' ) );
		}

		$instance_id = substr( md5( wp_json_encode( $config ) . wp_rand() ), 0, 8 );

		wp_enqueue_style( 'lumina-ig-feed' );
		wp_enqueue_script( 'lumina-ig-feed' );

		$css = $this->design->generate_css( $config, $instance_id );

		$classes = $this->design->get_wrapper_classes( $config );
		$data    = $this->design->get_data_attributes( $config );

		$data_attrs = '';
		foreach ( $data as $key => $value ) {
			$data_attrs .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		ob_start();
		?>
		<style id="lumina-ig-<?php echo esc_attr( $instance_id ); ?>-css"><?php echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
		<div id="lumina-ig-<?php echo esc_attr( $instance_id ); ?>" class="<?php echo esc_attr( $classes ); ?>"<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="lumina-ig-feed__inner">
				<?php
				foreach ( $items as $index => $item ) {
					echo $this->render_item( $item, $config, $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
			<?php if ( 'carousel' === $config['layout'] ) : ?>
				<button type="button" class="lumina-ig-nav lumina-ig-nav--prev" aria-label="<?php esc_attr_e( 'Previous', 'lumina-instagram-feed' ); ?>">&lsaquo;</button>
				<button type="button" class="lumina-ig-nav lumina-ig-nav--next" aria-label="<?php esc_attr_e( 'Next', 'lumina-instagram-feed' ); ?>">&rsaquo;</button>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single feed item.
	 *
	 * @param array $item   Normalized item data.
	 * @param array $config Feed configuration.
	 * @param int   $index  Item index.
	 * @return string
	 */
	private function render_item( $item, $config, $index ) {
		$show_image   = ! empty( $config['show_image'] );
		$show_caption = ! empty( $config['show_caption'] );
		$show_date    = ! empty( $config['show_date'] );
		$show_account = ! empty( $config['show_account'] );
		$show_link    = ! empty( $config['show_link'] );
		$new_tab      = ! empty( $config['open_in_new_tab'] );

		$tag   = $show_link && ! empty( $item['permalink'] ) ? 'a' : 'div';
		$attrs = array( 'class' => 'lumina-ig-item' );

		if ( 'showcase' === $config['layout'] && 0 === $index ) {
			$attrs['class'] .= ' lumina-ig-item--hero';
		}

		if ( 'a' === $tag ) {
			$attrs['href']   = $item['permalink'];
			$attrs['class'] .= ' lumina-ig-item--linked';
			if ( $new_tab ) {
				$attrs['target'] = '_blank';
				$attrs['rel']    = 'noopener noreferrer';
			}
		}

		$attr_string = '';
		foreach ( $attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		ob_start();
		?>
		<<?php echo esc_html( $tag ); ?><?php echo $attr_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> style="--lumina-item-index: <?php echo esc_attr( $index ); ?>;">
			<?php if ( $show_image && ! empty( $item['image_url'] ) ) : ?>
				<div class="lumina-ig-item__media">
					<img
						class="lumina-ig-item__image"
						src="<?php echo esc_url( $item['image_url'] ); ?>"
						alt="<?php echo esc_attr( wp_strip_all_tags( $item['caption'] ) ?: __( 'Instagram post', 'lumina-instagram-feed' ) ); ?>"
						loading="lazy"
						decoding="async"
					/>
					<?php if ( 'VIDEO' === ( $item['media_type'] ?? '' ) ) : ?>
						<span class="lumina-ig-item__badge" aria-hidden="true">&#9654;</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_caption || $show_date || $show_account ) : ?>
				<div class="lumina-ig-item__body">
					<?php if ( $show_account && ! empty( $item['username'] ) ) : ?>
						<div class="lumina-ig-item__account">
							<span class="lumina-ig-item__account-icon" aria-hidden="true">@</span>
							<span class="lumina-ig-item__account-name"><?php echo esc_html( $item['username'] ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $show_caption && ! empty( $item['caption'] ) ) : ?>
						<div class="lumina-ig-item__caption"><?php echo wp_kses_post( nl2br( $item['caption'] ) ); ?></div>
					<?php endif; ?>

					<?php if ( $show_date && ! empty( $item['date'] ) ) : ?>
						<time class="lumina-ig-item__date" datetime="<?php echo esc_attr( $item['timestamp'] ); ?>">
							<?php echo esc_html( $item['date'] ); ?>
						</time>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</<?php echo esc_html( $tag ); ?>>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render error message.
	 *
	 * @param string $message Error text.
	 * @return string
	 */
	private function render_error( $message ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		return sprintf(
			'<div class="lumina-ig-error">%s</div>',
			esc_html( $message )
		);
	}
}
