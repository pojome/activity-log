<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Notification_HipChat extends AAL_Notification_Base {
	public $id = 'atlassian-hipchat';
	public $name = 'Atlassian HipChat';
	public $description = 'Notify users on channels.';
	
	public function trigger() {
	
	}
	
	public function settings_fields() {
	
	}
}

// Register this handler, creates an instance of this class when necessary.
aal_register_notification_handler( 'AAL_Notification_HipChat' );