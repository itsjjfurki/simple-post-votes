<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

/**
 * Class Votes for performing db operations
 */
class Votes {

	/**
	 * Saves the vote if it doesn't exist based on given parameters
	 *
	 * @param bool     $selection positive or negative feedback.
	 * @param int      $post_id post id.
	 * @param string   $ip_address ip address.
	 * @param string   $user_agent user agent string.
	 * @param string   $sec_ch_ua_platform platform(OS) string.
	 * @param int|null $user_id user id.
	 * @return int|null
	 */
	public function save_vote( bool $selection, int $post_id, string $ip_address, string $user_agent, string $sec_ch_ua_platform, int|null $user_id = null ): null|int {
		// Prepare search criteria based on whether user ID is provided.
		if ( is_null( $user_id ) ) {
			$search_criteria = array(
				'ip_address'         => $ip_address,
				'user_agent'         => $user_agent,
				'sec_ch_ua_platform' => $sec_ch_ua_platform,
			);
		} else {
			$search_criteria['user_id'] = $user_id;
		}

		// Add post ID to search criteria.
		$search_criteria['post_id'] = $post_id;

		// Check if the vote already exists based on search criteria.
		if ( $this->vote_exists( $search_criteria ) ) {
			return null;
		}

		global $wpdb;

		// Get the table name.
		$table_name = $wpdb->prefix . SIMPLE_POST_VOTES_TABLE_NAME;

		// Prepare data for insertion.
		$prepared_data = array(
			'selection'          => $selection,
			'post_id'            => $post_id,
			'user_id'            => $user_id,
			'ip_address'         => $ip_address,
			'user_agent'         => $user_agent,
			'sec_ch_ua_platform' => $sec_ch_ua_platform,
		);

		// Insert the vote data into the database table.
		$wpdb->insert( $table_name, $prepared_data );

		// Check if the vote was successfully inserted and return its ID.
		if ( $wpdb->insert_id ) {
			return $wpdb->insert_id;
		} else {
			return null;
		}
	}

	/**
	 * Checks if vote exists for given parameters
	 *
	 * @param array $search_criteria an array of parameters.
	 * @return bool
	 */
	public function vote_exists( array $search_criteria = array() ): bool {
		global $wpdb;

		// Get the table name.
		$table_name = $wpdb->prefix . SIMPLE_POST_VOTES_TABLE_NAME;

		// Initialize the WHERE clause.
		$where_clause = '';

		// Construct the WHERE clause based on the search criteria.
		foreach ( $search_criteria as $column => $value ) {
			$where_clause .= $wpdb->prepare( " AND $column = %s", $value );
		}

		// If user_id is not specified in the search criteria, add a condition for NULL user_id.
		if ( ! array_key_exists( 'user_id', $search_criteria ) ) {
			$where_clause .= ' AND user_id IS NULL';
		}

		// Prepare the SQL query to count matching records.
		$query = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE 1=1 $where_clause" );

		// Execute the query and get the count of matching records.
		$record_count = $wpdb->get_var( $query );

		// Return true if matching records are found, false otherwise.
		return $record_count > 0;
	}

	/**
	 * Finds the vote by given parameters and gets if it's a positive or a negative feedback
	 *
	 * @param int      $post_id post id.
	 * @param string   $ip_address ip address.
	 * @param string   $user_agent user agent string.
	 * @param string   $sec_ch_ua_platform platform (OS).
	 * @param int|null $user_id user id.
	 * @return int|null
	 */
	public function get_user_vote_selection( int $post_id, string $ip_address, string $user_agent, string $sec_ch_ua_platform, int|null $user_id = null ): int|null {
		global $wpdb;

		// Get the table name.
		$table_name = $wpdb->prefix . SIMPLE_POST_VOTES_TABLE_NAME;

		// Prepare search criteria based on whether user ID is provided.
		if ( is_null( $user_id ) ) {
			$search_criteria = array(
				'ip_address'         => $ip_address,
				'user_agent'         => $user_agent,
				'sec_ch_ua_platform' => $sec_ch_ua_platform,
			);
		} else {
			$search_criteria['user_id'] = $user_id;
		}

		// Add post ID to search criteria.
		$search_criteria['post_id'] = $post_id;

		// Initialize the WHERE clause.
		$where_clause = '';

		// Construct the WHERE clause based on the search criteria.
		foreach ( $search_criteria as $column => $value ) {
			$where_clause .= $wpdb->prepare( " AND $column = %s", $value );
		}

		// If user_id is not specified in the search criteria, add a condition for NULL user_id.
		if ( is_null( $user_id ) ) {
			$where_clause .= ' AND user_id IS NULL';
		}

		// Prepare the SQL query to select the selection column.
		$query = $wpdb->prepare( "SELECT selection FROM $table_name WHERE 1=1 $where_clause LIMIT 1" );

		// Execute the query and get the row.
		$result = $wpdb->get_row( $query );

		// If a row is found, return the selection value, otherwise return null.
		if ( ! empty( $result ) ) {
			return $result->selection;
		}

		return null;
	}

	/**
	 * Gets all vote results
	 *
	 * @param int $post_id post id.
	 * @return int[]|null
	 */
	public function get_post_vote_results( int $post_id ): array|null {
		global $wpdb;

		// Get the table name.
		$table_name = $wpdb->prefix . SIMPLE_POST_VOTES_TABLE_NAME;

		// Prepare SQL query to count all votes for the given post.
		$all_post_votes_query = $wpdb->prepare( "SELECT COUNT(*) as count FROM $table_name WHERE post_id=%d", $post_id );

		// Get the total count of votes for the post.
		$all_post_votes_count = $wpdb->get_var( $all_post_votes_query );

		// Prepare SQL query to count positive votes for the given post.
		$positive_post_votes_query = $wpdb->prepare( "SELECT COUNT(*) as count FROM $table_name WHERE post_id=%d AND selection=1", $post_id );

		// Get the count of positive votes for the post.
		$positive_post_votes_count = $wpdb->get_var( $positive_post_votes_query );

		// Calculate the count of negative votes for the post.
		$negative_post_votes_count = $all_post_votes_count - $positive_post_votes_count;

		// If is called from admin meta box and no vote records exist, return null.
		if ( is_admin() && empty( $positive_post_votes_count ) && empty( $negative_post_votes_count ) ) {
			return null;
		}

		// Return an array containing total, positive, and negative vote counts.
		return array(
			'total'          => (int) $all_post_votes_count,
			'positive_votes' => (int) $positive_post_votes_count,
			'negative_votes' => (int) $negative_post_votes_count,
		);
	}

	/**
	 * Gets vote results with percentages
	 *
	 * @param int  $post_id post id.
	 * @param bool $with_counts switch for fetching result with counts included.
	 * @return array|null
	 */
	public function get_post_votes_average_percentages( int $post_id, bool $with_counts = false ): array|null {
		// Get the vote results for the post.
		$data = $this->get_post_vote_results( $post_id );

		// If no vote results exist, return null.
		if ( is_null( $data ) ) {
			return null;
		}

		// Calculate the percentage of positive votes and round it.
		$positive_percent = round( ( $data['positive_votes'] / $data['total'] ) * 100, 0, PHP_ROUND_HALF_UP );

		// Calculate the percentage of negative votes.
		$negative_percent = 100 - $positive_percent;

		// Construct an array containing percentages.
		$percentages = array(
			'yes' => $positive_percent,
			'no'  => $negative_percent,
		);

		// If with_counts is true, return an array containing both counts and percentages, otherwise return only percentages.
		return $with_counts ? array(
			'counts'      => $data,
			'percentages' => $percentages,
		) : $percentages;
	}
}
