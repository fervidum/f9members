<?php
/**
 * F9members Meta Boxes
 *
 * Sets up the write panels.
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
 * F9members_Admin_Meta_Boxes.
 */
class F9members_Admin_Meta_Boxes {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var boolean
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 *
	 * @var array
	 */
	public static $meta_box_errors  = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes',         array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes',         array( $this, 'add_meta_boxes' ), 20 );
		add_action( 'save_post',              array( $this, 'save_meta_boxes' ), 1, 2 );
		add_filter( 'display_post_states',    array( $this, 'display_post_states' ), 10, 2 );
		/**
		 * Save Post Meta Boxes.
		 */
		add_action( 'f9members_submit_post_meta', 'F9members_Meta_Box_Post_Submit::save', 10, 2 );
	}

	/**
	 * Add F9members Meta boxes.
	 *
	 * @global string $post_type
	 */
	public function add_meta_boxes() {
		global $post_type;

		if ( 'attachment' !== $post_type ) {
			remove_meta_box( 'submitdiv', $post_type, 'side' );
			add_meta_box(
				'submitdiv',
				__( 'Publish' ),
				'F9members_Meta_Box_Post_Submit::output',
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Remove bloat.
	 *
	 * @global string $post_type
	 */
	public function remove_meta_boxes() {
		global $post_type;

		if ( 'attachment' !== $post_type ) {
			remove_meta_box( 'submitdiv', $post_type, 'side' );
		}
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @global string $post_type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		global $post_type;

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		// remove_action( current_filter(), __METHOD__ );
		// But cannot be used due to https://github.com/woocommerce/woocommerce/issues/6485
		// When that is patched in core we can use the above. For now:
		self::$saved_meta_boxes = true;

		if ( 'attachment' !== $post_type ) {
			do_action( 'f9members_submit_post_meta', $post_id, $post );
		}
	}

	/**
	 * Show post status members in list posts.
	 *
	 * @param array  $post_status Current post states.
	 * @param object $post Post object.
	 * @return array
	 */
	public function display_post_states( $post_states, $post ) {
		if ( 'private' === $post->post_status && get_post_meta( $post->ID, '_members', true ) ) {
			unset( $post_states['private'] );
			$post_states['f9members'] = __( 'Membros', 'f9members' );
		}
		return $post_states;
	}
}

new F9members_Admin_Meta_Boxes();
