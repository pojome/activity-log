<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AAL_API {

	public function __construct() {
		add_action( 'admin_init', [ $this, 'maybe_add_schedule_delete_old_items' ] );
		add_action( 'aal/maintenance/clear_old_items', [ $this, 'delete_old_items' ] );
	}

	public function maybe_add_schedule_delete_old_items() {
		if ( ! wp_next_scheduled( 'aal/maintenance/clear_old_items' ) ) {
			wp_schedule_event( time(), 'daily', 'aal/maintenance/clear_old_items' );
		}
	}

	public function delete_old_items() {
		global $wpdb;

		$logs_lifespan = absint( AAL_Main::instance()->settings->get_option( 'logs_lifespan' ) );
		if ( empty( $logs_lifespan ) ) {
			return;
		}

		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM `' . $wpdb->activity_log . '`
					WHERE `hist_time` < %d',
				strtotime( '-' . $logs_lifespan . ' days', current_time( 'timestamp' ) )
			)
		);
	}

	/**
	 * Get real address
	 *
	 * @since 2.1.4
	 * @return string real address IP
	 */
	protected function _get_ip_address() {
		$header_key = AAL_Main::instance()->settings->get_option( 'log_visitor_ip_source' );
		
		if ( empty( $header_key ) ) {
			$header_key = 'REMOTE_ADDR';
		}
		
		if ( 'no-collect-ip' === $header_key ) {
			return '';
		}
		
		$visitor_ip_address = '';
		if ( ! empty( $_SERVER[ $header_key ] ) ) {
			$visitor_ip_address = $_SERVER[ $header_key ];
		}

		$remote_address = apply_filters( 'aal_get_ip_address', $visitor_ip_address );
		
		if ( ! empty( $remote_address ) && filter_var( $remote_address, FILTER_VALIDATE_IP ) ) {
			return $remote_address;
		}
		
		return '127.0.0.1';
	}

	/**
	 * @since 2.0.0
	 * @return void
	 */
	public function erase_all_items() {
		global $wpdb;

		$wpdb->query( 'TRUNCATE `' . $wpdb->activity_log . '`' );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return void
	 */
	public function insert( $args ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'action'         => '',
				'object_type'    => '',
				'object_subtype' => '',
				'object_name'    => '',
				'object_id'      => '',
				'hist_ip'        => $this->_get_ip_address(),
				'hist_time'      => current_time( 'timestamp' ),
			)
		);

		$args = $this->setup_userdata( $args );

		// Make sure for non duplicate.
		$check_duplicate = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT `histid` FROM `' . $wpdb->activity_log . '`
					WHERE `user_caps` = %s
						AND `action` = %s
						AND `object_type` = %s
						AND `object_subtype` = %s
						AND `object_name` = %s
						AND `user_id` = %s
						AND `hist_ip` = %s
						AND `hist_time` = %s
				;',
				$args['user_caps'],
				$args['action'],
				$args['object_type'],
				$args['object_subtype'],
				$args['object_name'],
				$args['user_id'],
				$args['hist_ip'],
				$args['hist_time']
			)
		);

		if ( $check_duplicate ) {
			return;
		}

		$should_skip_insert = apply_filters( 'aal_skip_insert_log', false, $args );

		if ( $should_skip_insert ) {
			return;
		}

		$wpdb->insert(
			$wpdb->activity_log,
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
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d' )
		);

		do_action( 'aal_insert_log', $args );
	}

	private function setup_userdata( $args ) {
		$user = false;

		if ( function_exists( 'get_user_by' ) ) {
			$user = get_user_by( 'id', get_current_user_id() );
		}

		if ( $user ) {
			$args['user_caps'] = strtolower( key( $user->caps ) );
			if ( empty( $args['user_id'] ) ) {
				$args['user_id'] = $user->ID;
			}
		} else {
			$args['user_caps'] = 'guest';
			if ( empty( $args['user_id'] ) ) {
				$args['user_id'] = 0;
			}
		}

		// TODO: Find better way to Multisite compatibility.
		// Fallback for multisite with bbPress
		if ( empty( $args['user_caps'] ) || 'bbp_participant' === $args['user_caps'] ) {
			$args['user_caps'] = 'administrator';
		}

		return $args;
	}
}

/**
 * @since 1.0.0
 *
 * @see AAL_API::insert
 *
 * @param array $args
 * @return void
 */
function aal_insert_log( $args = array() ) {
	AAL_Main::instance()->api->insert( $args );
}
