<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

use WP_Post;

/**
 * Class Admin_Meta_Widget
 */
class Admin_Meta_Widget {
	/**
	 * Holds Votes class for database queries
	 *
	 * @var Votes
	 */
	private Votes $votes;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize a new instance of the Votes class to manage voting functionality.
		$this->votes = new Votes();

		// Call the initialization method to set up necessary configurations or actions.
		$this->init();
	}

	/**
	 * Registers admin meta box
	 *
	 * @return void
	 */
	private function init() {
		// Add a hook to the add_meta_boxes action, calling the add_admin_meta_box method of this class.
		add_action( 'add_meta_boxes', array( $this, 'add_admin_meta_box' ) );
	}

	/**
	 * Builds admin meta box
	 *
	 * @return void
	 */
	public function add_admin_meta_box(): void {
		// Get the post ID from the URL parameters.
		$post_id = $_GET['post'] ?? null;

		// If post ID is not available, return.
		if ( is_null( $post_id ) ) {
			return;
		}

		// Retrieve the post status.
		$post_status = get_post_status( $post_id );

		// Meta box is only available to posts with publish status.
		if ( 'publish' === $post_status ) {

			// Add a meta box for Simple Post Votes.
			add_meta_box(
				'spv-meta-box',
				'Simple Post Votes',
				array( $this, 'render_admin_meta_box' ),
				'post',
				'side',
				'high'
			);
		}
	}

	/**
	 * Renders admin meta box
	 *
	 * @param WP_Post $post WordPress post object.
	 * @return void
	 */
	public function render_admin_meta_box( WP_Post $post ): void {
		// Retrieve vote data for the post.
		$data = $this->votes->get_post_votes_average_percentages( $post->ID, true );

		// If no vote data is available, display a message and return.
		if ( is_null( $data ) ) {
			echo '<p>' . esc_html( __( 'No votes found for this post', 'simple-post-votes' ) ) . '</p>';
			return;
		}

		// Construct the HTML content to display vote counts and percentages.
		$result =
			'<b>' . esc_html( __( 'count', 'simple-post-votes' ) ) . '</b><br />
            <i>' . esc_html( __( 'total', 'simple-post-votes' ) ) . '</i>: ' . $data['counts']['total'] . '<br />
            <i>' . esc_html( __( 'positive_votes', 'simple-post-votes' ) ) . '</i>: ' . $data['counts']['positive_votes'] . '<br />
            <i>' . esc_html( __( 'negative_votes', 'simple-post-votes' ) ) . '</i>: ' . $data['counts']['negative_votes'] . '<br />
            <b>' . esc_html( __( 'percentage', 'simple-post-votes' ) ) . '</b><br />
            <i>' . esc_html( __( 'yes', 'simple-post-votes' ) ) . '</i>: ' . $data['percentages']['yes'] . '%<br />
            <i>' . esc_html( __( 'no', 'simple-post-votes' ) ) . '</i>: ' . $data['percentages']['no'] . '%';

		echo $result;
	}
}
