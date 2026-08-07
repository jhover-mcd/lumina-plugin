<?php
/**
 * Curated feed picker admin page.
 *
 * @package LuminaInstagramFeed
 *
 * @var array  $settings Plugin settings.
 * @var array  $library  Imported photo library keyed by post ID.
 * @var array  $selected Selected post IDs in display order.
 * @var array  $meta     Curated library metadata.
 * @var string $notice   Admin notice key.
 */

defined( 'ABSPATH' ) || exit;

$is_curated = 'curated' === ( $settings['feed_mode'] ?? 'live' );
$library_list = array_values( $library );
usort(
	$library_list,
	function ( $a, $b ) {
		return strcmp( $b['timestamp'] ?? '', $a['timestamp'] ?? '' );
	}
);
?>
<div class="wrap lumina-ig-admin lumina-ig-curate">
	<h1><?php esc_html_e( 'Curate Feed', 'lumina-instagram-feed' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Import Instagram posts into your site, pick the ones you want, and control the order. Image URLs refresh automatically once a week while your selection stays the same.', 'lumina-instagram-feed' ); ?></p>

	<?php if ( ! $is_curated ) : ?>
		<div class="notice notice-warning">
			<p>
				<?php esc_html_e( 'Curated mode is not enabled yet.', 'lumina-instagram-feed' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=lumina-instagram-feed' ) ); ?>"><?php esc_html_e( 'Switch to Curated Feed on Settings', 'lumina-instagram-feed' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( 'library_synced' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Instagram posts imported into your photo library.', 'lumina-instagram-feed' ); ?></p></div>
	<?php elseif ( 'library_sync_failed' === $notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( get_transient( 'lumina_ig_library_error' ) ?: __( 'Import failed.', 'lumina-instagram-feed' ) ); ?></p></div>
	<?php elseif ( 'curated_saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Curated feed selection saved.', 'lumina-instagram-feed' ); ?></p></div>
	<?php elseif ( 'curated_mode_required' === $notice ) : ?>
		<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'Enable Curated Feed mode on Settings before saving a selection.', 'lumina-instagram-feed' ); ?></p></div>
	<?php endif; ?>

	<div class="lumina-ig-curate__toolbar lumina-ig-panel">
		<div class="lumina-ig-curate__toolbar-copy">
			<strong><?php esc_html_e( 'Photo library', 'lumina-instagram-feed' ); ?></strong>
			<p>
				<?php
				printf(
					/* translators: 1: library count 2: import limit 3: display count */
					esc_html__( '%1$d posts in library · imports up to %2$d · feed displays up to %3$d', 'lumina-instagram-feed' ),
					(int) count( $library ),
					(int) ( $settings['curated_fetch_limit'] ?? 50 ),
					(int) ( $settings['post_count'] ?? 12 )
				);
				?>
			</p>
			<?php if ( ! empty( $meta['last_sync'] ) ) : ?>
				<p class="description"><?php printf( esc_html__( 'Last import: %s', 'lumina-instagram-feed' ), esc_html( $meta['last_sync'] ) ); ?></p>
			<?php endif; ?>
			<?php
			$next_import = wp_next_scheduled( Lumina_IG_Cron::CURATED_HOOK );
			if ( $next_import ) :
				?>
				<p class="description"><?php printf( esc_html__( 'Next auto-import: %s', 'lumina-instagram-feed' ), esc_html( date_i18n( 'Y-m-d H:i:s', $next_import ) ) ); ?></p>
			<?php endif; ?>
		</div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'lumina_ig_sync_library' ); ?>
			<input type="hidden" name="action" value="lumina_ig_sync_library" />
			<button type="submit" class="button button-secondary" <?php disabled( ! $is_curated ); ?>>
				<?php esc_html_e( 'Import from Instagram', 'lumina-instagram-feed' ); ?>
			</button>
		</form>
	</div>

	<?php if ( empty( $library ) ) : ?>
		<div class="lumina-ig-panel lumina-ig-curate__empty">
			<p><?php esc_html_e( 'No photos in the library yet. Click Import from Instagram to pull in recent posts, then select the ones you want on your feed.', 'lumina-instagram-feed' ); ?></p>
		</div>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="lumina-ig-curate-form">
			<?php wp_nonce_field( 'lumina_ig_save_curated' ); ?>
			<input type="hidden" name="action" value="lumina_ig_save_curated" />

			<div class="lumina-ig-panel">
				<div class="lumina-ig-curate__selected-head">
					<h2><?php esc_html_e( 'Selected for feed', 'lumina-instagram-feed' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Drag to reorder. The feed shows these photos in this order (up to your display limit).', 'lumina-instagram-feed' ); ?></p>
					<p class="lumina-ig-curate__count"><span id="lumina-ig-curate-count"><?php echo (int) count( $selected ); ?></span> <?php esc_html_e( 'selected', 'lumina-instagram-feed' ); ?></p>
				</div>
				<ul id="lumina-ig-curate-selected" class="lumina-ig-curate__selected-list">
					<?php foreach ( $selected as $id ) : ?>
						<?php if ( empty( $library[ $id ] ) ) { continue; } $item = $library[ $id ]; ?>
						<li class="lumina-ig-curate__selected-item" data-id="<?php echo esc_attr( $id ); ?>">
							<span class="lumina-ig-curate__drag" aria-hidden="true">&#8942;&#8942;</span>
							<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="" />
							<input type="hidden" name="curated_selected[]" value="<?php echo esc_attr( $id ); ?>" />
							<button type="button" class="lumina-ig-curate__remove" aria-label="<?php esc_attr_e( 'Remove from feed', 'lumina-instagram-feed' ); ?>">&times;</button>
						</li>
					<?php endforeach; ?>
				</ul>
				<p id="lumina-ig-curate-selected-empty" class="lumina-ig-curate__selected-empty" <?php echo ! empty( $selected ) ? 'hidden' : ''; ?>>
					<?php esc_html_e( 'Click photos below to add them to your feed.', 'lumina-instagram-feed' ); ?>
				</p>
			</div>

			<div class="lumina-ig-panel">
				<h2><?php esc_html_e( 'Choose photos', 'lumina-instagram-feed' ); ?></h2>
				<div class="lumina-ig-curate__grid">
					<?php foreach ( $library_list as $item ) : ?>
						<?php
						$id       = $item['id'] ?? '';
						$is_on    = in_array( $id, $selected, true );
						$caption  = wp_strip_all_tags( $item['caption'] ?? '' );
						$caption  = $caption ? wp_trim_words( $caption, 10, '…' ) : __( 'Instagram post', 'lumina-instagram-feed' );
						?>
						<button
							type="button"
							class="lumina-ig-curate__photo<?php echo $is_on ? ' is-selected' : ''; ?>"
							data-id="<?php echo esc_attr( $id ); ?>"
							data-image="<?php echo esc_url( $item['image_url'] ); ?>"
							data-caption="<?php echo esc_attr( $caption ); ?>"
							aria-pressed="<?php echo $is_on ? 'true' : 'false'; ?>"
						>
							<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $caption ); ?>" loading="lazy" />
							<span class="lumina-ig-curate__check" aria-hidden="true">&#10003;</span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<p class="submit">
				<button type="submit" class="button button-primary" <?php disabled( ! $is_curated ); ?>><?php esc_html_e( 'Save Feed Selection', 'lumina-instagram-feed' ); ?></button>
			</p>
		</form>
	<?php endif; ?>
</div>
