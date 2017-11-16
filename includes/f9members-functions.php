<?php
/**
 * F9members Functions
 *
 * Functions available on both the front-end and admin.
 *
 * @author      Fervidum
 * @package     F9members/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get other templates (e.g. login form) passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function f9members_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = f9members_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		f9members_doing_it_wrong( __FUNCTION__, sprintf( __( '%s não existe.', 'f9members' ), '<code>' . $located . '</code>' ), '1.0' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'f9members_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'f9members_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'f9members_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /    $template_name
 *      yourtheme       /   $template_path
 *      $default_path   /   $template_path
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function f9members_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = F9members()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = F9members()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/
	if ( ! $template || F9MEMBERS_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'f9members_locate_template', $template, $template_name, $template_path );
}


/**
 * Wrapper for members_doing_it_wrong.
 *
 * @param  string $function
 * @param  string $version
 * @param  string $replacement
 */
function f9members_doing_it_wrong( $function, $message, $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( is_ajax() ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
}

/**
 * Gets the url to the register page.
 *
 * @return string Url to register page
 */
function f9members_get_register_url() {
	$register_url = wc_get_account_endpoint_url( 'cadastro' );
	if ( $register_url ) {
		// Force SSL if needed
		if ( is_ssl() || 'yes' === get_option( 'f9members_force_ssl' ) ) {
			$register_url = str_replace( 'http:', 'https:', $register_url );
		}
	}

	return apply_filters( 'f9members_get_register_url', $register_url );
}

/**
 * Get account menu item classes.
 *
 * @param string $endpoint
 * @return string
 */
function f9members_get_menu_item_classes( $endpoint ) {
	global $wp;

	$classes = array(
		'f9members-navigation-link',
		'f9members-navigation-link--' . $endpoint,
	);

	// Set current item class.
	$current = isset( $wp->query_vars[ $endpoint ] );
	if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
		$current = true; // Dashboard is not an endpoint, so needs a custom check.
	}

	if ( $current ) {
		$classes[] = 'is-active';
	}

	$classes = apply_filters( 'f9members_menu_item_classes', $classes, $endpoint );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Get members endpoint URL.
 *
 * @param string $endpoint
 * @return string
 */
function f9members_get_endpoint_url( $endpoint ) {
	if ( 'dashboard' === $endpoint ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return members_get_endpoint_url( $endpoint, '', wc_get_page_permalink( 'myaccount' ) );
}

/**
 * Prints messages and errors which are stored in the session, then clears them.
 */
function f9members_print_notices() {
	if ( ! did_action( 'woocommerce_init' ) ) {
		wc_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before woocommerce_init.', 'woocommerce' ), '2.3' );
		return;
	}

	$all_notices  = WC()->session->get( 'wc_notices', array() );
	$notice_types = apply_filters( 'f9members_notice_types', array( 'error', 'success', 'notice' ) );

	foreach ( $notice_types as $notice_type ) {
		if ( wc_notice_count( $notice_type ) > 0 ) {
			wc_get_template( "notices/{$notice_type}.php", array(
				'messages' => array_filter( $all_notices[ $notice_type ] ),
			) );
		}
	}

	wc_clear_notices();
}

if ( ! function_exists( 'f9members_create_new_member' ) ) {

	/**
	 * Create a new member.
	 *
	 * @param  string $email Member email.
	 * @param  string $username Member username.
	 * @param  string $password Member password.
	 * @param  string $fullname Member fullname.
	 * @param  string $cpfcnpj Member CPF or CNPJ.
	 * @return int|WP_Error Returns WP_Error on failure, Int (user ID) on success.
	 */
	function f9members_create_new_member(  $cpfcnpj, $email, $person, $fullname, $company, $username = '', $password = '' ) {
		global $wpdb;

		// Check empty CPF/CNPJ.
		if ( empty( $cpfcnpj ) ) {
			$label = ( 'natural' === $person ) ? __( 'CPF', 'f9members' ) : __( 'CNPJ', 'f9members' );
			return new WP_Error( 'registration-error-invalid-cnpjcpf', sprintf( __( 'Por favor informe o %s válido.', 'f9members' ), $label ) );
		}

		if ( cpfcnpj_exists( $cpfcnpj ) ) {
			$label = is_cpf( $cpfcnpj ) ? __( 'CPF', 'f9members' ) : __( 'CNPJ', 'f9members' );
			return new WP_Error( 'registration-error-cpfcnpj-exists', sprintf( __( 'Já existe uma conta registrada com o seu %s. Faça login.', 'f9members' ), $label ) );
		}

		// Check the email address.
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address.', 'woocommerce' ) );
		}

		if ( email_exists( $email ) ) {
			return new WP_Error( 'registration-error-email-exists', __( 'An account is already registered with your email address. Please log in.', 'woocommerce' ) );
		}

		// Handle username creation.
		if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) || ! empty( $username ) ) {
			$username = sanitize_user( $username );

			if ( empty( $username ) || ! validate_username( $username ) ) {
				return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'woocommerce' ) );
			}

			if ( username_exists( $username ) ) {
				return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'woocommerce' ) );
			}
		} else {
			$username = sanitize_user( current( explode( '@', $email ) ), true );

			// Ensure username is unique.
			$append     = 1;
			$o_username = $username;

			while ( username_exists( $username ) ) {
				$username = $o_username . $append;
				$append++;
			}
		}

		// Handle password creation.
		if ( 'yes' === get_option( 'f9members_registration_generate_password', 'yes' ) && empty( $password ) ) {
			$password           = wp_generate_password();
			$password_generated = true;
		} elseif ( empty( $password ) ) {
			return new WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'woocommerce' ) );
		} else {
			$password_generated = false;
		}

		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();

		do_action( 'f9members_register_post', $username, $email, $errors );

		$errors = apply_filters( 'f9members_registration_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$new_member_data = apply_filters( 'f9members_new_customer_data', array(
			'user_login'   => $username,
			'user_pass'    => $password,
			'user_email'   => $email,
			'role'         => 'member',
			'user_status'  => 1,
			'cpfcnpj'      => $cpfcnpj,
			'display_name' => $fullname,
		) );

		if ( $company ) {
			$new_member_data['company'] = $company;
		}

		preg_match( '/(?P<first_name>.+)\s(?P<last_name>.+)/', $fullname, $fullname_parts );
		if ( isset( $fullname_parts ) && isset( $fullname_parts['first_name'] ) ) {
			$new_member_data['first_name'] = $fullname_parts['first_name'];
		} else {
			$new_member_data['first_name'] = $fullname;
		}
		if ( isset( $fullname_parts ) && isset( $fullname_parts['last_name'] ) ) {
			$new_member_data['last_name'] = $fullname_parts['last_name'];
		}

		$member_id = wp_insert_user( $new_member_data );

		if ( is_wp_error( $member_id ) ) {
			return new WP_Error( 'registration-error', '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'woocommerce' ) );
		} else {
			add_user_meta( $member_id, 'cpfcnpj', $new_member_data['cpfcnpj'], true );
			if ( isset( $new_member_data['company'] ) ) {
				add_user_meta( $member_id, 'company', $new_member_data['company'], true );
			}

			if ( isset( $new_member_data['user_status'] ) && $new_member_data['user_status'] === 1 ) {
				$wpdb->update(
					$wpdb->users,
					array( 'user_status' => 1 ),
					array( 'ID' => $member_id ),
					array( '%d' ),
					array( '%d' )
				);
			}

			f9members_notify_moderator_user( $member_id );
		}

		do_action( 'f9members_created_member', $member_id, $new_member_data, $password_generated );

		return $member_id;
	}
}

if ( ! function_exists( 'f9members_create_new_optin' ) ) {

	/**
	 * Create a new subscriber.
	 *
	 * @param  string $email Subscriber email.
	 * @return int|WP_Error Returns WP_Error on failure, Int (user ID) on success.
	 */
	function f9members_create_new_optin(  $email ) {
		global $wpdb;

		// Check the email address.
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address.', 'woocommerce' ) );
		}

		if ( email_exists( $email ) ) {
			if ( email_exists( $email ) ) {
				return new WP_Error( 'optin-error-email-exists', __( 'E-mail já cadastrado.', 'ceb' ) );
			}
		}

		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// Ensure username is unique.
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;
			$append++;
		}

		// Handle password creation.
		$password           = wp_generate_password();
		$password_generated = true;

		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();

		do_action( 'f9members_optin_post', $username, $email, $errors );

		$errors = apply_filters( 'f9members_optin_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$new_optin_data = apply_filters( 'f9members_new_customer_data', array(
			'user_login'   => $username,
			'user_pass'    => $password,
			'user_email'   => $email,
			'role'         => 'subscriber',
			'user_status'  => 1,
		) );

		$optin_id = wp_insert_user( $new_optin_data );

		if ( is_wp_error( $optin_id ) ) {
			return new WP_Error( 'subscribe-error', '<strong>' . __( 'Erro:', 'f9members' ) . '</strong> ' . __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'f9members' ) );
		} else {

			if ( isset( $new_optin_data['user_status'] ) && $new_optin_data['user_status'] === 1 ) {
				$wpdb->update(
					$wpdb->users,
					array( 'user_status' => 1 ),
					array( 'ID' => $optin_id ),
					array( '%d' ),
					array( '%d' )
				);
			}
		}

		do_action( 'f9members_created_member', $optin_id, $new_optin_data, $password_generated );

		return $optin_id;
	}
}

if ( ! function_exists( 'numbers' ) ) {
	function numbers( $string ) {
		return preg_replace( '/\D/', '', $string );
	}
}

if ( ! function_exists( 'sanitize_cpfcnpj' ) ) {
	function sanitize_cpfcnpj( $raw ) {
		if ( maybe_cnpj( $raw ) ) {
			$sanitized = sanitize_cnpj( $raw );
		} else {
			$sanitized = sanitize_cpf( $raw );
		}
		return $sanitized;
	}
}

if ( ! function_exists( 'sanitize_cpf' ) ) {
	function sanitize_cpf( $raw ) {
		$sanitized = format_cpf( $raw, false );
		if ( ! is_cpf( $sanitized ) ) {
			$sanitized = '';
		}
		return $sanitized;
	}
}

if ( ! function_exists( 'sanitize_cnpj' ) ) {
	function sanitize_cnpj( $raw ) {
		$sanitized = format_cnpj( $raw, false );
		if ( ! is_cnpj( $sanitized ) ) {
			$sanitized = '';
		}
		return $sanitized;
	}
}

if ( ! function_exists( 'validate_cpf' ) ) {
	function validate_cpf( $cpf ) {
		// Mantêm apenas números, insere zeros a esquerda e garante tamanho correto.
		return substr( str_pad( numbers( $cpf ), 11, 0, STR_PAD_LEFT ), -11 );
	}
}

if ( ! function_exists( 'validate_cnpj' ) ) {
	function validate_cnpj( $cnpj ) {
		return substr( str_pad( numbers( $cnpj ), 14, 0, STR_PAD_LEFT ), -14 );
	}
}

if ( ! function_exists( 'format_cpfcnpj' ) ) {
	function format_cpfcnpj( $cpfcnpj ) {
		if ( maybe_cnpj( $cpfcnpj ) ) {
			$formatted = format_cnpj( $cpfcnpj );
		} else {
			$formatted = format_cpf( $cpfcnpj );
		}
		return $formatted;
	}
}

if ( ! function_exists( 'format_cpf' ) ) {
	function format_cpf( $cpf, $punctuation = true ) {
		$cpf = validate_cpf( $cpf );
		if ( $punctuation ) {
			$cpf = mask_cpf( $cpf );
		}
		return $cpf;
	}
}

if ( ! function_exists( 'format_cnpj' ) ) {
	function format_cnpj( $cnpj, $punctuation = true ) {
		$cnpj = validate_cnpj( $cnpj );
		if ( $punctuation ) {
			$cnpj = mask_cnpj( $cnpj );
		}
		return $cnpj;
	}
}

if ( ! function_exists( 'mask' ) ) {
	function mask( $string, $mask ) {
		$maskared = '';
		$k = 0;
		$len = strlen( $mask ) - 1;
		for ( $i = 0; $i <= $len; $i++) {
			if ( $mask[$i] == '#' ) {
				if ( isset( $string[$k] ) ) {
					$maskared .= $string[$k++];
				}
			} else {
				if ( isset( $mask[$i ] ) ) {
					$maskared .= $mask[$i];
				}
			}
		}
		return $maskared;
	}
}

if ( ! function_exists( 'mask_cpf' ) ) {
	function mask_cpf( $cpf, $display = false ) {
		$cpf = format_cpf( $cpf, false );

		if ( maybe_cpf( $cpf ) ) {
			$formatted = mask( $cpf, '###.###.###-##' );
		} else {
			$formatted = '';
		}

		if ( ! $display ) {
			return $formatted;
		}
		echo $formatted;
	}
}

if ( ! function_exists( 'mask_cnpj' ) ) {
	function mask_cnpj( $cnpj, $display = false ) {
		$cnpj = format_cnpj( $cnpj, false );

		if ( is_cnpj( $cnpj ) ) {
			$formatted = mask( $cnpj, '##.###.###/####-##' );
		} else {
			$formatted = '';
		}

		if ( ! $display ) {
			return $formatted;
		}
		echo $formatted;
	}
}

if ( ! function_exists( 'maybe_cpf' ) ) {
	function maybe_cpf( $maybe_cpf ) {
		$maybe_cpf = numbers( $maybe_cpf );
		return strlen( $maybe_cpf ) <= 11;
	}
}

if ( ! function_exists( 'maybe_cnpj' ) ) {
	function maybe_cnpj( $maybe_cnpj ) {
		return ! maybe_cpf( $maybe_cnpj );
	}
}

if ( ! function_exists( 'is_cpf' ) ) {
	function is_cpf( $cnpj ) {
		$cnpj = validate_cpf( $cnpj );

		// Ignora falsos positivos quando possui somente repetição de um único número
		if ( preg_match( '/^(\d)\1+$/', $cnpj ) ) {
			return false;
		}

		// Interação para cada dígito verificador
		for ($n = 0; $n < 2; $n++) {
			$sum = 0;
			// Multiplica as posições por descescentes 10 e 11 na segunda interação, acumulando a soma
			for ($i = 10 + $n; $i > 1; $i--) {
				$sum += substr( $cnpj, abs( $i - 10 - $n ), 1 ) * $i;
			}
			// Calcula o módulo 11 para o dígito calculado
			$d = 11 - $sum % 11;
			// Caso 10 substitui por 0
			$d = $d <= 9 ? $d : 0;
			// Falso quando não combinar o dígito calculado com o verificar
			if ( intval( substr( $cnpj, 9 + $n, 1 ) ) !== $d ) {
				return false;
			}
		}
		return true;
	}
}

if ( ! function_exists( 'is_cnpj' ) ) {
	function is_cnpj( $cnpj ) {
		$cnpj = validate_cnpj( $cnpj );

		// Ignora falsos positivos quando possui somente repetição de um único número
		if ( preg_match( '/^(\d)\1+$/', $cnpj ) ) {
			return false;
		}

		// Interação em cada grupo de multiplicadores
		foreach( array( '543298765432', '6543298765432' ) as $n => $f ) {
			$sum = 0;
			// Multiplica cada fator por sua posição na interação acumulando a soma
			for ($i = 0; $i < strlen( $f ); $i++) {
				$sum += substr( $cnpj, $i, 1 ) * substr( $f, $i, 1 );
			}
			// Calcula o módulo 11 para o dígito calculado
			$d = 11 - $sum % 11;
			// Caso 10 substitui por 0
			$d = $d <= 9 ? $d : 0;
			// Falso quando não combinar o dígito calculado com o verificar
			if ( intval( substr( $cnpj, 12 + $n, 1 ) ) !== $d ) {
				return false;
			}
		}
		return true;
	}
}

/**
 * Check if CPF/CNPJ exists.
 */
function cpfcnpj_exists( $cpfcnpj ) {
	$cpfcnpj = sanitize_cpfcnpj( $cpfcnpj );
	$args = array(
		'meta_key'   => 'cpfcnpj',
		'meta_value' => $cpfcnpj,
		'fields'     => array( 'ID' ),
	);

	$exists = false;
	if ( get_users( $args ) ) {
		$exists = true;
	}
	return $exists;
}

function mc_subscribe( $email, $apikey, $listid ) {
	preg_match( '/-(us\d+)$/', $apikey, $server );
	if ( $server ) {
		$server = $server[1];
	} else {
		$server = '';
	}
	$auth = base64_encode( 'user:' . $apikey );
	$list_url = "https://$server.api.mailchimp.com/3.0/lists/$listid/members";
	$response = wp_remote_post( $list_url, array(
			'method' => 'POST',
			'headers' => array(
				'Authorization' => 'Basic ' . $auth,
				'Content-type'  => 'application/json',
			),
			'body' => json_encode( array( 'email_address' => $email, 'status' => 'subscribed' ) )
		)
	);
};

function f9members_awaiting_aproval() {
	global $wpdb;
	return $wpdb->get_var( "SELECT count(ID) FROM $wpdb->users WHERE user_status = '1'" );
}

function f9members_notify_moderator_user( $user_id ) {
	global $wpdb;

	$user = get_userdata( $user_id );

	$cpfcnpj = get_user_meta( $user_id, 'cpfcnpj', true );
	$cpfcnpj_label = is_cnpj( $cpfcnpj ) ? __( 'CNPJ: %s' ) : __( 'CPF: %s' );

	$user = get_object_vars( $user->data );

	$user['cpfcnpj'] = $cpfcnpj;

	if ( is_cnpj( $cpfcnpj ) ) {
		$user['cpfcnpj'] = get_user_meta( $user_id, 'company', true );
	}

	$user = (Object) $user;

	$emails = array( get_option( 'admin_email' ) );

	$users_pending = f9members_awaiting_aproval();

	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES);

	$notify_message  = __( 'Um novo usuário está aguardando aprovação:' ) . "\r\n\r\n";

	$notify_message .= sprintf( __( 'E-mail: %s' ), $user->user_email ) . "\r\n";
	$notify_message .= sprintf( $cpfcnpj_label, format_cpfcnpj( $user->cpfcnpj ) ) . "\r\n\r\n";

	if ( $user->company ) {
		$notify_message .= sprintf( __( 'Empresa: %s' ), $user->company ) . "\r\n\r\n";
	}

	$notify_message .= sprintf( __( 'Aprovar: %s' ), admin_url( f9members_approve_link( $user_id ) ) ) . "\r\n";

	$member_role = apply_filters( 'f9members_member_role', 'member' );

	$notify_message .= sprintf( _n( 'No momento, %s usuário aguarda aprovação. Visite o painel:',
 		'No momento, %s usuários aguardam aprovação. Visite o painel:', $users_pending ), number_format_i18n( $users_pending ) ) . "\r\n";
	$notify_message .= admin_url( 'users.php?role=' . $member_role ) . "\r\n";

	$subject = sprintf( __('[%s] Aprovar novo usuário'), $blogname );
	$message_headers = '';

	$emails = apply_filters( 'user_moderation_recipients', $emails, $user_id );
	$notify_message = apply_filters( 'user_moderation_text', $notify_message, $user_id );
	$subject = apply_filters( 'user_moderation_subject', $subject, $user_id );
	$message_headers = apply_filters( 'user_moderation_headers', $message_headers, $user_id );

	foreach ( $emails as $email ) {
		wp_mail( $email, wp_specialchars_decode( $subject ), $notify_message, $message_headers );
	}

	return true;
}

function f9members_approve_user( $user_id ) {
	global $wpdb;

	$wpdb->update(
		$wpdb->users,
		array( 'user_status' => 0 ),
		array( 'ID' => $user_id ),
		array( '%d' ),
		array( '%d' )
	);

	$user_pass = wp_generate_password( 6, false );

	update_user_meta( $user_id, 'user_pass', wp_hash_password( $user_pass ) );

	f9members_new_user_notification( $user_id, $user_pass );
}

function f9members_new_user_notification( $user_id ) {

	global $wpdb, $wp_hasher;
	$user = get_userdata( $user_id );

	// Generate something random for a password reset key.
	$key = wp_generate_password( 20, false );

	/** This action is documented in wp-login.php */
	do_action( 'retrieve_password_key', $user->user_login, $key );

	// Now insert the key, hashed, into the DB.
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

	$message = sprintf( __( 'E-mail: %s' ), $user->user_email ) . "\r\n\r\n";
	$message .= __( 'Para definir sua senha, acesse:' ) . "\r\n\r\n";
	$message .= '<a href="' . admin_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";

	$message .= wp_login_url() . "\r\n";
	$title = sprintf( __( '[%s] Defina sua senha' ), $blogname );
	wp_mail( $user->user_email, wp_specialchars_decode( $title ), $message );
}


function f9members_approve_link( $id ) {
	if ( ! $user = get_user_by( 'id',  $id ) ) {
		return;
	}

	$action = 'approve';

	$args = array(
		'action'   => $action,
		'user'     => $user->ID,
	);
	$url = add_query_arg( $args, 'users.php' );

	return wp_nonce_url( $url, "f9members-$action" );
}
