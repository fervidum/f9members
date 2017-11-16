<?php
/**
 * F9members Template
 *
 * Functions for the templating system.
 *
 * @author   Fervidum
 * @category Core
 * @package  F9members/Functions
 * @version  1.0.0
 */

/** Login *****************************************************************/

if ( ! function_exists( 'f9members_login_form' ) ) {

	/**
	 * Output the F9members Login Form.
	 *
	 * @subpackage  Forms
	 * @param array $args Arguments.
	 */
	function f9members_login_form( $args = array() ) {

		$defaults = array(
			'echo'     => true,
			'message'  => '',
			'redirect' => '',
			'hidden'   => false,
		);

		$args = wp_parse_args( $args, $defaults );

		ob_start();

		f9members_get_template( 'form-login.php', $args );

		$form = ob_get_clean();

		if ( $args['echo'] ) {
			echo $form;
		} else {
			return $form;
		}
	}
}

if ( ! function_exists( 'f9members_account_navigation' ) ) {
	function f9members_account_navigation() {
		$files_page_id = downloads_get_page_id( 'files' );
		$post = get_post( $files_page_id );
		$files_permalink = get_permalink( $post );
		?>
		<div class="account-content">
			<div class="member-menu cart-nav">
				<ul>
					<li><a href="<?php esc_url( $files_permalink ); ?>">Documentos disponíveis</a></li>
					<li class="active"><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>">Editar
					minha conta</a></li>
					<li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Sair da
					área do cliente</a></li>
				</ul>
			</div>
			<div class="account-form">
		<?php
	}
}

/**
 * Add body classes for F9members pages.
 *
 * @param  array $classes
 * @return array
 */
function f9members_body_class( $classes ) {
	global $wp;

	$classes = (array) $classes;

	if ( is_account_page() ) {

		$classes[] = 'f9members-account';
		$classes[] = 'f9members-page';

		if ( ! isset( $wp->query_vars['member-register'] ) ) {
			$classes[] = 'f9members-account-edit';
		}

	}


	foreach ( F9members()->query->query_vars as $key => $value ) {
		if ( is_wc_endpoint_url( $key ) ) {
			$classes[] = 'f9members-' . sanitize_html_class( $key );
		}
	}

	return array_unique( $classes );
}
