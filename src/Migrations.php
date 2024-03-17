<?php
/**
 * @package simple-post-votes-plugin
 */

namespace Simple_Post_Votes;

/**
 * Class Migrations for specifying database tables
 */
class Migrations {

	/**
	 * Creates database table
	 *
	 * @return void
	 */
	public static function create_post_votes_table() {
		global $wpdb;

		// Get the table name and charset collate.
		$table_name      = $wpdb->prefix . SIMPLE_POST_VOTES_TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		// Define the SQL query to create the post votes table.
		$sql = "CREATE TABLE $table_name (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          selection tinyint(1) NOT NULL,
          post_id INT UNSIGNED NOT NULL,
          user_id BIGINT UNSIGNED DEFAULT NULL,
          ip_address varchar(45) NOT NULL,
          user_agent varchar(255) NOT NULL,
          sec_ch_ua_platform varchar(255) NOT NULL,
          PRIMARY KEY (id)
        ) $charset_collate;";

		// Include WordPress database upgrade functions.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// Run the SQL query to create the table.
		dbDelta( $sql );
	}

	/**
	 * Removes database table
	 *
	 * @return void
	 */
	public static function remove_post_votes_table() {
		global $wpdb;

		// Get the table name.
		$table_name = $wpdb->prefix . SIMPLE_POST_VOTES_TABLE_NAME;

		// Define the SQL query to drop the table if it exists.
		$query = "DROP TABLE IF EXISTS $table_name";

		// Execute the SQL query to remove the table.
		$wpdb->query( $query );
	}
}
