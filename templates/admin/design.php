<?php
/**
 * Design studio admin page template.
 *
 * @package LuminaInstagramFeed
 *
 * @var array $settings Plugin settings.
 * @var array $layouts  Available layouts.
 * @var array $groups   Design field groups.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lumina_ig_render_design_field' ) ) {
	/**
	 * Render a single design field control.
	 *
	 * @param string $field    Field key.
	 * @param array  $settings Current settings.
	 */
	function lumina_ig_render_design_field( $field, $settings ) {
		$key   = Lumina_IG_Settings::OPTION_KEY;
		$value = $settings[ $field ] ?? '';
		$id    = 'lumina_' . $field;

		echo '<div class="lumina-ig-field lumina-ig-field--' . esc_attr( $field ) . '">';

		switch ( $field ) {
			case 'layout':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Layout', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[layout]" class="lumina-ig-design-input">';
				foreach ( lumina_ig()->design->get_layouts() as $layout_key => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $layout_key ), selected( $value, $layout_key, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'bento_pattern':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Bento Pattern', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[bento_pattern]" class="lumina-ig-design-input">';
				foreach ( array( 'classic' => 'Classic', 'hero' => 'Hero Focus', 'wave' => 'Wave' ) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'columns':
			case 'columns_tablet':
			case 'columns_mobile':
				$labels = array(
					'columns'        => __( 'Desktop Columns', 'lumina-instagram-feed' ),
					'columns_tablet' => __( 'Tablet Columns', 'lumina-instagram-feed' ),
					'columns_mobile' => __( 'Mobile Columns', 'lumina-instagram-feed' ),
				);
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $labels[ $field ] ) . '</label>';
				printf(
					'<input type="number" min="1" max="8" id="%s" name="%s[%s]" value="%s" class="lumina-ig-design-input small-text" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $field ),
					esc_attr( $value )
				);
				break;

			case 'gap':
			case 'border_radius':
			case 'caption_size':
			case 'meta_size':
			case 'caption_lines':
			case 'carousel_speed':
				$labels = array(
					'gap'            => __( 'Gap (px)', 'lumina-instagram-feed' ),
					'border_radius'  => __( 'Border Radius (px)', 'lumina-instagram-feed' ),
					'caption_size'   => __( 'Caption Size (px)', 'lumina-instagram-feed' ),
					'meta_size'      => __( 'Meta Size (px)', 'lumina-instagram-feed' ),
					'caption_lines'  => __( 'Caption Line Clamp', 'lumina-instagram-feed' ),
					'carousel_speed' => __( 'Autoplay Speed (ms)', 'lumina-instagram-feed' ),
				);
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $labels[ $field ] ) . '</label>';
				printf(
					'<input type="number" id="%s" name="%s[%s]" value="%s" class="lumina-ig-design-input small-text" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $field ),
					esc_attr( $value )
				);
				break;

			case 'aspect_ratio':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Aspect Ratio', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[aspect_ratio]" class="lumina-ig-design-input">';
				foreach ( array( 'square' => 'Square', 'portrait' => 'Portrait 4:5', 'landscape' => 'Landscape 16:9', 'original' => 'Original' ) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'image_fit':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Image Fit', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[image_fit]" class="lumina-ig-design-input">';
				printf( '<option value="cover" %s>Cover</option>', selected( $value, 'cover', false ) );
				printf( '<option value="contain" %s>Contain</option>', selected( $value, 'contain', false ) );
				echo '</select>';
				break;

			case 'card_style':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Card Style', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[card_style]" class="lumina-ig-design-input">';
				foreach ( array( 'flat' => 'Flat', 'elevated' => 'Elevated', 'bordered' => 'Bordered', 'glass' => 'Glass' ) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'shadow':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Shadow', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[shadow]" class="lumina-ig-design-input">';
				foreach ( array( 'none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large' ) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'hover_effect':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Hover Effect', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[hover_effect]" class="lumina-ig-design-input">';
				foreach ( array(
					'none'          => 'None',
					'zoom'          => 'Zoom',
					'fade'          => 'Fade',
					'overlay-slide' => 'Overlay Slide Up',
					'grayscale'     => 'Grayscale to Color',
					'tilt'          => '3D Tilt',
				) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'animation':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Load Animation', 'lumina-instagram-feed' ) . '</label>';
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '[animation]" class="lumina-ig-design-input">';
				foreach ( array( 'none' => 'None', 'stagger-fade' => 'Stagger Fade', 'stagger-scale' => 'Stagger Scale' ) as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'carousel_autoplay':
				echo '<label>';
				printf(
					'<input type="checkbox" id="%s" name="%s[carousel_autoplay]" value="1" class="lumina-ig-design-input" %s /> %s',
					esc_attr( $id ),
					esc_attr( $key ),
					checked( ! empty( $value ), true, false ),
					esc_html__( 'Carousel autoplay', 'lumina-instagram-feed' )
				);
				echo '</label>';
				break;

			case 'font_caption':
			case 'font_meta':
				$labels = array(
					'font_caption' => __( 'Caption Font Family', 'lumina-instagram-feed' ),
					'font_meta'    => __( 'Meta Font Family', 'lumina-instagram-feed' ),
				);
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $labels[ $field ] ) . '</label>';
				printf(
					'<input type="text" id="%s" name="%s[%s]" value="%s" class="lumina-ig-design-input regular-text" placeholder="inherit" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $field ),
					esc_attr( $value )
				);
				break;

			case 'color_bg':
			case 'color_text':
			case 'color_muted':
			case 'color_accent':
				$labels = array(
					'color_bg'     => __( 'Background', 'lumina-instagram-feed' ),
					'color_text'   => __( 'Text', 'lumina-instagram-feed' ),
					'color_muted'  => __( 'Muted Text', 'lumina-instagram-feed' ),
					'color_accent' => __( 'Accent', 'lumina-instagram-feed' ),
				);
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $labels[ $field ] ) . '</label>';
				printf(
					'<input type="color" id="%s" name="%s[%s]" value="%s" class="lumina-ig-design-input" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $field ),
					esc_attr( $value )
				);
				break;

			case 'color_overlay':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Overlay Color', 'lumina-instagram-feed' ) . '</label>';
				printf(
					'<input type="text" id="%s" name="%s[color_overlay]" value="%s" class="lumina-ig-design-input regular-text" placeholder="rgba(0,0,0,0.55)" />',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_attr( $value )
				);
				break;

			case 'custom_css':
				echo '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Custom CSS', 'lumina-instagram-feed' ) . '</label>';
				printf(
					'<textarea id="%s" name="%s[custom_css]" rows="8" class="large-text code lumina-ig-design-input" placeholder="{{feed}} .lumina-ig-item { ... }">%s</textarea>',
					esc_attr( $id ),
					esc_attr( $key ),
					esc_textarea( $value )
				);
				echo '<p class="description">' . esc_html__( 'Use {{feed}} as the feed selector placeholder.', 'lumina-instagram-feed' ) . '</p>';
				break;
		}

		echo '</div>';
	}
}
?>
<div class="wrap lumina-ig-admin lumina-ig-design-studio">
	<h1><?php esc_html_e( 'Design Studio', 'lumina-instagram-feed' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Craft unique feed layouts with full control over structure, color, typography, and motion.', 'lumina-instagram-feed' ); ?></p>

	<form method="post" action="options.php" id="lumina-ig-design-form">
		<?php settings_fields( 'lumina_ig_settings_group' ); ?>
		<input type="hidden" name="<?php echo esc_attr( Lumina_IG_Settings::OPTION_KEY ); ?>[agency_license_key]" value="<?php echo esc_attr( $settings['agency_license_key'] ); ?>" />

		<div class="lumina-ig-design-studio__layout">
			<div class="lumina-ig-design-studio__controls">
				<?php foreach ( $groups as $group ) : ?>
					<div class="lumina-ig-panel lumina-ig-panel--compact">
						<h2><?php echo esc_html( $group['label'] ); ?></h2>
						<?php
						foreach ( $group['fields'] as $field ) {
							lumina_ig_render_design_field( $field, $settings );
						}
						?>
					</div>
				<?php endforeach; ?>

				<p class="submit">
					<?php submit_button( __( 'Save Design', 'lumina-instagram-feed' ), 'primary', 'submit', false ); ?>
					<button type="button" class="button" id="lumina-ig-preview-btn"><?php esc_html_e( 'Live Preview', 'lumina-instagram-feed' ); ?></button>
				</p>
			</div>

			<div class="lumina-ig-design-studio__preview">
				<div class="lumina-ig-panel">
					<h2><?php esc_html_e( 'Preview', 'lumina-instagram-feed' ); ?></h2>
					<div id="lumina-ig-live-preview" class="lumina-ig-live-preview">
						<p class="lumina-ig-live-preview__placeholder"><?php esc_html_e( 'Click Live Preview to render your feed with current design settings.', 'lumina-instagram-feed' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
