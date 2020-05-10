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
	}

	/**
	 * Determine where to place LearnDash courses as meta.
	 */
	public function post_meta_wrapper() {
		$post_types = apply_filters( 'pmproap_supported_post_types', array( 'page', 'post' ) );

		// get extra post types from PMPro CPT, if available
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
		echo 'test';
	}
}
