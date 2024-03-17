<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

/**
 * Class Assets for preparing JS and CSS files to be loaded by frontend
 */
class Assets {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Call the initialization method to set up necessary configurations or actions.
		$this->init();
	}

	/**
	 * Loads assets only for non-admin users on the frontend
	 *
	 * @return void
	 */
	private function init() {
		// If in the admin area, return.
		if ( is_admin() ) {
			return;
		}

		// If not in the admin area, enqueue assets for blocks.
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Injects required assets to posts pages
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		// If not viewing a single post, return.
		if ( ! is_single() ) {
			return;
		}

		// Enqueue JavaScript file for Simple Post Votes plugin.
		wp_enqueue_script(
			'spv-js',
			SIMPLE_POST_VOTES_PLUGIN_BUILD_URL . '/js/simplepostvotesplugin.js',
			array( 'jquery' ),
			filemtime( SIMPLE_POST_VOTES_PLUGIN_BUILD_PATH . '/js/simplepostvotesplugin.js' ),
			true // Whether to load the script in the footer.
		);

		// Localize the enqueued JavaScript script with necessary data.
		wp_localize_script(
			'spv-js',
			'spv_js_vars',
			array(
				'site_url' => esc_url( home_url( '/' ) ),
				'nonce'    => is_user_logged_in() ? '' : wp_create_nonce( 'spv_nonce' ),
				'post_id'  => is_single() ? get_the_ID() ?? '' : '',
				'feedback' => __( 'THANK YOU FOR YOUR FEEDBACK', 'simple-post-votes' ),
			)
		);

		// Enqueue Google Fonts CSS file.
		wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Ruda:wght@400..900&display=swap' );

		// Enqueue CSS file for Simple Post Votes plugin.
		wp_enqueue_style(
			'spv-css',
			SIMPLE_POST_VOTES_PLUGIN_BUILD_URL . '/css/simplepostvotesplugin.css',
			filemtime( SIMPLE_POST_VOTES_PLUGIN_BUILD_PATH . '/css/simplepostvotesplugin.css' ),
			'all'
		);
	}
}
