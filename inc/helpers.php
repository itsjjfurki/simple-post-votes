<?php
/**
 * Helper functions
 *
 * @package simple-post-votes-plugin
 */

if ( ! function_exists( 'spv_get_requester_ip' ) ) {

	/**
	 * Return requesters ip address
	 *
	 * @return string|null
	 */
	function spv_get_requester_ip(): string|null {
		// Check for forwarded IP address.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_addresses = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$requester_ip = trim( end( $ip_addresses ) );
		} elseif ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// Check for client IP address.
			$requester_ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			// Use remote address as fallback.
			$requester_ip = $_SERVER['REMOTE_ADDR'];
		}

		return $requester_ip ?? null;
	}
}
