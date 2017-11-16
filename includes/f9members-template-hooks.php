<?php
/**
 * F9members Template Hooks
 *
 * Action/filter hooks used for F9members functions/templates.
 *
 * @author      Fervidum
 * @category    Core
 * @package     F9members/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'body_class', 'f9members_body_class' );


/**
 * Account.
 */
add_action( 'f9members_account_navigation', 'f9members_account_navigation' );
add_action( 'f9members_account_content', 'woocommerce_account_content' );
