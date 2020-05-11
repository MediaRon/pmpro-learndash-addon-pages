<?php
/**
 * Add or Revoke LearnDash courses
 *
 * @package PMPRO_LearnDash_AOP
 */

namespace PMPRO_LearnDash_AOP\Includes;

/**
 * Helper class for add/remove courses.
 */
class Add_Remove_Courses {
	/**
	 * Initialization function.
	 */
	public function run() {
		add_action( 'pmproap_action_add_to_package', array( $this, 'add_to_package' ), 10, 2 );
		add_action( 'pmproap_action_remove_from_package', array( $this, 'remove_from_package' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'add_courses_to_page' ) );
	}

	/**
	 * Add enrolled courses to the add-on page.
	 *
	 * @param string $content The main post content.
	 *
	 * @return string $content The modified post content.
	 */
	public function add_courses_to_page( $content ) {
		global $post;
		global $current_user;
		if ( pmproap_hasAccess( $current_user->ID, $post->ID ) ) {
			$courses = learndash_user_get_enrolled_courses( $current_user->ID );
			if ( $courses ) {
				ob_start();
				?>
				<h2><?php esc_html_e( 'Your courses', 'pmpro-learndash-addon-pages' ); ?></h2>
				<ul>
					<?php
					foreach ( $courses as $course_id ) {
						printf( '<li><a href="%s">%s</li>', esc_url( get_permalink( $course_id ) ), esc_html( get_the_title( $course_id ) ) );
					}
					?>
				</ul>
				<?php
				$content = ob_get_clean() . $content;
			}
		}
		return $content;
	}
	/**
	 * Add a course for a user after checkout.
	 *
	 * @param int $user_id The User ID.
	 * @param int $post_id The POST ID.
	 */
	public function add_to_package( $user_id, $post_id ) {
		$courses        = learndash_user_get_enrolled_courses( $user_id );
		$courses_to_add = get_post_meta( $post_id, '_aop_ld_courses', true );
		if ( is_array( $courses_to_add ) ) {
			$courses = array_merge( $courses, $courses_to_add );
			array_unique( $courses );
			learndash_user_set_enrolled_courses( $user_id, $courses );
		}
	}
	/**
	 * Removes course access.
	 *
	 * @param int $user_id The User ID.
	 * @param int $post_id The POST ID.
	 */
	public function remove_from_package( $user_id, $post_id ) {
		$courses_to_remove = get_post_meta( $post_id, '_aop_ld_courses', true );
		if ( is_array( $courses_to_remove ) ) {
			foreach ( $courses_to_remove as $course_id ) {
				ld_update_course_access( $user_id, $course_id, true );
			}
		}
	}
}
