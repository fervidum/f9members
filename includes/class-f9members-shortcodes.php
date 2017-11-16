<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * F9members_Shortcodes class
 *
 * @class       F9members_Shortcodes
 * @version     1.0.0
 * @package     F9members/Classes
 * @category    Class
 * @author      Fervidum
 */
class F9members_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'woocommerce_my_account'   => __CLASS__ . '::member_account',
			'f9members_account'        => __CLASS__ . '::member_account',
		);

		remove_shortcode( 'woocommerce_my_account' );

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function
	 * @param array $atts (default: array())
	 * @param array $wrapper
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'f9members',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		return ob_get_clean();
	}

	/**
	 * Member account page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function member_account( $atts ) {
		return self::shortcode_wrapper( array( 'F9members_Shortcode_Account', 'output' ), $atts );
	}
}
