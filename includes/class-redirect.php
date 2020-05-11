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

		if ( ! empty( $post ) && $post->post_type == 'sfwd-courses' ) {
			$mapped_post = get_post_meta( $post->ID, '_aop_ld_mapped_page', true );
			if ( ! $mapped_post ) {
				return;
			}
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( esc_url( get_permalink( $mapped_post ) ) );
				exit;
			}
			$courses = learndash_user_get_enrolled_courses( $current_user->ID );
			if ( ! in_array( $post->ID, $courses ) ) {
				wp_safe_redirect( esc_url( get_permalink( $mapped_post ) ) );
				exit;
			}
		}
	}
}
