<?php
/*
 * Plugin Name:       Simple Post Votes
 * Plugin URI:        https://furkanozturk.dev/simple-post-votes
 * Description:       Enables visitors to vote on posts
 * Version:           1.0.0
 * Requires at least: 6.1.1
 * Requires PHP:      8.1
 * Author:            Furkan OZTURK
 * Author URI:        https://furkanozturk.dev/about
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-post-votes
 * Domain Path:       /languages
 */

/**
 * Bootstrap the plugin.
 */

require_once 'vendor/autoload.php';
require_once untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/inc/helpers.php';
use Simple_Post_Votes\Plugin;

// Check if class exists.
if ( class_exists( 'Simple_Post_Votes\Plugin' ) ) {
	// Load translations.
	add_action( 'init', 'simple_post_votes_text_domain_load' );

	// Entry point for the plugin.
	$simple_post_votes = new Plugin();
	register_activation_hook( __FILE__, array( $simple_post_votes, 'activate' ) );
	register_deactivation_hook( __FILE__, array( $simple_post_votes, 'deactivate' ) );
}

/**
 * Loads translations
 *
 * @return void
 */
function simple_post_votes_text_domain_load(): void {
	load_plugin_textdomain( 'simple-post-votes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
