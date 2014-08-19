<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Hook_Export extends AAL_Hook_Base {

	public function hook_export_wp( $args ) {
		
	}

	public function __construct() {
		add_action( 'export_wp', array( &$this, 'export_wp' ) );
		
		parent::__construct();
	}
	
}