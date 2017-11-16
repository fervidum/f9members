<?php
/**
 * Login form
 *
 * This template can be overridden by copying it to yourtheme/f9members/form-login.php.
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
	exit; // Exit if accessed directly
}

if ( is_user_logged_in() ) {
	return;
}

?>
<form class="members-form members-form-login login" method="post" <?php echo ( $hidden ) ? 'style="display:none;"' : ''; ?> action="<?php echo wp_login_url(); ?>">

	<?php do_action( 'f9members_login_form_start' ); ?>

	<?php echo ( $message ) ? wpautop( wptexturize( $message ) ) : ''; ?>

	<p class="form-row form-row-first">
		<label for="username"><?php _e( 'Usuário ou E-mail', 'f9members' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="username" id="username" />
	</p>
	<p class="form-row form-row-last">
		<label for="password"><?php _e( 'Senha', 'f9members' ); ?> <span class="required">*</span></label>
		<input class="input-text" type="password" name="password" id="password" />
	</p>
	<div class="clear"></div>

	<?php do_action( 'f9members_login_form' ); ?>

	<p class="form-row">
		<?php wp_nonce_field( 'f9members-login' ); ?>
		<input type="submit" class="button" name="login" value="<?php esc_attr_e( 'Entrar', 'f9members' ); ?>" />
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		<label class="members-form__label members-form__label-for-checkbox inline">
			<input class="members-form__input members-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php _e( 'Lembre-me', 'f9members' ); ?></span>
		</label>
	</p>
	<p class="lost_password">
		<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Perdeu sua senha?', 'f9members' ); ?></a>
	</p>

	<?php do_action( 'f9members_login_form_end' ); ?>

</form>
