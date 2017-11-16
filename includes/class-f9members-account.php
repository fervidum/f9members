<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account class.
 *
 * The F9members account class handles the register account process, collecting user data.
 *
 * @class    F9members_Account
 * @package  F9members/Classes
 * @category Class
 * @author   Fervidum
 */
class F9members_Account {

	/**
	 * The single instance of the class.
	 *
	 * @var F9members_Account|null
	 */
	protected static $instance = null;

	/**
	 * Checkout fields are stored here.
	 *
	 * @var array|null
	 */
	protected $fields = null;

	/**
	 * Gets the main F9members_Account Instance.
	 *
	 * @static
	 * @return F9members_Account Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			// Hook in actions once.
			add_action( 'f9members_form', array( self::$instance, 'members_form' ) );

			// Action members_account_init is ran once when the class is first constructed.
			do_action( 'f9members_account_init', self::$instance );
		}
		return self::$instance;
	}

	/**
	 * See if variable is set. Used to support legacy public variables which are no longer defined.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset( $key ) {
		return in_array( $key, array() );
	}

	/**
	 * Sets the legacy public variables for backwards compatibility.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
		}
	}

	/**
	 * Gets the legacy public variables for backwards compatibility.
	 *
	 * @param string $key
	 *
	 * @return array|string
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'account_fields' :
				return $this->get_account_fields();
		}
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		members_doing_it_wrong( __FUNCTION__, __( 'Trapaceando hein?', 'f9members' ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		members_doing_it_wrong( __FUNCTION__, __( 'Trapaceando hein?', 'f9members' ) );
	}

	/**
	 * Get an array of account fields.
	 *
	 * @param  string $fieldset to get.
	 * @return array
	 */
	public function get_account_fields( $fieldset = '' ) {
		if ( is_null( $this->fields ) ) {
			$this->fields['account']['email'] = array(
				'label'        => __( 'E-mail', 'f9members' ),
				'required'     => true,
				'type'         => 'email',
				'class'        => array( 'field-email' ),
				'validate'     => array( 'email' ),
				'autocomplete' => 'email',
				'priority'     => 110,
				'placeholder'  => esc_attr__( 'E-mail', 'f9members' ),
			);

			if ( 'no' === get_option( 'f9members_registration_generate_username', 'yes' ) ) {
				$this->fields['account']['username'] = array(
					'type'         => 'text',
					'class'        => array( 'field-username' ),
					'label'        => __( 'Usuário', 'f9members' ),
					'required'     => true,
					'placeholder'  => esc_attr__( 'Usuário', 'f9members' ),
				);
			}

			if ( 'no' === get_option( 'f9members_registration_generate_password', 'yes' ) ) {
				$this->fields['account']['password'] = array(
					'type'         => 'password',
					'class'        => array( 'field-password' ),
					'label'        => __( 'Senha', 'f9members' ),
					'required'     => true,
					'placeholder'  => esc_attr__( 'Senha', 'f9members' ),
				);
			}

			$get_default_address_fields = WC()->countries->get_default_address_fields();
			$removes = array( 'country', 'address_1', 'city', 'state', 'postcode' );
			foreach ( $removes as $remove ) {
				unset( $get_default_address_fields[ $remove ] );
			}
			$get_default_address_fields['full_name']['class'] = array( 'field-fullname' );
			$get_default_address_fields['full_name']['cpf']   = array( 'field-cpf' );
			$get_default_address_fields['full_name']['cnpj']  = array( 'field-cnpj' );

			$this->fields['account'] = array_merge( $get_default_address_fields, $this->fields['account'] );

			$person_field = array(
				'person' => array(
					'type'         => 'radio',
					'class'        => array( 'field-person' ),
					'options'      => array(
							'natural' => esc_attr__( 'Pessoa Física', 'f9members' ),
							'legal'   => esc_attr__( 'Pessoa Jurídica', 'f9members' ),
						),
					'label'        => __( 'Tipo', 'f9members' ),
					'required'     => true,
				),
			);

			$this->fields['account'] = array_merge( $person_field, $this->fields['account'] );

			$new_fields = array();
			$new_fields['person'] = $this->fields['account']['person'];
			$new_fields['full_name'] = $this->fields['account']['full_name'];
			$new_fields['full_name']['placeholder'] = esc_attr__( 'Nome', 'f9members' );
			$new_fields['email'] = $this->fields['account']['email'];
			$new_fields['email']['placeholder'] = esc_attr__( 'E-mail', 'f9members' );
			$new_fields['cpfcnpj'] = array(
				'type'         => 'text',
				'class'        => array( 'field-cpf' ),
				'validate'     => array( 'cpfcnpj' ),
				'autocomplete' => 'cpfcnpj',
				'label'        => esc_html__( 'CPF/CNPJ', 'f9members' ),
				'required'     => true,
				'placeholder'  => esc_attr__( 'CPF/CNPJ', 'f9members' ),
			);
			$this->fields['account'] = $new_fields;

			$this->fields = apply_filters( 'f9members_account_fields', $this->fields );
		}
		if ( $fieldset ) {
			return $this->fields[ $fieldset ];
		} else {
			return $this->fields;
		}
	}

	/**
	 * Gets the value either from the posted data, or from the users meta data.
	 *
	 * @param string $input
	 * @return string
	 */
	public function get_value( $input ) {
		if ( ! empty( $_POST[ $input ] ) ) {
			return wc_clean( $_POST[ $input ] );

		} else {

			$value = apply_filters( 'f9members_account_get_value', null, $input );

			if ( null !== $value ) {
				return $value;
			}

			if ( is_callable( array( WC()->customer, "get_$input" ) ) ) {
				$value = WC()->customer->{"get_$input"}() ? WC()->customer->{"get_$input"}() : null;
			} elseif ( WC()->customer->meta_exists( $input ) ) {
				$value = WC()->customer->get_meta( $input, true );
			}

			return apply_filters( 'default_account_' . $input, $value, $input );
		}
	}
}
