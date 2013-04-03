<?php

class HT_API {

	protected function _delete_old_items() {
		/** @var $wpdb wpdb */
		global $wpdb;
		
		/** @todo: Add setting value for days. */

		$wpdb->query( $wpdb->prepare(
			'DELETE FROM `%1$s`
				WHERE `histTime` < %2$d',
			$wpdb->history_timeline,
			strtotime( '-30 days', current_time( 'timestamp' ) )
		) );
	}

	public static function insert( $args ) {
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
			if ( empty( $args['user_id'] ) )
				$args['user_id']  = $user->ID;
		} else {
			$args['userCaps'] = 'guest';
			if ( empty( $args['user_id'] ) )
				$args['user_id']  = 0;
		} 

		$wpdb->insert( $wpdb->history_timeline,
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

		// Remove old items.
		self::_delete_old_items();
		do_action( 'ht_insert_log', $args );
	}

}

function ht_insert_log( $args = array() ) {
	HT_API::insert( $args );
}
