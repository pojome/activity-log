<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Maintenance {

	public static function activate( $network_wide ) {
		global $wpdb;

		if ( function_exists( 'is_multisite') && is_multisite() && $network_wide ) {
			$old_blog_id = $wpdb->blogid;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::_create_tables();
			}

			switch_to_blog( $old_blog_id );
		} else {
			self::_create_tables();
		}
	}

	public static function uninstall( $network_deactivating ) {
		global $wpdb;

		if ( function_exists( 'is_multisite') && is_multisite() && $network_deactivating ) {
			$old_blog_id = $wpdb->blogid;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs;" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::_remove_tables();
			}

			switch_to_blog( $old_blog_id );
		} else {
			self::_remove_tables();
		}
	}

	protected static function _create_tables() {
		global $wpdb;

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}aryo_activity_log` (
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

		update_option( 'activity_log_db_version', '1.0' );
	}

	protected static function _remove_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}aryo_activity_log`;" );

		delete_option( 'activity_log_db_version' );
	}
}

register_activation_hook( ACTIVITY_LOG_BASE, array( 'AAL_Maintenance', 'activate' ) );
register_uninstall_hook( ACTIVITY_LOG_BASE, array( 'AAL_Maintenance', 'uninstall' ) );