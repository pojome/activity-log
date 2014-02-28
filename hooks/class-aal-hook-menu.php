<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Hook_Menu extends AAL_Hook_Base {

	public function hooks_menu_updated( $nav_menu_selected_id ) {
		if ( $menu_object = wp_get_nav_menu_object( $nav_menu_selected_id ) ) {
			aal_insert_log( array(
				'action'         => 'updated',
				'object_type'    => 'Menu',
				'object_name'    => $menu_object->name,
			) );
		}
	}
	
	public function __construct() {
		add_action( 'wp_update_nav_menu', array( &$this, 'hooks_menu_updated' ) );

		parent::__construct();
	}

}