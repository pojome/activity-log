<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Notifications {
	
	public function __construct() {
		// Load abstract class.
		include( plugin_dir_path( ACTIVITY_LOG__FILE__ ) . '/notifications/abstract-class-aal-notification-base.php' );
		
		// TODO: Maybe I will use with glob() function for this.
		include( plugin_dir_path( ACTIVITY_LOG__FILE__ ) . '/notifications/class-aal-notification-email.php' );

		new AAL_Notification_Email();
	}
}
