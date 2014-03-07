<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Notification_HipChat extends AAL_Notification_Base {
	public $id = 'atlassian-hipchat';
	public $name = 'Atlassian HipChat';
	public $description = 'Notify users on channels.';
	
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
		
	}
	
	public function settings_fields() {
		$this->add_settings_field_helper( 'api_token', __( 'API access token', 'aryo-aal' ), array( 'AAL_Settings_Fields', 'text_field' ), __( 'The API token is accessible at Account Settings &rarr; API access' ) );
	}
}

// Register this handler, creates an instance of this class when necessary.
aal_register_notification_handler( 'AAL_Notification_HipChat' );