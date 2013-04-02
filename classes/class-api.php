<?php

class HT_Api {

	protected $_table = 'history_timeline';
	
	public function insert( $args ) {
		/** @var $wpdb wpdb */
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'action'         => '',
			'object_type'    => '',
			'object_subtype' => '',
			'object_name'    => '',
			'object_id'      => '',
			'histIP'         => $_SERVER['REMOTE_ADDR'],
			'histTime'       => current_time( 'timestamp' ),
		) );

		$user = get_user_by( 'id', get_current_user_id() );
		if ( $user ) {
			$args['userCaps'] = strtolower( key( $user->caps ) );
			$args['user_id']  = $user->ID;
		} else {
			$args['userCaps'] = 'guest';
			$args['user_id']  = 0;
		} 

		$wpdb->insert( $this->_table,
			array(
				'action'         => $args['action'],
				'object_type'    => $args['object_type'],
				'object_subtype' => $args['object_subtype'],
				'object_name'    => $args['object_name'],
				'object_id'      => $args['object_id'],
				'user_id'        => $args['user_id'],
				'userCaps'       => $args['userCaps'],
				'histIP'         => $args['histIP'],
				'histTime'       => $args['histTime'],
			),
			array( "%s", "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%d" )
		);
	}
	
	public function __construct() {
		/** @var $wpdb wpdb */
		global $wpdb;
		$this->_table = $wpdb->prefix . $this->_table;
	}
	
}

function ht_insert_log( $args = array() ) {
	/** @var $ht_main_class HT_Main */
	global $ht_main_class;
	
	$ht_main_class->api->insert( $args );
}
