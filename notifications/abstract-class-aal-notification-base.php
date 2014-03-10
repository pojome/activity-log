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
	public $id = '';
	public $name = '';
	public $description = '';
	
	public $aal_options;
	
	public function __construct() {
		$this->aal_options = AAL_Main::instance()->settings->get_options();
		
		add_action( 'init', array( &$this, 'init' ), 30 );
		add_action( 'aal_validate_options', array( &$this, '_validate_options' ), 10, 2 );
	}
	
	public function init() {}
	
	/**
	 * Registers the settings for this individual extension
	 */
	public function settings_fields() {}
	
	/**
	 * Exectutes when notification is due
	 */
	public function trigger( $args ) {}
	
	public function _settings_section_callback() {
		echo '<p>' . $this->description . '</p>';
	}
	
	public function _settings_enabled_field_callback( $args = array() ) {
		AAL_Settings_Fields::yesno_field( $args );
	}
	
	public function add_settings_field_helper( $option_name, $title, $callback, $description = '' ) {
		$settings_page_slug = AAL_Main::instance()->settings->slug();
		
		add_settings_field( 
			"notification_handler_{$this->id}_{$option_name}", 
			$title, 
			$callback, 
			$settings_page_slug, 
			"notification_{$this->id}",
			array(
				'name' 		=> $this->settings_field_name_attr( $option_name ),
				'value' 	=> isset( $this->aal_options[ $option_name ] ) ? $this->aal_options[ $option_name ] : '',
				'desc' 		=> $description,
				'id'      	=> $option_name,
				'page'    	=> $settings_page_slug,
			) 
		);
	}
	
	public function _validate_options( $form_data, $aal_options ) {
		$post_key 	= "notification_handler_options_{$this->id}";
		$option_key = "handler_options_{$this->id}";
	
		if ( ! isset( $_POST[ $post_key ] ) )
			return $form_data;
	
		$input = $_POST[ $post_key ];
		$output = $this->validate_options( $input );
		$form_data[ $option_key ] = $output;
	
		return $form_data;
	}
	
	/**
	 * This method is supposed to be overriden by the extending class
	 * @param array $input The formdata
	 * @return array
	 */
	private function validate_options( $input ) {
		return $input;
	}
	
	private function settings_field_name_attr( $name ) {
		return esc_attr( "notification_handler_options_{$this->id}[{$name}]" );
	}
	
	public function get_handler_options() {
		$handler_options = array();
		$option_key = "handler_options_{$this->id}";
		
		if ( isset( $this->aal_options[ $option_key ] ) ) {
			$handler_options = (array) $this->aal_options[ $option_key ];
		}
		
		return $handler_options;
	}
}

function aal_register_notification_handler( $classname = '' ) {
	return AAL_Main::instance()->notifications->register_handler( $classname );
}