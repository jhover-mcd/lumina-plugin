<?php
/**
 * Admin settings page template.
 *
 * @package LuminaInstagramFeed
 *
 * @var array  $settings Plugin settings.
 * @var array  $meta     Cache metadata.
 * @var string $notice   Admin notice key.
 */

defined( 'ABSPATH' ) || exit;

$option_key = Lumina_IG_Settings::OPTION_KEY;
?>
<div class="wrap lumina-ig-admin">
	<h1><?php esc_html_e( 'Lumina Instagram Feed', 'lumina-instagram-feed' ); ?></h1>

	<?php if ( 'refreshed' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Feed cache refreshed successfully.', 'lumina-instagram-feed' ); ?></p></div>
	<?php elseif ( 'test_ok' === $notice ) : ?>
		<?php $test = get_transient( 'lumina_ig_test_result' ); ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				if ( $test ) {
					printf(
						/* translators: 1: license label 2: media count */
						esc_html__( 'Connected to %1$s (%2$d posts available).', 'lumina-instagram-feed' ),
						esc_html( $test['label'] ?: __( 'your feed', 'lumina-instagram-feed' ) ),
						absint( $test['media_count'] ?? 0 )
					);
				} else {
					esc_html_e( 'Connection successful.', 'lumina-instagram-feed' );
				}
				?>
			</p>
		</div>
	<?php elseif ( 'test_failed' === $notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( get_transient( 'lumina_ig_test_error' ) ?: __( 'Connection test failed.', 'lumina-instagram-feed' ) ); ?></p></div>
	<?php endif; ?>

	<div class="lumina-ig-admin__grid">
		<div class="lumina-ig-admin__main">
			<form method="post" action="options.php">
				<?php settings_fields( 'lumina_ig_settings_group' ); ?>

				<div class="lumina-ig-panel">
					<h2><?php esc_html_e( 'License Key', 'lumina-instagram-feed' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Enter the license key provided for this site. Your agency manages the Instagram connection from their hub.', 'lumina-instagram-feed' ); ?></p>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="lumina_agency_license_key"><?php esc_html_e( 'License Key', 'lumina-instagram-feed' ); ?></label></th>
							<td>
								<?php if ( Lumina_IG_Config::is_license_locked() ) : ?>
									<code><?php echo esc_html( $settings['agency_license_key'] ); ?></code>
									<input type="hidden" name="<?php echo esc_attr( $option_key ); ?>[agency_license_key]" value="<?php echo esc_attr( $settings['agency_license_key'] ); ?>" />
								<?php else : ?>
									<input type="text" class="regular-text" id="lumina_agency_license_key" name="<?php echo esc_attr( $option_key ); ?>[agency_license_key]" value="<?php echo esc_attr( $settings['agency_license_key'] ); ?>" placeholder="your-site-license-key" autocomplete="off" />
								<?php endif; ?>
							</td>
						</tr>
					</table>

					<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save License Key', 'lumina-instagram-feed' ); ?></button>
					</p>
				</div>

				<div class="lumina-ig-panel">
					<h2><?php esc_html_e( 'Feed Mode', 'lumina-instagram-feed' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Source', 'lumina-instagram-feed' ); ?></th>
							<td>
								<label style="display:block;margin-bottom:8px;">
									<input type="radio" name="<?php echo esc_attr( $option_key ); ?>[feed_mode]" value="live" <?php checked( ( $settings['feed_mode'] ?? 'live' ), 'live' ); ?> />
									<?php esc_html_e( 'Live feed — automatically shows the latest Instagram posts', 'lumina-instagram-feed' ); ?>
								</label>
								<label style="display:block;">
									<input type="radio" name="<?php echo esc_attr( $option_key ); ?>[feed_mode]" value="curated" <?php checked( ( $settings['feed_mode'] ?? 'live' ), 'curated' ); ?> />
									<?php esc_html_e( 'Curated feed — manually pick which photos to display', 'lumina-instagram-feed' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'Curated mode stores photos on your site and only calls the API when you import. Use Curate Feed to choose photos.', 'lumina-instagram-feed' ); ?>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=lumina-instagram-curate' ) ); ?>"><?php esc_html_e( 'Open Curate Feed', 'lumina-instagram-feed' ); ?></a>
								</p>
							</td>
						</tr>
						<tr class="lumina-ig-curated-only">
							<th scope="row"><label for="lumina_curated_fetch_limit"><?php esc_html_e( 'Import Limit', 'lumina-instagram-feed' ); ?></label></th>
							<td>
								<input type="number" min="1" max="50" id="lumina_curated_fetch_limit" name="<?php echo esc_attr( $option_key ); ?>[curated_fetch_limit]" value="<?php echo esc_attr( $settings['curated_fetch_limit'] ?? 50 ); ?>" />
								<p class="description"><?php esc_html_e( 'How many recent posts to pull in when you click Import from Instagram on the Curate Feed page.', 'lumina-instagram-feed' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<div class="lumina-ig-panel">
					<h2><?php esc_html_e( 'Feed Content', 'lumina-instagram-feed' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="lumina_post_count"><?php esc_html_e( 'Number of Posts', 'lumina-instagram-feed' ); ?></label></th>
							<td>
								<input type="number" min="1" max="50" id="lumina_post_count" name="<?php echo esc_attr( $option_key ); ?>[post_count]" value="<?php echo esc_attr( $settings['post_count'] ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Display Fields', 'lumina-instagram-feed' ); ?></th>
							<td>
								<?php
								$fields = array(
									'show_image'   => __( 'Image', 'lumina-instagram-feed' ),
									'show_caption' => __( 'Caption / Description', 'lumina-instagram-feed' ),
									'show_date'    => __( 'Date', 'lumina-instagram-feed' ),
									'show_link'    => __( 'Link to Instagram', 'lumina-instagram-feed' ),
								);
								foreach ( $fields as $key => $label ) :
									?>
									<label style="display:block;margin-bottom:6px;">
										<input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?> />
										<?php echo esc_html( $label ); ?>
									</label>
								<?php endforeach; ?>
								<label style="display:block;margin-top:10px;">
									<input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[open_in_new_tab]" value="1" <?php checked( ! empty( $settings['open_in_new_tab'] ) ); ?> />
									<?php esc_html_e( 'Open links in new tab', 'lumina-instagram-feed' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="lumina_cache_ttl"><?php esc_html_e( 'Cache Duration', 'lumina-instagram-feed' ); ?></label></th>
							<td>
								<select id="lumina_cache_ttl" name="<?php echo esc_attr( $option_key ); ?>[cache_ttl]">
									<?php
									$options = array(
										1800  => __( '30 minutes', 'lumina-instagram-feed' ),
										3600  => __( '1 hour (recommended)', 'lumina-instagram-feed' ),
										7200  => __( '2 hours', 'lumina-instagram-feed' ),
										14400 => __( '4 hours', 'lumina-instagram-feed' ),
									);
									foreach ( $options as $value => $label ) :
										?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $settings['cache_ttl'], $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description lumina-ig-live-only"><?php esc_html_e( 'Feeds are also refreshed automatically every hour via WP-Cron.', 'lumina-instagram-feed' ); ?></p>
								<p class="description lumina-ig-curated-only"><?php esc_html_e( 'Curated feeds refresh image URLs automatically once a week via WP-Cron. Your selected photos stay the same.', 'lumina-instagram-feed' ); ?></p>
							</td>
						</tr>
					</table>

					<?php submit_button( __( 'Save Feed Settings', 'lumina-instagram-feed' ) ); ?>
				</div>
			</form>
		</div>

		<aside class="lumina-ig-admin__sidebar">
			<div class="lumina-ig-panel">
				<h2><?php echo 'curated' === ( $settings['feed_mode'] ?? 'live' ) ? esc_html__( 'Curated Feed', 'lumina-instagram-feed' ) : esc_html__( 'Cache Status', 'lumina-instagram-feed' ); ?></h2>
				<?php if ( 'curated' === ( $settings['feed_mode'] ?? 'live' ) ) : ?>
					<?php $curated_meta = lumina_ig()->curated->get_meta(); ?>
					<ul class="lumina-ig-status-list">
						<li><strong><?php esc_html_e( 'Mode:', 'lumina-instagram-feed' ); ?></strong> <?php esc_html_e( 'Curated', 'lumina-instagram-feed' ); ?></li>
						<li><strong><?php esc_html_e( 'Library posts:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( $curated_meta['item_count'] ); ?></li>
						<li><strong><?php esc_html_e( 'Selected:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( count( lumina_ig()->curated->get_selected_ids() ) ); ?></li>
						<li><strong><?php esc_html_e( 'Last import:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( $curated_meta['last_sync'] ?: '—' ); ?></li>
						<li><strong><?php esc_html_e( 'Next auto-import:', 'lumina-instagram-feed' ); ?></strong>
							<?php
							$next_import = wp_next_scheduled( Lumina_IG_Cron::CURATED_HOOK );
							echo esc_html( $next_import ? date_i18n( 'Y-m-d H:i:s', $next_import ) : '—' );
							?>
						</li>
					</ul>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=lumina-instagram-curate' ) ); ?>"><?php esc_html_e( 'Curate Feed', 'lumina-instagram-feed' ); ?></a>
				<?php else : ?>
					<ul class="lumina-ig-status-list">
						<li><strong><?php esc_html_e( 'Status:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( ucfirst( $meta['status'] ) ); ?></li>
						<li><strong><?php esc_html_e( 'Cached posts:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( $meta['item_count'] ); ?></li>
						<li><strong><?php esc_html_e( 'Last refresh:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( $meta['last_refresh'] ?: '—' ); ?></li>
						<li><strong><?php esc_html_e( 'Next refresh:', 'lumina-instagram-feed' ); ?></strong> <?php echo esc_html( $meta['next_refresh'] ?: '—' ); ?></li>
					</ul>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'lumina_ig_refresh_cache' ); ?>
						<input type="hidden" name="action" value="lumina_ig_refresh_cache" />
						<button type="submit" class="button"><?php esc_html_e( 'Refresh Cache Now', 'lumina-instagram-feed' ); ?></button>
					</form>
				<?php endif; ?>
			</div>

			<div class="lumina-ig-panel">
				<h2><?php esc_html_e( 'Test Connection', 'lumina-instagram-feed' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'lumina_ig_test_connection' ); ?>
					<input type="hidden" name="action" value="lumina_ig_test_connection" />
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Test Connection', 'lumina-instagram-feed' ); ?></button>
				</form>
			</div>

			<div class="lumina-ig-panel">
				<h2><?php esc_html_e( 'Embed Your Feed', 'lumina-instagram-feed' ); ?></h2>
				<p><?php esc_html_e( 'Use the shortcode:', 'lumina-instagram-feed' ); ?></p>
				<code class="lumina-ig-code">[lumina_instagram]</code>
				<p><?php esc_html_e( 'Or with overrides:', 'lumina-instagram-feed' ); ?></p>
				<code class="lumina-ig-code">[lumina_instagram count="8" layout="bento" show_caption="0"]</code>
				<p><?php esc_html_e( 'You can also add the Lumina Instagram block in the block editor.', 'lumina-instagram-feed' ); ?></p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=lumina-instagram-design' ) ); ?>"><?php esc_html_e( 'Open Design Studio', 'lumina-instagram-feed' ); ?></a>
			</div>
		</aside>
	</div>
</div>
