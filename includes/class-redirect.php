<?php
/**
 * Redirect for LearnDash courses.
 *
 * @package PMPRO_LearnDash_AOP
 */

namespace PMPRO_LearnDash_AOP\Includes;

/**
 * Helper class for admin pages.
 */
class Redirect {
	/**
	 * Initialization for the class.
	 */
	public function run() {
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ) );
	}

	/**
	 * Redirect user to checkout page.
	 */
	public function maybe_redirect() {
		global $post, $current_user;
		if ( is_post_type_archive( 'sfwd-courses' ) ) {
			return;
		}
		// Prevent a redirect if user is enrolled in course.
		$enrolled_courses = learndash_user_get_enrolled_courses( $current_user->ID );
		foreach ( $enrolled_courses as $course_id ) {
			if ( $post->ID == $course_id ) {
				return;
			}
		}
		// Redirect if mapped to a post and level.
		if ( ! empty( $post ) && $post->post_type == 'sfwd-courses' ) {
			$mapped_post = get_post_meta( $post->ID, '_aop_ld_mapped_page', true );
			$level       = absint( get_post_meta( $post->ID, '_aop_ld_level', true ) );
			$blah = get_post_custom( $post->ID );
			if ( ! $mapped_post || ! $level ) {
				return;
			}
			$checkout_url = add_query_arg(
				array(
					'level' => $level,
					'ap'    => $mapped_post,
				),
				get_permalink( pmpro_getOption( 'checkout_page_id' ) )
			);
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( esc_url_raw( $checkout_url ) );
				exit;
			}
			$courses = learndash_user_get_enrolled_courses( $current_user->ID );
			if ( ! in_array( $post->ID, $courses ) ) {
				wp_safe_redirect( esc_url_raw( $checkout_url ) );
				exit;
			}
		}
	}
}
