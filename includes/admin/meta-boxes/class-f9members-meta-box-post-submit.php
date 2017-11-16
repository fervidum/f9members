<?php
/**
 * Replace default post submit forms.
 *
 * @author      Fervidum
 * @category    Admin
 * @package     F9members/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * F9members_Meta_Box_Post_Submit Class.
 */
class F9members_Meta_Box_Post_Submit {

	/**
	 * Output the metabox.
	 *
	 * @global $action
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function output( $post ) {
		global $action;

		$post_type        = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$can_publish      = current_user_can( $post_type_object->cap->publish_posts );
		?>
		<div class="submitbox" id="submitpost">

			<div id="minor-publishing">

			<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key. ?>
			<div style="display:none;">
			<?php submit_button( __( 'Save' ), '', 'save' ); ?>
			</div>

			<div id="minor-publishing-actions">
			<div id="save-action">
			<?php if ( 'publish' !== $post->post_status && 'future' !== $post->post_status && 'pending' !== $post->post_status ) { ?>
			<input <?php if ( 'private' === $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save Draft' ); ?>" class="button" />
			<span class="spinner"></span>
			<?php } elseif ( 'pending' === $post->post_status && $can_publish ) { ?>
			<input type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save as Pending' ); ?>" class="button" />
			<span class="spinner"></span>
			<?php } ?>
			</div>
			<?php if ( is_post_type_viewable( $post_type_object ) ) : ?>
			<div id="preview-action">
			<?php
			$preview_link = esc_url( get_preview_post_link( $post ) );
			if ( 'publish' === $post->post_status ) {
				$preview_button_text = __( 'Preview Changes' );
			} else {
				$preview_button_text = __( 'Preview' );
			}

			$preview_button = sprintf( '%1$s<span class="screen-reader-text"> %2$s</span>',
				$preview_button_text,
				/* translators: accessibility text */
				__( '(opens in a new window)' )
			);
			$allowed_html = array(
				'span' => array(
					'class' => array(),
				),
			);
			?>
			<a class="preview button" href="<?php echo esc_url( $preview_link ); ?>" target="wp-preview-<?php echo (int) $post->ID; ?>" id="post-preview"><?php echo wp_kses( $preview_button, $allowed_html ); ?></a>
			<input type="hidden" name="wp-preview" id="wp-preview" value="" />
			</div>
			<?php endif; // Public post type. ?>
			<?php
			/**
			 * Fires before the post time/date setting in the Publish meta box.
			 *
			 * @since 4.4.0
			 *
			 * @param WP_Post $post WP_Post object for the current post.
			 */
			do_action( 'post_submitbox_minor_actions', $post );
			?>
			<div class="clear"></div>
			</div><!-- #minor-publishing-actions -->

			<div id="misc-publishing-actions">

			<div class="misc-pub-section misc-pub-post-status">
			<?php esc_html_e( 'Status:' ); ?> <span id="post-status-display"><?php

			if ( 'private' === $post->post_status && 'yes' === get_post_meta( $post->ID, '_members', true ) ) {
				$private_status_text = __( 'Publicado para membros', 'f9members' );
			} else {
				$private_status_text = __( 'Privately Published' );
			}

			switch ( $post->post_status ) {
				case 'private':
					echo esc_html( $private_status_text );
					break;
				case 'publish':
					esc_html_e( 'Published' );
					break;
				case 'future':
					esc_html_e( 'Scheduled' );
					break;
				case 'pending':
					esc_html_e( 'Pending Review' );
					break;
				case 'draft':
				case 'auto-draft':
					esc_html_e( 'Draft' );
					break;
			}
			?>
			</span>
			<?php if ( 'publish' === $post->post_status || 'private' === $post->post_status || $can_publish ) { ?>
			<a href="#post_status" <?php if ( 'private' === $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js" role="button"><span aria-hidden="true"><?php esc_html_e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Edit status' ); ?></span></a>

			<div id="post-status-select" class="hide-if-js">
			<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ( 'auto-draft' === $post->post_status ) ? 'draft' : $post->post_status ); ?>" />
			<label for="post_status" class="screen-reader-text"><?php esc_html_e( 'Set status' ) ?></label>
			<select name="post_status" id="post_status">
			<?php if ( 'publish' === $post->post_status ) : ?>
			<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php esc_html_e( 'Published' ) ?></option>
			<?php elseif ( 'private' === $post->post_status ) : ?>
			<option<?php selected( $post->post_status, 'private' ); ?> value='publish'><?php esc_html_e( 'Privately Published' ) ?></option>
			<?php elseif ( 'future' === $post->post_status ) : ?>
			<option<?php selected( $post->post_status, 'future' ); ?> value='future'><?php esc_html_e( 'Scheduled' ) ?></option>
			<?php endif; ?>
			<option<?php selected( $post->post_status, 'pending' ); ?> value='pending'><?php esc_html_e( 'Pending Review' ) ?></option>
			<?php if ( 'auto-draft' === $post->post_status ) : ?>
			<option<?php selected( $post->post_status, 'auto-draft' ); ?> value='draft'><?php esc_html_e( 'Draft' ) ?></option>
			<?php else : ?>
			<option<?php selected( $post->post_status, 'draft' ); ?> value='draft'><?php esc_html_e( 'Draft' ) ?></option>
			<?php endif; ?>
			</select>
			 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php esc_html_e( 'OK' ); ?></a>
			 <a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel' ); ?></a>
			</div>

			<?php } ?>
			</div><!-- .misc-pub-section -->

			<div class="misc-pub-section misc-pub-visibility" id="visibility">
			<?php esc_html_e( 'Visibility:' ); ?> <span id="post-visibility-display"><?php

			if ( 'private' === $post->post_status && 'yes' === get_post_meta( $post->ID, '_members', true ) ) {
				$post->post_password = '';
				$visibility = 'f9members';
				$visibility_trans = __( 'Membros', 'f9members' );
			} elseif ( 'private' === $post->post_status ) {
				$post->post_password = '';
				$visibility = 'private';
				$visibility_trans = __( 'Private' );
			} elseif ( ! empty( $post->post_password ) ) {
				$visibility = 'password';
				$visibility_trans = __( 'Password protected' );
			} elseif ( 'post' === $post_type && is_sticky( $post->ID ) ) {
				$visibility = 'public';
				$visibility_trans = __( 'Public, Sticky' );
			} else {
				$visibility = 'public';
				$visibility_trans = __( 'Public' );
			}

			echo esc_html( $visibility_trans ); ?></span>
			<?php if ( $can_publish ) { ?>
			<a href="#visibility" class="edit-visibility hide-if-no-js" role="button"><span aria-hidden="true"><?php esc_html_e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Edit visibility' ); ?></span></a>

			<div id="post-visibility-select" class="hide-if-js">
			<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr( $post->post_password ); ?>" />
			<?php if ( 'post' === $post_type ) : ?>
			<input type="checkbox" style="display:none" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> />
			<?php endif; ?>
			<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>" />
			<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?> /> <label for="visibility-radio-public" class="selectit"><?php esc_html_e( 'Public' ); ?></label><br />
			<input type="radio" name="visibility" id="visibility-radio-members" value="members" <?php checked( $visibility, 'f9members' ); ?> /> <label for="visibility-radio-members" class="selectit"><?php esc_html_e( 'Membros', 'f9members' ); ?></label><br />
			<?php if ( 'post' === $post_type && current_user_can( 'edit_others_posts' ) ) : ?>
			<span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> /> <label for="sticky" class="selectit"><?php esc_html_e( 'Stick this post to the front page' ); ?></label><br /></span>
			<?php endif; ?>
			<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?> /> <label for="visibility-radio-password" class="selectit"><?php esc_html_e( 'Password protected' ); ?></label><br />
			<span id="password-span"><label for="post_password"><?php esc_html_e( 'Password:' ); ?></label> <input type="text" name="post_password" id="post_password" value="<?php echo esc_attr( $post->post_password ); ?>"  maxlength="255" /><br /></span>
			<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?> /> <label for="visibility-radio-private" class="selectit"><?php esc_html_e( 'Private' ); ?></label><br />

			<p>
			 <a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php esc_html_e( 'OK' ); ?></a>
			 <a href="#visibility" class="cancel-post-visibility hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel' ); ?></a>
			</p>
			</div>
			<?php } ?>

			</div><!-- .misc-pub-section -->

			<?php
			/* Translators: Publish box date format, see https://secure.php.net/date. */
			$datef = __( 'M j, Y @ H:i' );
			if ( 0 !== $post->ID ) {
				if ( 'future' === $post->post_status ) { // Scheduled for publishing at a future date.
					/* Translators: Post date information. 1: Date on which the post is currently scheduled to be published. */
					$stamp = __( 'Scheduled for: <b>%1$s</b>' );
				} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // Already published
					/* translators: Post date information. 1: Date on which the post was published. */
					$stamp = __( 'Published on: <b>%1$s</b>' );
				} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
					$stamp = __( 'Publish <b>immediately</b>' );
				} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
					/* Translators: Post date information. 1: Date on which the post is to be published. */
					$stamp = __( 'Schedule for: <b>%1$s</b>' );
				} else { // draft, 1 or more saves, date specified
					/* Translators: Post date information. 1: Date on which the post is to be published. */
					$stamp = __( 'Publish on: <b>%1$s</b>' );
				}
				$date = date_i18n( $datef, strtotime( $post->post_date ) );
			} else { // Draft (no saves, and thus no date specified).
				$stamp = __( 'Publish <b>immediately</b>' );
				$date = date_i18n( $datef, strtotime( current_time( 'mysql' ) ) );
			}
			$allowed_html = array(
				'b' => array(),
			);

			if ( ! empty( $args['args']['revisions_count'] ) ) : ?>
			<div class="misc-pub-section misc-pub-revisions">
				<?php
					/* Translators: Post revisions heading. 1: The number of available revisions. */
					echo wp_kses( sprintf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $args['args']['revisions_count'] ) . '</b>' ), $allowed_html );
				?>
				<a class="hide-if-no-js" href="<?php echo esc_url( get_edit_post_link( $args['args']['revision_id'] ) ); ?>"><span aria-hidden="true"><?php echo esc_html( _x( 'Browse', 'revisions' ) ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Browse revisions' ); ?></span></a>
			</div>
			<?php endif;

			if ( $can_publish ) : // Contributors don't get to choose the date of publish. ?>
			<div class="misc-pub-section curtime misc-pub-curtime">
				<span id="timestamp">
				<?php echo wp_kses( sprintf( $stamp, $date ), $allowed_html ); ?></span>
				<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button"><span aria-hidden="true"><?php esc_html_e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php esc_html_e( 'Edit date and time' ); ?></span></a>
				<fieldset id="timestampdiv" class="hide-if-js">
				<legend class="screen-reader-text"><?php esc_html_e( 'Date and time' ); ?></legend>
				<?php touch_time( ( 'edit' === $action ), 1 ); ?>
				</fieldset>
			</div><?php // /misc-pub-section ?>
			<?php endif; ?>

			<?php
			/**
			 * Fires after the post time/date setting in the Publish meta box.
			 *
			 * @since 2.9.0
			 * @since 4.4.0 Added the `$post` parameter.
			 *
			 * @param WP_Post $post WP_Post object for the current post.
			 */
			do_action( 'post_submitbox_misc_actions', $post );
			?>
			</div>
			<div class="clear"></div>
			</div>

			<div id="major-publishing-actions">
			<?php
			/**
			 * Fires at the beginning of the publishing actions section of the Publish meta box.
			 *
			 * @since 2.7.0
			 */
			do_action( 'post_submitbox_start' );
			?>
			<div id="delete-action">
			<?php
			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( ! EMPTY_TRASH_DAYS ) {
					$delete_text = __( 'Delete Permanently' );
				} else {
					$delete_text = __( 'Move to Trash' );
				}
				?>
			<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo esc_html( $delete_text ); ?></a><?php
			} ?>
			</div>

			<div id="publishing-action">
			<span class="spinner"></span>
			<?php
			if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) || 0 === $post->ID ) {
				if ( $can_publish ) :
					if ( ! empty( $post->post_date_gmt ) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Schedule' ) ?>" />
					<?php submit_button( __( 'Schedule' ), 'primary large', 'publish', false ); ?>
			<?php	else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>" />
					<?php submit_button( __( 'Publish' ), 'primary large', 'publish', false ); ?>
			<?php	endif;
				else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review' ) ?>" />
					<?php submit_button( __( 'Submit for Review' ), 'primary large', 'publish', false ); ?>
			<?php
				endif;
			} else { ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ) ?>" />
			<?php
			} ?>
			</div>
			<div class="clear"></div>
			</div>
			</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public static function save( $post_id, $post ) {
		delete_post_meta( $post_id, '_members' );
		if ( 'f9members' === $_POST['visibility'] ) {
			$post->post_status = 'private';
			$_POST['post_status'] = 'private';
			$_POST['visibility'] = 'private';
			wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'private',
			) );
			add_post_meta( $post_id, '_members', 'yes' );
		}
	}
}
