<?php
/*
Plugin Name: ARYO Activity Log
Plugin URI: http://wordpress.org/extend/plugins/activity-log
Description: Get aware of the activities that are taking place on your dashboard! Weather a post was deleted or a plugin was activated, it’s all these for you to see.
Author: Yakir Sitbon, Maor Chasen
Version: 1.0
Author URI: http://www.aryo.co.il
License: GPLv2 or later


This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	
	public function load_textdomain() {
		load_textdomain( 'aryo-aal', false, basename( dirname( __FILE__ ) ) . '/language' );
	}

	public function __construct() {
		global $wpdb;

		$this->ui 			= new AAL_Admin_Ui();
		$this->hooks 		= new AAL_Hooks();
		$this->settings     = new AAL_Settings();

		// set up our DB name
		$wpdb->activity_log = $wpdb->prefix . 'aryo_activity_log';
		
		add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );
	}
	
}
global $aal_main_class;
$aal_main_class = new AAL_Main();

// EOF