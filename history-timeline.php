<?php
/*
 Plugin Name: History Timeline
 Plugin URI: http://www.aryo.co.il/
 Description: Never mind.. Created by Yakir Sitbon.
 Author: Yakir Sitbon
 Version: 0.1
 Author URI: http://www.yakirs.net/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'HISTORY_TIMELINE_BASE', plugin_basename( __FILE__ ) );

include( 'classes/maintenance.php' );
include( 'classes/class-model.php' );
include( 'classes/settings.php' );
include( 'classes/admin-ui.php' );
include( 'classes/hooks.php' );

// Probably we should put this in a separate file
class HT_Main {

	public $ui;
	public $hooks;
	public $settings;

	public function __construct() {
		$this->ui 			= new HT_Admin_Ui();
		$this->hooks 		= new HT_Hooks();
		$this->settings     = new HT_Settings();
	}
}
new HT_Main;

// EOF