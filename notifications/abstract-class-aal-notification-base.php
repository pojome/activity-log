<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Base class, handles notifications
 * 
 * Class AAL_Notification_Base
 */
abstract class AAL_Notification_Base {
	/**
	 * The following variables have to be defined for each payment method.
	 */
	public $name = '';
	
	public function __construct() {}
	
}

function aal_register_notification_handler( $classname = '' ) {
	return AAL_Main::instance()->notifications->register_handler( $classname );
}