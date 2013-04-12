<?php
/*
 Plugin Name: ARYO Activity Log
 Plugin URI: http://www.aryo.co.il/
 Description: Never mind.. Created by Yakir Sitbon.
 Author: Yakir Sitbon
 Version: 0.1
 Author URI: http://www.yakirs.net/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'ACTIVITY_LOG_BASE', plugin_basename( __FILE__ ) );

include( 'classes/class-maintenance.php' );
include( 'classes/class-aal-activity-log-list-table.php' );
include( 'classes/class-settings.php' );
include( 'classes/class-admin-ui.php' );
include( 'classes/class-api.php' );
include( 'classes/class-hooks.php' );

// Probably we should put this in a separate file
class AAL_Main {

	/**
	 * @var AAL_Admin_Ui
	 */
	public $ui;

	/**
	 * @var AAL_Hooks
	 */
	public $hooks;

	/**
	 * @var AAL_Settings
	 */
	public $settings;

	public function __construct() {
		global $wpdb;

		$this->ui 			= new AAL_Admin_Ui();
		$this->hooks 		= new AAL_Hooks();
		$this->settings     = new AAL_Settings();

		// set up our DB name
		$wpdb->activity_log = $wpdb->prefix . 'aryo_activity_log';
	}
	
}
global $aal_main_class;
$aal_main_class = new AAL_Main();

// EOF