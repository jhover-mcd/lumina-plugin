<?php
/**
 * Agency hub API client.
 *
 * @package LuminaInstagramFeed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Lumina_IG_Api
 */
class Lumina_IG_Api {

	/**
	 * Settings instance.
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
	 * Fetch media from the agency hub.
	 *
	 * @param int $limit Number of posts to fetch.
	 * @return array|WP_Error Normalized media items or error.
	 */
	public function fetch_media( $limit = 12 ) {
		if ( ! $this->settings->is_configured() ) {
			return new WP_Error(
				'lumina_ig_not_configured',
				__( 'Please enter a license key.', 'lumina-instagram-feed' )
			);
		}

		$body = $this->request_hub(
			'feed',
			array(
				'limit' => min( 50, max( 1, absint( $limit ) ) ),
			)
		);

		if ( is_wp_error( $body ) ) {
			return $body;
		}

		if ( empty( $body['items'] ) || ! is_array( $body['items'] ) ) {
			return new WP_Error(
				'lumina_ig_empty',
				$body['message'] ?? __( 'No Instagram posts were returned.', 'lumina-instagram-feed' )
			);
		}

		return $this->normalize_items( $body['items'] );
	}

	/**
	 * Test hub connection and return account info.
	 *
	 * @return array|WP_Error
	 */
	public function test_connection() {
		if ( ! $this->settings->is_configured() ) {
			return new WP_Error(
				'lumina_ig_not_configured',
				__( 'Please enter a license key.', 'lumina-instagram-feed' )
			);
		}

		$body = $this->request_hub( 'status', array() );

		if ( is_wp_error( $body ) ) {
			return $body;
		}

		return array(
			'user_id'              => $body['user_id'] ?? '',
			'label'                => $body['label'] ?? '',
			'media_count'          => $body['media_count'] ?? 0,
			'token_status'         => $body['token_status'] ?? '',
			'token_expires_at'     => $body['token_expires_at'] ?? '',
			'token_days_remaining' => $body['token_days_remaining'] ?? null,
		);
	}

	/**
	 * Stored Instagram token health from the last successful hub response.
	 *
	 * @return array
	 */
	public function get_token_health() {
		$stored = get_option( 'lumina_ig_token_health', array() );

		return wp_parse_args(
			is_array( $stored ) ? $stored : array(),
			array(
				'status'         => '',
				'expires_at'     => '',
				'days_remaining' => null,
				'checked_at'     => '',
			)
		);
	}

	/**
	 * Perform a request against the agency hub.
	 *
	 * @param string $endpoint Endpoint slug.
	 * @param array  $args     Query arguments.
	 * @return array|WP_Error
	 */
	private function request_hub( $endpoint, $args = array() ) {
		$license = (string) $this->settings->get( 'agency_license_key', '' );
		$base    = trailingslashit( Lumina_IG_Config::hub_url() );
		$url     = add_query_arg( $args, $base . 'v1/' . $endpoint );

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'Accept'             => 'application/json',
					'X-Lumina-License'   => $license,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new WP_Error(
				'lumina_ig_hub_invalid',
				__( 'Agency feed service returned an invalid response.', 'lumina-instagram-feed' )
			);
		}

		$this->remember_token_health( $body );

		if ( $code < 200 || $code >= 300 ) {
			$message = $body['message'] ?? $body['error'] ?? __( 'Agency feed service request failed.', 'lumina-instagram-feed' );

			return new WP_Error( 'lumina_ig_hub_error', $message, array( 'status' => $code ) );
		}

		return $body;
	}

	/**
	 * Persist token expiry details returned by the hub.
	 *
	 * @param array $body Hub response body.
	 */
	private function remember_token_health( $body ) {
		if ( empty( $body['token_status'] ) ) {
			return;
		}

		update_option(
			'lumina_ig_token_health',
			array(
				'status'         => sanitize_text_field( (string) $body['token_status'] ),
				'expires_at'     => sanitize_text_field( (string) ( $body['token_expires_at'] ?? '' ) ),
				'days_remaining' => isset( $body['token_days_remaining'] ) && null !== $body['token_days_remaining']
					? (int) $body['token_days_remaining']
					: null,
				'checked_at'     => current_time( 'mysql' ),
			),
			false
		);
	}

	/**
	 * Normalize hub items into consistent feed items.
	 *
	 * @param array $items Hub items.
	 * @return array
	 */
	private function normalize_items( $items ) {
		$normalized = array();

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$normalized[] = array(
				'id'         => sanitize_text_field( $item['id'] ?? '' ),
				'caption'    => isset( $item['caption'] ) ? wp_kses_post( $item['caption'] ) : '',
				'media_type' => sanitize_text_field( $item['media_type'] ?? 'IMAGE' ),
				'image_url'  => esc_url_raw( $item['image_url'] ?? '' ),
				'permalink'  => esc_url_raw( $item['permalink'] ?? '' ),
				'timestamp'  => sanitize_text_field( $item['timestamp'] ?? '' ),
				'username'   => sanitize_text_field( $item['username'] ?? '' ),
				'date'       => sanitize_text_field( $item['date'] ?? $this->format_date( $item['timestamp'] ?? '' ) ),
			);
		}

		return $normalized;
	}

	/**
	 * Format ISO timestamp for display.
	 *
	 * @param string $timestamp ISO 8601 timestamp.
	 * @return string
	 */
	private function format_date( $timestamp ) {
		if ( empty( $timestamp ) ) {
			return '';
		}

		$time = strtotime( $timestamp );

		if ( ! $time ) {
			return '';
		}

		return date_i18n( get_option( 'date_format' ), $time );
	}
}
