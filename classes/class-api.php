<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_API {

	protected function _delete_old_items() {
		global $wpdb;
		
		$logs_lifespan = absint( AAL_Settings::get_option( 'logs_lifespan' ) );
		if ( empty( $logs_lifespan ) )
			return;
		
		$wpdb->query( $wpdb->prepare(
			'DELETE FROM `%1$s`
				WHERE `hist_time` < %2$d',
			$wpdb->activity_log,
			strtotime( '-' . $logs_lifespan . ' days', current_time( 'timestamp' ) )
		) );
	}

	public static function insert( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'action'         => '',
			'object_type'    => '',
			'object_subtype' => '',
			'object_name'    => '',
			'object_id'      => '',
			'hist_ip'        => $_SERVER['REMOTE_ADDR'],
			'hist_time'      => current_time( 'timestamp' ),
		) );

		$user = get_user_by( 'id', get_current_user_id() );
		if ( $user ) {
			$args['user_caps'] = strtolower( key( $user->caps ) );
			if ( empty( $args['user_id'] ) )
				$args['user_id']  = $user->ID;
		} else {
			$args['user_caps'] = 'guest';
			if ( empty( $args['user_id'] ) )
				$args['user_id']  = 0;
		} 

		$wpdb->insert( $wpdb->activity_log,
			array(
				'action'         => $args['action'],
				'object_type'    => $args['object_type'],
				'object_subtype' => $args['object_subtype'],
				'object_name'    => $args['object_name'],
				'object_id'      => $args['object_id'],
				'user_id'        => $args['user_id'],
				'user_caps'      => $args['user_caps'],
				'hist_ip'        => $args['hist_ip'],
				'hist_time'      => $args['hist_time'],
			),
			array( "%s", "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%d" )
		);

		// Remove old items.
		//self::_delete_old_items();
		do_action( 'aal_insert_log', $args );
	}

}

function aal_insert_log( $args = array() ) {
	AAL_API::insert( $args );
}
