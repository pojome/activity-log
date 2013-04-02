<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HT_Model {

	public $action;
	public $object_type;
	public $object_subtype;
	public $object_name;
	public $object_id;
	public $user_id;
	public $histIP;
	public $histTime;
	public $userCaps;

	public function __construct() {
		/** @var $wpdb wpdb */
		global $wpdb;

		$this->_roles = array(
			// admin
			'manage_options' => array( 'Post', 'User', 'Attachment', 'Plugin' ),
			// editor
			'edit_pages'     => array( 'Post', 'Attachment' ),
		);


		$this->_caps = array(
			'administrator' => array( 'administrator', 'editor', 'author', 'guest' ),
			'editor'        => array( 'editor', 'author', 'guest' ),
			'author'        => array( 'author', 'guest' ),
		);
	}

	private function _deleteOldItems() {
		/** @var $wpdb wpdb */
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			'DELETE FROM `%1$s`
				WHERE `histTime` < %2$d',
			$wpdb->history_timeline,
			strtotime( '-30 days', current_time( 'timestamp' ) )
		) );
	}

	/**
	 * @deprecated
	 */
	public function insert() {
		/** @var $wpdb wpdb */
		global $wpdb;

		if ( empty( $this->histTime ) ) {
			$this->histTime = current_time( 'timestamp' );
		}

		$user = get_user_by( 'id', get_current_user_id() );
		if ( ! $user ) {
			$this->userCaps = 'guest';
			$this->user_id  = 0;
		}
		else {
			$this->userCaps = strtolower( key( $user->caps ) );
			$this->user_id  = $user->ID;
		}

		$this->histIP = $_SERVER['REMOTE_ADDR'];

		$wpdb->insert( $wpdb->history_timeline,
			array(
				'action'         => $this->action,
				'object_type'    => $this->object_type,
				'object_subtype' => $this->object_subtype,
				'object_name'    => $this->object_name,
				'object_id'      => $this->object_id,
				'user_id'        => $this->user_id,
				'userCaps'       => $this->userCaps,
				'histIP'         => $this->histIP,
				'histTime'       => $this->histTime,
			),
			array( "%s", "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%d" )
		);

		$this->_deleteOldItems();
	}
}