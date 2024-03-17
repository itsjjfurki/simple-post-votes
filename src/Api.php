<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Api for handling REST API Requests
 */
class Api {
	/**
	 * Holds Votes class for database queries
	 *
	 * @var Votes
	 */
	private Votes $votes;

	/**
	 * Api namespace used in URL
	 *
	 * @var string
	 */
	private string $namespace;

	/**
	 * User id fetched from request
	 *
	 * @var int
	 */
	private int $user_id;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize a new instance of the Votes class to manage voting functionality.
		$this->votes = new Votes();

		// Set the namespace for this class, typically used for REST API endpoints.
		$this->namespace = 'spv/v1';

		// Call the initialization method to set up necessary configurations or actions.
		$this->init();
	}

	/**
	 * Registers routes
	 *
	 * @return void
	 */
	private function init() {
		// Add a hook to the REST API initialization process, calling the register_routes method of this class.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers routes
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Define the HTTP methods allowed for this route (only POST).
		register_rest_route(
			$this->namespace,
			'/vote',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'process_vote_request' ),
				'permission_callback' => '__return_true',
			)
		);

		// Define the HTTP methods allowed for this route (only POST).
		register_rest_route(
			$this->namespace,
			'/vote-result',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'process_vote_result_request' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Processes vote requests
	 *
	 * @param WP_REST_Request $request request object.
	 * @return WP_REST_Response
	 */
	public function process_vote_request( WP_REST_Request $request ): WP_REST_Response {
		// Validate the incoming vote request.
		$this->validate_vote_request( $request );

		// Extract data from the request.
		$data = $this->get_vote_request_data( $request );

		// Save the vote using the Votes class.
		$result = $this->votes->save_vote(
			$data['selection'],
			$data['post_id'],
			$data['ip_address'],
			$data['user_agent'],
			$data['sec_ch_ua_platform'],
			$data['user_id'],
		);

		// If the vote is already cast, return an error.
		if ( is_null( $result ) ) {
			wp_send_json_error( 'You can only vote once', 409 );
		}

		// If the vote is successfully saved, return the success response along with vote results.
		return rest_ensure_response(
			array(
				'success'      => true,
				'vote_results' => $this->votes->get_post_votes_average_percentages( $data['post_id'] ),
			)
		);
	}

	/**
	 * Validates vote request params
	 *
	 * @param WP_REST_Request $request request object.
	 * @return void
	 */
	public function validate_vote_request( WP_REST_Request $request ): void {
		// Determine user ID based on the authentication cookie.
		$this->user_id = isset( $_COOKIE[ LOGGED_IN_COOKIE ] ) ? wp_validate_auth_cookie( $_COOKIE[ LOGGED_IN_COOKIE ], 'logged_in' ) : 0;

		// Check if all necessary parameters are present in the request.
		if ( ! $request->has_param( 'nonce' ) || ! $request->has_param( 'action' ) || ! $request->has_param( 'value' ) || ! $request->has_param( 'post_id' ) ) {
			wp_send_json_error( 'Invalid data', 400 );
		}

		// If the user is not logged in, validate the nonce.
		if ( 0 === $this->user_id && ! wp_verify_nonce( $request->get_param( 'nonce' ), $request->get_param( 'action' ) ) ) {
			wp_send_json_error( 'Invalid nonce', 400 );
		}

		// Check if the value parameter is valid (either 'yes' or 'no').
		if ( ! in_array( $request->get_param( 'value' ), array( 'yes', 'no' ), true ) ) {
			wp_send_json_error( 'Invalid data', 400 );
		}

		// Check if the post ID is a valid numeric value and corresponds to an existing post.
		if ( ! ctype_digit( $request->get_param( 'post_id' ) ) || ! get_post( $request->get_param( 'post_id' ) ) ) {
			wp_send_json_error( 'Invalid data', 400 );
		}
	}

	/**
	 * Prepares vote request data fetched via WP_REST_Request object
	 *
	 * @param WP_REST_Request $request request object.
	 * @return array
	 */
	public function get_vote_request_data( WP_REST_Request $request ): array {
		return array(
			'user_id'            => $this->user_id > 0 ? $this->user_id : null,
			'selection'          => $request->get_param( 'value' ) === 'yes',
			'post_id'            => (int) $request->get_param( 'post_id' ),
			'ip_address'         => spv_get_requester_ip(),
			'user_agent'         => $request->get_header( 'user_agent' ),
			'sec_ch_ua_platform' => $request->get_header( 'sec_ch_ua_platform' ),
		);
	}

	/**
	 * Processes vote result request
	 *
	 * @param WP_REST_Request $request request object.
	 * @return WP_REST_Response
	 */
	public function process_vote_result_request( WP_REST_Request $request ): WP_REST_Response {
		$this->validate_vote_result_request( $request );

		// Extract data from the request.
		$data = $this->get_vote_request_data( $request );

		// Get the user's vote selection for the specified post.
		$selection = $this->votes->get_user_vote_selection( $data['post_id'], $data['ip_address'], $data['user_agent'], $data['sec_ch_ua_platform'], $data['user_id'] );

		// If the user has already voted for the post, retrieve vote results.
		if ( ! is_null( $selection ) ) {
			$vote_results = $this->votes->get_post_votes_average_percentages( $data['post_id'] );

			// If vote results are available, construct the success response.
			if ( ! is_null( $vote_results ) ) {
				$result = array(
					'success'      => true,
					'selection'    => $selection,
					'vote_results' => $vote_results,
				);
			} else {
				// If vote results are not available, construct a failure response.
				$result = array(
					'success' => false,
				);
			}
		} else {
			// If the user hasn't voted for the post, construct a failure response.
			$result = array(
				'success' => false,
			);
		}

		// Ensure the response is in the correct REST format and return it.
		return rest_ensure_response( $result );
	}

	/**
	 * Validates vote result request params
	 *
	 * @param WP_REST_Request $request request object.
	 * @return void
	 */
	public function validate_vote_result_request( WP_REST_Request $request ): void {
		// Determine user ID based on the authentication cookie.
		$this->user_id = isset( $_COOKIE[ LOGGED_IN_COOKIE ] ) ? wp_validate_auth_cookie( $_COOKIE[ LOGGED_IN_COOKIE ], 'logged_in' ) : 0;

		// Check if all necessary parameters are present in the request.
		if ( ! $request->has_param( 'nonce' ) || ! $request->has_param( 'action' ) || ! $request->has_param( 'post_id' ) ) {
			wp_send_json_error( 'Invalid data', 400 );
		}

		// If the user is not logged in, validate the nonce.
		if ( 0 === $this->user_id && ! wp_verify_nonce( $request->get_param( 'nonce' ), $request->get_param( 'action' ) ) ) {
			wp_send_json_error( 'Invalid nonce', 400 );
		}

		// Check if the post ID is a valid numeric value and corresponds to an existing post.
		if ( ! ctype_digit( $request->get_param( 'post_id' ) ) || ! get_post( $request->get_param( 'post_id' ) ) ) {
			wp_send_json_error( 'Invalid data', 400 );
		}
	}
}
