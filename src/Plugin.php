<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Call the initialization method to set up necessary configurations or actions.
		$this->init();
	}

	/**
	 * Creates table in database on plugin activation
	 *
	 * @return void
	 */
	public function activate(): void {
		// Create the post votes table using the Migrations class.
		Migrations::create_post_votes_table();
	}

	/**
	 * Removes table in database on plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate(): void {
		// Remove the post votes table using the Migrations class.
		Migrations::remove_post_votes_table();
	}

	/**
	 * Initializes plugin
	 *
	 * @return void
	 */
	private function init() {
		// Define the table name.
		define( 'SIMPLE_POST_VOTES_TABLE_NAME', 'post_votes' );
		// Define plugin path.
		define( 'SIMPLE_POST_VOTES_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __DIR__ ) ) );
		// Define plugin url.
		define( 'SIMPLE_POST_VOTES_PLUGIN_URL', untrailingslashit( plugin_dir_url( __DIR__ ) ) );
		// Define assets path.
		define( 'SIMPLE_POST_VOTES_PLUGIN_BUILD_PATH', SIMPLE_POST_VOTES_PLUGIN_PATH . '/assets/build' );
		// Define publicly accessible assets url.
		define( 'SIMPLE_POST_VOTES_PLUGIN_BUILD_URL', SIMPLE_POST_VOTES_PLUGIN_URL . '/assets/build' );
		// Define plugin version.
		define( 'SIMPLE_POST_VOTES_PLUGIN_VERSION', '1.0.0' );

		// Initialize api that is going to receive requests from vote dialogue in posts page.
		new Api();
		// Initialize admin meta box that show statistics of the votes in each posts page.
		new Admin_Meta_Widget();
		// Initialize and inject assets into frontend.
		new Assets();
		// Initialize publicly available voting dialogue.
		new Vote_Dialogue();
	}
}
