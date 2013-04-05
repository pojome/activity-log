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

define( 'HT_TEXT_DOMAIN', 'histimeline' );

include( 'classes/class-maintenance.php' );
include( 'classes/class-ht-history-list-table.php' );
include( 'classes/class-settings.php' );
include( 'classes/class-admin-ui.php' );
include( 'classes/class-api.php' );
include( 'classes/class-hooks.php' );

// Probably we should put this in a separate file
class HT_Main {

	public $ui;
	public $hooks;
	public $settings;

	public function __construct() {
		global $wpdb;

		$this->ui 			= new HT_Admin_Ui();
		$this->hooks 		= new HT_Hooks();
		$this->settings     = new HT_Settings();

		// set up our DB name
		$wpdb->activity_log = $wpdb->prefix . 'activity_log';
	}
	
}
global $ht_main_class;
$ht_main_class = new HT_Main();

// EOF