<?php
/**
 * Plugin Name: F9members
 * Description: Manager for account user members.
 * Version: 1.0.0
 *
 * Text Domain: f9members
 *
 * @package  F9members
 * @category Core
 * @author   Fervidum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'F9members' ) ) :

	/**
	 * Main F9member Class.
	 *
	 * @class F9members
	 * @version 1.0.0
	 */
	final class F9members {

		/**
		 * The single instance of the class.
		 *
		 * @var F9members
		 */
		protected static $_instance = null;

		/**
		 * Query instance.
		 *
		 * @var F9members_Query
		 */
		public $query = null;

		/**
		 * Main F9members Instance.
		 *
		 * Ensures only one instance of F9members is loaded or can be loaded.
		 *
		 * @static
		 * @see F9members()
		 * @return F9members - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Setup class.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks() {
			add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
			if ( $this->is_request( 'frontend' ) ) {
				add_action( 'init', array( 'F9members_Shortcodes', 'init' ) );
			}

			add_action( 'after_setup_theme', array( __CLASS__, 'create_role' ) );
			if ( $this->is_request( 'admin' ) ) {
				add_action( 'show_user_profile', array( __CLASS__, 'add_user_fields' ), 10, 1 );
				add_action( 'edit_user_profile', array( __CLASS__, 'add_user_fields' ), 10, 1 );

				add_action( 'personal_options_update', array( $this, 'save_customer_meta_fields' ) );
				add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields' ) );
				add_filter( 'users_list_table_query_args', array( $this, 'list_table_query_args' ) );
				add_action( 'check_admin_referer', array( $this, 'check_admin_referer' ), 9, 2 );
				add_action( 'load-users.php', array( $this, 'update_action' ) );
			}
			add_action( 'woocommerce_endpoint_edit-account_title', array( $this, 'account_title' ) );

			add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
			add_filter( 'wp_authenticate_user', array( $this, 'authenticate_user' ) );
			add_filter( 'user_row_actions', array( $this, 'row_actions' ), 11, 2 );
		}

		/**
		 * Define F9members Constants.
		 */
		private function define_constants() {
			$this->define( 'F9MEMBERS_ABSPATH', dirname( __FILE__ ) . '/' );
			$this->define( 'F9MEMBERS_TEMPLATE_DEBUG_MODE', false );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string      $name Name.
		 * @param string|bool $value Value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Function used to Init F9members Template Functions - This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			include_once( F9MEMBERS_ABSPATH . 'includes/f9members-template-functions.php' );
		}

		/**
		 * Include functions.
		 */
		public function includes() {
			include_once( F9MEMBERS_ABSPATH . 'includes/f9members-functions.php' );
			include_once( F9MEMBERS_ABSPATH . 'includes/class-f9members-query.php' );

			if ( $this->is_request( 'admin' ) ) {
				include_once( F9MEMBERS_ABSPATH . 'includes/admin/class-f9members-admin-meta-boxes.php' );
				include_once( F9MEMBERS_ABSPATH . 'includes/admin/meta-boxes/class-f9members-meta-box-post-submit.php' );
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

			$this->query = new F9members_Query();
		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {
			include_once( F9MEMBERS_ABSPATH . 'includes/f9members-template-hooks.php' );
			include_once( F9MEMBERS_ABSPATH . 'includes/class-f9members-form-handler.php' ); // Form Handlers.

			include_once( F9MEMBERS_ABSPATH . 'includes/class-f9members-account.php' );      // Account class.
			include_once( F9MEMBERS_ABSPATH . 'includes/class-f9members-shortcodes.php' );   // Shortcodes class.

			include_once( F9MEMBERS_ABSPATH . 'includes/shortcodes/class-f9members-shortcode-account.php' );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'f9members_template_path', 'f9members/' );
		}

		/**
		 * Get Account Class.
		 *
		 * @return F9members_Account.
		 */
		public function account() {
			return F9members_Account::instance();
		}

		/**
		 * Create role and capabilities.
		 */
		public static function create_role() {
			global $wp_roles;

			if ( ! get_option( 'f9members_role_created' ) ) {

				if ( ! class_exists( 'WP_Roles' ) ) {
					return;
				}

				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}

				$member_role = apply_filters( 'f9members_member_role', 'member' );

				// Customer role
				add_role( $member_role, __( 'Membro', 'f9members' ), array(
					'read' => true,
				) );

				update_option( 'f9members_role_created', true );
			}
		}

		public static function add_user_fields( $user ) {
			$cpfcnpj = get_user_meta( $user->ID, 'cpfcnpj', true );
			$label = is_cpf( $cpfcnpj ) ? __( 'CPF', 'f9members' ) : __( 'CNPJ', 'f9members' );
			$cpfcnpj = format_cpfcnpj( $cpfcnpj );
			if ( is_cnpj( $cpfcnpj ) ) {
				$company = get_user_meta( $user->ID, 'company', true );
			}
			$edit_cnpfcnpj = apply_filters( 'f9members_block_cpfcnpj_edit', get_option( 'f9members_block_cpfcnpj_edit', true ) );
			$edit_disable = '';
			if ( ! $edit_cnpfcnpj ) {
				$edit_disable = ' disabled';
			}
			?>
			<table class="form-table texte">
				<tr>
					<th><label for="<?php echo esc_attr( 'cpfcnpj' ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( 'cpfcnpj' ); ?>" id="<?php echo esc_attr( 'cpfcnpj' ); ?>" value="<?php echo esc_attr( $cpfcnpj ); ?>" class="regular-text" <?php echo $edit_disable; ?>/>
						<?php if ( ! $edit_cnpfcnpj ) : ?>
						<span class="description"><?php esc_attr_e( sprintf( __( 'Não é possível alterar %s do membro.', 'f9members' ), $label ) ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<?php if ( is_cnpj( $cpfcnpj ) ) : ?>
				<tr class="texte2">
					<th><label for="<?php echo esc_attr( 'company' ); ?>"><?php echo esc_html( __( 'Empresa', 'f9members' ) ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( 'company' ); ?>" id="<?php echo esc_attr( 'company' ); ?>" value="<?php echo esc_attr( $company ); ?>" class="regular-text"/>
					</td>
				</tr>
				<?php endif; ?>
			</table>
			<?php
		}

		/**
		 * Save Member Fields on edit user pages.
		 *
		 * @param int $user_id User ID of the user being saved
		 */
		public function save_customer_meta_fields( $user_id ) {
			update_user_meta( $user_id, 'cpfcnpj', wc_clean( $_POST[ 'cpfcnpj' ] ) );
			update_user_meta( $user_id, 'company', wc_clean( $_POST[ 'company' ] ) );
		}

		public function account_title( $title ) {
			return __( 'Área do Cliente', 'ceb' );
		}

		public function login_redirect( $redirect_to, $request, $user ) {
			//is there a user to check?
			global $user;
			if ( isset( $user->roles ) && is_array( $user->roles ) ) {
				//check for admins

				$member_role = apply_filters( 'f9members_member_role', 'member' );
				if ( in_array( $member_role, $user->roles ) ) {
					// redirect them to the default place
					return f9downloads_page_files_url();
				} else {
					return $redirect_to;
				}
			} else {
				return $redirect_to;
			}
		}

		public function authenticate_user( $user ) {
			if ( $user->data->user_status ) {
				$user = new WP_Error( 'user_not_approved', __( '<strong>ERRO</strong>: Usuário aguardando aprovação.', 'f9members' ) );
			}
			return $user;
		}

		public function row_actions( $actions, $user ) {
			$member_role = apply_filters( 'f9members_member_role', 'member' );
			if ( in_array( $member_role, $user->roles ) && $user->data->user_status ) {

				$approve_url = esc_url( f9members_approve_link( $user->ID ) );
				$approve_title = esc_attr( __( 'Aprovar esse usuário', 'f9members' ) );
				$approve_text = esc_html( __( 'Aprovar', 'f9members' ) );
				$approve_link = sprintf( '<a style="color:#006505" href="%s" title="%s">%s</a>', $approve_url, $approve_title, $approve_text );

				$actions = array_merge( array( 'approves' => $approve_link ), $actions );
			}

			return $actions;
		}

		public function list_table_query_args( $args ) {
			$member_role = apply_filters( 'f9members_member_role', 'member' );
			$args['role'] = $member_role;
			return $args;
		}

		function check_admin_referer( $action, $result ) {

			if ( 'approve' === $action ) {
				preg_match( '/\d+$/', $action, $user_id );
				$user_id = (int) reset( $user_id );
				f9members_approve_user( $user_id );
			}
		}

		/**
		 * Update the user status if the approve or deny link was clicked.
		 *
		 * @uses load-users.php
		 */
		public function update_action() {
			if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'approve' ) ) ) {
				check_admin_referer( 'f9members-approve' );

				$sendback = remove_query_arg( array( 'approved' ), wp_get_referer() );
				if ( ! $sendback ) {
					$sendback = admin_url( 'users.php' );
				}

				$status = sanitize_key( $_GET['action'] );
				$user = absint( $_GET['user'] );

				f9members_approve_user( $user );

				if ( $_GET['action'] == 'approve' ) {
					$sendback = add_query_arg( array( 'approved' => 1, 'ids' => $user ), $sendback );
				}

				wp_redirect( $sendback );
				exit;
			}
		}
	}
endif;

/**
 * Main instance of F9members.
 *
 * Returns the main instance of F9members to prevent the need to use globals.
 *
 * @return F9members
 */
// @codingStandardsIgnoreStart
function F9members() {
	return F9members::instance();
}
// @codingStandardsIgnoreEnd

F9members();
