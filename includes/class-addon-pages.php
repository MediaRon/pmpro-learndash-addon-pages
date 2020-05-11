<?php
/**
 * Creates and drops the table.
 *
 * @package PMPRO_LearnDash_AOP
 */

namespace PMPRO_LearnDash_AOP\Includes;

/**
 * Helper class for admin pages.
 */
class Addon_Pages {
	/**
	 * Initialization for the class.
	 */
	public function run() {
		add_action( 'admin_init', array( $this, 'post_meta_wrapper' ) );
		add_action( 'save_post', array( $this, 'post_save' ) );
	}

	/**
	 * Save the LearnDash course data.
	 *
	 * @param int $post_id the Post ID to save to.
	 */
	public function post_save( $post_id ) {

		if ( ! wp_verify_nonce( $_POST['aopld_nonce'], 'save_pmpro_learndash_aop_post' ) ) {
			return;
		}
		/**
		 * Verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		$courses = array();
		if ( isset( $_POST['aopld'] ) ) {
			$courses = array_filter( $_POST['aopld'], 'absint' );
		}

		$level = isset( $_POST[ 'aopld_level'] ) ? absint( $_POST['aopld_level'] ) : 0;

		// Retrieve previous post meta. LearnDash courses can only have one mapped page per course.
		foreach ( $courses as $aop_course_id ) {
			update_post_meta( $aop_course_id, '_aop_ld_mapped_page', $post_id );
			update_post_meta( $aop_course_id, '_aop_ld_level', $level );
		}

		$courses_to_save = array();
		foreach ( $courses as $course_id ) {
			if ( ! in_array( $course_id, $ld_is_mapped, true ) ) {
				$courses_to_save[] = $course_id;
			}
		}

		if ( ! empty( $courses_to_save ) ) {
			update_post_meta( $post_id, '_aop_ld_level', $level );
			update_post_meta( $post_id, '_aop_ld_courses', $courses_to_save );
		} else {
			update_post_meta( $post_id, '_aop_ld_level', 0 );
			update_post_meta( $post_id, '_aop_ld_courses', array() );
		}
	}

	/**
	 * Determine where to place LearnDash courses as meta.
	 */
	public function post_meta_wrapper() {
		$post_types = apply_filters( 'pmproap_supported_post_types', array( 'page', 'post' ) );

		// get extra post types from PMPro CPT, if available.
		if ( function_exists( 'pmprocpt_getCPTs' ) ) {
			$post_types = array_merge( $post_types, pmprocpt_getCPTs() );
		}

		foreach ( $post_types as $type ) {
			add_meta_box( 'pmproap_post_meta_learndash', __( 'PMPro Addon Package LearnDash Settings', 'pmpro-learndash-addon-pages' ), array( $this, 'output_learndash_courses' ), $type, 'normal' );
		}
	}

	/**
	 * Output LearnDash courses to select.
	 */
	public function output_learndash_courses() {
		global $post;
		$courses = ld_course_list(
			array(
				'array'          => true,
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		if ( ! $courses ) {
			esc_html_e( 'No courses found', 'pmpro-learndash-addon-pages' );
			return;
		}
		?>
		<h2><?php esc_html_e( 'Select LearnDash Courses', 'pmpro-learndash-addon-pages' ); ?></h2>
		<input type="hidden" name="aopld_nonce" id="aopld_nonce" value="<?php echo esc_html( wp_create_nonce( 'save_pmpro_learndash_aop_post' ) ); ?>" />
		<?php
		$mapped_courses = get_post_meta( $post->ID, '_aop_ld_courses', true );
		foreach ( $courses as $course ) {
			?>
			<div>
				<label>
					<input type="checkbox" name="aopld[]" value="<?php echo absint( $course->ID ); ?>" <?php checked( true, in_array( $course->ID, $mapped_courses ) ); ?> />
					<?php echo esc_html( $course->post_title ); ?>
				</label>
			</div>
			<?php
		}
		?>
		<h2><?php esc_html_e( 'Select a Level for the Course(s)', 'pmpro-learndash-addon-pages' ); ?></h2>
		<?php
		$selected_level = get_post_meta( $post->ID, '_aop_ld_level', true );
		$level_data     = pmpro_getAllLevels( true, true );
		if ( $level_data ) {
			?>
			<select name="aopld_level">
				<option value="0"><?php esc_html_e( 'Select a level', 'pmpro-learndash-addon-pages' ); ?></option>
				<?php
				foreach ( $level_data as $level ) {
					printf( '<option value="%s" %s>%s</option>', absint( $level->id ), selected( $level->id, $selected_level ), esc_html( $level->name ) );
				}
				?>
			</select>
			<?php
		}
	}
}
