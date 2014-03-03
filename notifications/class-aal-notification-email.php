<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Notification_Email extends AAL_Notification_Base {
	public $name = 'Email';
}

// Register this handler, creates an instance of this class when necessary.
aal_register_notification_handler( 'AAL_Notification_Email' );