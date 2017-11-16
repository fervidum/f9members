<?php
/**
 * Register Form
 *
 * This template can be overridden by copying it to yourtheme/members/form-register.php.
 *
 * HOWEVER, on occasion F9members will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      Fervidum
 * @package     F9members/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'f9members_before_register_form' );
?>


<form method="post" class="register f9members-register f9members-register-natural">
	<fieldset>
		<legend><?php esc_html_e( 'Cadastre-se' ); ?></legend>

		<?php f9members_print_notices(); ?>

		<?php do_action( 'f9members_register_form_start' ); ?>

		<p class="form-row field-person validate-required" id="person_field" data-priority="">
			<label for="natural"><?php esc_html_e( 'Tipo', 'f9members' ); ?> <abbr class="required">*</abbr></label>
			<?php
			// @codingStandardsIgnoreLine
			$value = ( ! empty( $_POST['person'] ) ) ? $_POST['person'] : 'natural';
			?>
			<label for="person_natural" class="radio">
				<input type="radio" class="input-radio " value="natural" name="person" id="person_natural" <?php checked( $value, 'natural' ); ?>>
				<?php esc_html_e( 'Pessoa Física', 'f9members' ); ?>
			</label>
			<label for="person_legal" class="radio">
				<input type="radio" class="input-radio " value="legal" name="person" id="person_legal" <?php checked( $value, 'legal' ); ?>>
				<?php esc_html_e( 'Pessoa Jurídica', 'f9members' ); ?>
			</label>
		</p>

		<?php if ( 'no' === get_option( 'f9members_registration_generate_username', 'yes' ) ) : ?>

			<p class="f9members-form-row f9members-form-row--wide form-row form-row-wide">
				<label for="reg_username"><?php esc_html_e( 'Usuário', 'f9members' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Obrigatório', 'f9members' ); ?>">*</abbr></label>
				<?php
				// @codingStandardsIgnoreLine
				$value = ( ! empty( $_POST['username'] ) ) ? $_POST['username'] : '';
				?>
				<input type="text" class="f9members-Input f9members-Input--text input-text" name="username" id="reg_username" value="<?php echo esc_attr( $value ); ?>" />
			</p>

		<?php endif; ?>

		<p class="f9members-form-row f9members-form-row--wide form-row form-row-wide field-fullname">
			<label for="full_name"><?php esc_html_e( 'Nome', 'f9members' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Obrigatório', 'f9members' ); ?>">*</abbr></label>
			<?php
			// @codingStandardsIgnoreLine
			$value = ( ! empty( $_POST['full_name'] ) ) ? $_POST['full_name'] : '';
			?>
			<input type="text" class="f9members-Input f9members-Input--text input-text" name="full_name" id="full_name" value="<?php echo esc_attr( $value ); ?>" />
		</p>

		<p class="f9members-form-row f9members-form-row--wide form-row form-row-wide field-company">
			<label for="company"><?php esc_html_e( 'Empresa', 'f9members' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Obrigatório', 'f9members' ); ?>">*</abbr></label>
			<?php
			// @codingStandardsIgnoreLine
			$value = ( ! empty( $_POST['company'] ) ) ? $_POST['company'] : '';
			?>
			<input type="text" class="f9members-Input f9members-Input--text input-text" name="company" id="company" value="<?php echo esc_attr( $value ); ?>" />
		</p>

		<p class="f9members-form-row f9members-form-row--wide form-row form-row-wide field-email">
			<label for="reg_email"><?php esc_html_e( 'E-mail', 'f9members' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Obrigatório', 'f9members' ); ?>">*</abbr></label>
			<?php
			// @codingStandardsIgnoreLine
			$value = ( ! empty( $_POST['email'] ) ) ? $_POST['email'] : '';
			?>
			<input type="email" class="f9members-Input f9members-Input--text input-text" name="email" id="reg_email" value="<?php echo esc_attr( $value ); ?>" />
		</p>

		<p class="f9members-form-row f9members-form-row--wide form-row form-row-wide field-cpfcnpj">
			<label for="cpfcnpj"><?php esc_html_e( 'CPF/CNPJ', 'f9members' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Obrigatório', 'f9members' ); ?>">*</abbr></label>
			<?php
			// @codingStandardsIgnoreLine
			$value = ( ! empty( $_POST['cpfcnpj'] ) ) ? $_POST['cpfcnpj'] : '';
			?>
			<input type="text" class="f9members-Input f9members-Input--text input-text" name="cpfcnpj" id="cpfcnpj" value="<?php echo esc_attr( $value ); ?>" />
		</p>

		<?php if ( 'no' === get_option( 'f9members_registration_generate_password' ) ) : ?>

			<p class="f9members-form-row f9members-form-row--wide form-row form-row-wide">
				<label for="reg_password"><?php esc_html_e( 'Senha', 'f9members' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Obrigatório', 'f9members' ); ?>">*</abbr></label>
				<input type="password" class="f9members-Input f9members-Input--text input-text" name="password" id="reg_password" />
			</p>

		<?php endif; ?>

		<!-- Spam Trap -->
		<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php esc_html_e( 'Anti-spam', 'f9members' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" autocomplete="off" /></div>

		<?php do_action( 'f9members_register_form' ); ?>

		<p class="woocomerce-FormRow form-row field-submit">
			<?php wp_nonce_field( 'f9members-register', 'f9members-register-nonce' ); ?>
			<input type="submit" class="f9members-Button button" name="register" value="<?php esc_attr_e( 'Enviar', 'f9members' ); ?>" />
		</p>

		<?php do_action( 'f9members_register_form_end' ); ?>

	</fieldset>
</form>

<?php do_action( 'f9members_after_register_form' ); ?>
