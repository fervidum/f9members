<?php
/**
 * Account page
 *
 * This template can be overridden by copying it to yourtheme/f9members/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Fervidum
 * @package F9members/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

f9members_print_notices();

$files_permalink = f9downloads_page_files_url();

/**
 * Account navigation.
 */
do_action( 'f9members_account_navigation' ); ?>

<div class="f9members-Account-content">
	<?php
		/**
		 * Account content.
		 */
		do_action( 'f9members_account_content' );
	?>
</div>
	</div>
</div>
