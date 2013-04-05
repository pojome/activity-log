<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HT_Maintenance {
	
	public static function activate() {
		global $wpdb;

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->activity_log}` (
					  `histid` int(11) NOT NULL AUTO_INCREMENT,
					  `user_caps` varchar(70) NOT NULL DEFAULT 'guest',
					  `action` varchar(255) NOT NULL,
					  `object_type` varchar(255) NOT NULL,
					  `object_subtype` varchar(255) NOT NULL DEFAULT '',
					  `object_name` varchar(255) NOT NULL,
					  `object_id` int(11) NOT NULL DEFAULT '0',
					  `user_id` int(11) NOT NULL DEFAULT '0',
					  `hist_ip` varchar(55) NOT NULL DEFAULT '127.0.0.1',
					  `hist_time` int(11) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`histid`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		update_option( 'history_timeline_db_version', '0.1' );
	}

	public static function uninstall() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->activity_log" );

		delete_option( 'history_timeline_db_version' );
	}
}

register_activation_hook( ACTIVITY_LOG_BASE, array( 'HT_Maintenance', 'activate' ) );
register_uninstall_hook( ACTIVITY_LOG_BASE, array( 'HT_Maintenance', 'uninstall' ) );