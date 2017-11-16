<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * F9members Account Shortcode
 *
 * Used on the account page, the account shortcode displays the register process.
 *
 * @author      Fervidum
 * @category    Shortcodes
 * @package     F9members/Shortcodes/Account
 * @version     1.0.0
 */
class F9members_Shortcode_Account {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function get( $atts ) {
		return F9members_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts
	 */
	public static function output( $atts ) {
		global $wp;

		// Check cart class is loaded or abort
		if ( is_null( WC()->cart ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			$message = apply_filters( 'woocommerce_my_account_message', '' );

			if ( ! empty( $message ) ) {
				wc_add_notice( $message );
			}

			// After password reset, add confirmation message.
			if ( ! empty( $_GET['password-reset'] ) ) {
				wc_add_notice( __( 'Your password has been reset successfully.', 'woocommerce' ) );
			}

			// Handle member actions
			if ( isset( $wp->query_vars['member-register'] ) ) {
				self::member_register();
			}

			if ( isset( $wp->query_vars['lost-password'] ) ) {
				self::lost_password();
			} else {
				wc_get_template( 'myaccount/form-login.php' );
			}
		} else {
			// Start output buffer since the html may need discarding for BW compatibility
			ob_start();

			if ( isset( $wp->query_vars['customer-logout'] ) ) {
				wc_add_notice( sprintf( __( 'Are you sure you want to log out? <a href="%s">Confirm and log out</a>', 'woocommerce' ), wc_logout_url() ) );
			}

			// Collect notices before output
			$notices = wc_get_notices();

			// Output the new account page
			self::account( $atts );

			// Send output buffer
			ob_end_flush();
		}
	}

	/**
	 * My account page.
	 *
	 * @param array $atts
	 */
	private static function account( $atts ) {
		extract( shortcode_atts( array(
		), $atts, 'f9members_account' ) );

		f9members_get_template( 'account.php', array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	 * Show the register page.
	 */
	private static function member_register() {

		f9members_get_template( 'form-register.php' );
	}
}
