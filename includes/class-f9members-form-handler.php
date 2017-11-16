<?php
/**
 * Handle frontend forms.
 *
 * @class       F9members_Form_Handler
 * @version     2.2.0
 * @package     F9members/Classes/
 * @category    Class
 * @author      Fervidum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * F9members_Form_Handler Class.
 */
class F9members_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_loaded', array( __CLASS__, 'process_registration' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_optin' ), 20 );
	}

	/**
	 * Process the registration form.
	 *
	 * @throws Exception Throws an exception, on validation, anti-spam and customer.
	 */
	public static function process_registration() {
		// @codingStandardsIgnoreStart
		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['f9members-register-nonce'] ) ? $_POST['f9members-register-nonce'] : $nonce_value;
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreLine
		if ( ! empty( $_POST['register'] ) && wp_verify_nonce( $nonce_value, 'f9members-register' ) ) {
			$option_value = get_option( 'f9members_registration_generate_username', 'yes' );
			// @codingStandardsIgnoreStart
			$username = ( 'no' === $option_value ) ? $_POST['username'] : '';
			$option_value = get_option( 'f9members_registration_generate_password', 'yes' );
			$password = ( 'no' === $option_value ) ? $_POST['password'] : '';
			$email    = $_POST['email'];
			$fullname = $_POST['full_name'];
			$cpfcnpj  = $_POST['cpfcnpj'];
			$company  = isset( $_POST['company'] ) ? $_POST['company'] : '';
			$person   = isset( $_POST['person'] ) ? $_POST['person'] : '';
			// @codingStandardsIgnoreEnd

			if ( empty( $person ) ) {
				$person = maybe_cpf( $cpfcnpj ) ? 'natural' : 'legal';
			}

			// @codingStandardsIgnoreLine
			$_POST['person'] = $person;

			try {
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'f9members_process_registration_errors', $validation_error, $username, $password, $email, $fullname, $cpfcnpj );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( $validation_error->get_error_message() );
				}

				// Anti-spam trap.
				// @codingStandardsIgnoreLine
				if ( ! empty( $_POST['email_2'] ) ) {
					throw new Exception( __( 'Anti-spam field was filled in.', 'f9members' ) );
				}

				$new_member = f9members_create_new_member( sanitize_cpfcnpj( $cpfcnpj ), sanitize_email( $email ), wc_clean( $person ), wc_clean( $fullname ), wc_clean( $company ), wc_clean( $username ), $password );

				if ( is_wp_error( $new_member ) ) {
					throw new Exception( $new_member->get_error_message() );
				} else {
					wc_add_notice( __( 'Cadastro efetuado. Enviaremos um e-mail quando for aprovado.' ), 'success' );
					$_POST = array();
				}

//				wp_safe_redirect( apply_filters( 'f9members_registration_redirect', wp_get_referer() ? wp_get_referer() : f9members_get_register_url() ) );
//				exit;

			} catch ( Exception $e ) {
				wc_add_notice( '<strong>' . __( 'Erro:', 'f9members' ) . '</strong> ' . $e->getMessage(), 'error' );
			}
		}
	}

	/**
	 * Process the optin form.
	 *
	 * @throws Exception Throws an exception.
	 */
	public static function process_optin() {
		// @codingStandardsIgnoreStart
		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['f9members-optin-nonce'] ) ? $_POST['f9members-optin-nonce'] : $nonce_value;
		$redirect_to = isset( $_POST['_wp_http_referer'] ) ? $_POST['_wp_http_referer'] : '';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreLine
		if ( ! empty( $_POST['optin'] ) && wp_verify_nonce( $nonce_value, 'f9members-optin' ) ) {
			// @codingStandardsIgnoreLine
			$email    = $_POST['email'];

			try {
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'f9members_process_optin_errors', $email );

				if ( is_wp_error( $validation_error ) && $validation_error->get_error_code() ) {
					throw new Exception( $validation_error->get_error_message() );
				}

				// Anti-spam trap.
				// @codingStandardsIgnoreLine
				if ( ! empty( $_POST['email_2'] ) ) {
					throw new Exception( __( 'Anti-spam field was filled in.', 'f9members' ) );
				}

				$new_optin = f9members_create_new_optin( sanitize_email( $email ) );

				if ( is_wp_error( $new_optin ) ) {
					wp_safe_redirect( apply_filters( 'f9members_optin_redirect', add_query_arg( 'nl', 'a', $redirect_to ) . '#newsletter' ) );
					exit;
					throw new Exception( $new_optin->get_error_message() );
				} else {
					$mc_email = sanitize_email( $email );
					$mc_apikey = 'd2671bb467c91abec90326ed3c30dc88-us16';
					$mc_list_id = '916ba7c3f8';
					mc_subscribe( $mc_email, $mc_apikey, $mc_list_id );
					// wc_add_notice( __( 'Confirme seu cadastro no e-mail enviado.' ), 'success' );
					$_POST = array();
				}

				wp_safe_redirect( apply_filters( 'f9members_optin_redirect', add_query_arg( 'nl', 's', $redirect_to ) . '#newsletter' ) );
				exit;

			} catch ( Exception $e ) {
				wp_safe_redirect( apply_filters( 'f9members_optin_redirect', add_query_arg( 'nl', 'e', $redirect_to ) . '#newsletter' ) );
				// wc_add_notice( '<strong>' . __( 'Erro:', 'f9members' ) . '</strong> ' . $e->getMessage(), 'error' );
			}
		}
	}
}

F9members_Form_Handler::init();
