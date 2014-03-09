<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Notification_Email extends AAL_Notification_Base {
	public $id = 'email';
	public $name = 'Email';
	public $description = 'Notify your users by email';
	
	/**
	 * Store options in a class locally
	 */
	protected $options = array();
	
	public function init() {
		$this->options = array_merge( array(
			'target_email' => get_option( 'admin_email' ),
		), $this->get_handler_options() );
	}
	
	public function trigger( $args ) {
		error_log( 'AAL: ' . var_export($args, true));
	}
	
	public function settings_fields() {
		$this->add_settings_field_helper( 'from_email', __( 'From Email', 'aryo-aal' ), array( 'AAL_Settings_Fields', 'text_field' ), __( 'The source Email address' ) );
		$this->add_settings_field_helper( 'to_email', __( 'To Email', 'aryo-aal' ), array( 'AAL_Settings_Fields', 'text_field' ), __( 'The Email address notifications will be sent to', 'aryo-aal' ) );
	}
	
	public function validate_options( $input ) {
		$output = $this->options;
		
		// @todo validate email
		if ( isset( $input['target_email'] ) )
			$output['target_email'] = $input['target_email'];
		
		return $output;
	}
}

// Register this handler, creates an instance of this class when necessary.
aal_register_notification_handler( 'AAL_Notification_Email' );