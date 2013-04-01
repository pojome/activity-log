<?php

class HS_Model {

	public $action;
	public $object_type;
	public $object_subtype;
	public $object_name;
	public $object_id;
	public $user_id;
	public $histIP;
	public $histTime;
	public $userCaps;

	public $_roles = array();
	public $_caps = array();

	private $_paginateLimit = 100;
	private $_paginateOffset;
	private $_paginatePageNum = 1;
	private $_paginateTotal;

	private $_table;

	public function __construct() {
		/** @var $wpdb wpdb */
		global $wpdb;

		$this->_table = $wpdb->prefix . 'history_timeline';


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
			$this->_table,
			strtotime( '-30 days', current_time( 'timestamp' ) )
		) );
	}

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

		$wpdb->insert( $this->_table,
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

	public function getLastResult( $args = array() ) {
		/** @var $wpdb wpdb */
		global $wpdb;

		$defaults = array(
			'byObjectType' => null,
			'byUserID'     => null,
			'limit'        => null,
		);

		//$this->_getWhereByRole();

		$args = wp_parse_args( $args, $defaults );

		$where = ' WHERE 1=1';

		if ( ! is_null( $args['byObjectType'] ) ) {
			$where .= ' AND `object_type` = \'' . $args['byObjectType'] . '\'';
		}

		if ( ! is_null( $args['byUserID'] ) ) {
			$where .= ' AND `user_id` = ' . $args['byUserID'];
		}

		$this->_paginatePageNum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$this->_paginateOffset  = ( $this->_paginatePageNum - 1 ) * $this->_paginateLimit;

		$this->_paginateTotal = $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(`histid`) FROM `%1$s`
				' . $where . '
					' . $this->_getWhereByRole(),
			$this->_table,
			$this->_paginateOffset,
			$this->_paginateLimit
		) );

		$sql = $wpdb->prepare(
			'SELECT * FROM `%1$s`
				' . $where . '
					' . $this->_getWhereByRole() . '
					ORDER BY `histTime` DESC
					LIMIT %2$d, %3$d;',
			$this->_table,
			$this->_paginateOffset,
			$this->_paginateLimit
		);

		return $wpdb->get_results( $sql );
	}

	public function thePaginate() {
		$num_of_pages = ceil( $this->_paginateTotal / $this->_paginateLimit );

		$page_links = paginate_links( array(
			'base'      => add_query_arg( 'pagenum', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;', 'aag' ),
			'next_text' => __( '&raquo;', 'aag' ),
			'total'     => $num_of_pages,
			'mid_size'  => 6,
			'current'   => $this->_paginatePageNum,
		) );

		echo $page_links;
	}

	public function getAllObjectTypes() {
		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT * FROM `%1$s`
				WHERE 1=1
				' . $this->_getWhereByRole() . '
					GROUP BY `object_type`
					ORDER BY `object_type`
					;',
			$this->_table
		);

		$types = $wpdb->get_results( $sql );

		if ( ! $types )
			return false;

		$output   = array();
		$baseLink = get_bloginfo( 'wpurl' ) . '/wp-admin/index.php?page=history_timeline_page';

		if ( isset( $_GET['usershow'] ) ) {
			$baseLink = add_query_arg( 'usershow', $_GET['usershow'], $baseLink );
		}

		$output[] = '<a href="' . $baseLink . '">All Modules</a>';

		foreach ( $types as $type ) {
			$output[] = '<a href="' . add_query_arg( 'typeshow', $type->object_type, $baseLink ) . '">' . $type->object_type . '</a>';
		}

		return implode( ' | ', $output );
	}

	public function getAllUsers() {
		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT * FROM `%1$s`
				WHERE 1=1
				' . $this->_getWhereByRole() . '
					GROUP BY `user_id`
					ORDER BY `user_id`
					;',
			$this->_table
		);

		$users = $wpdb->get_results( $sql );

		if ( ! $users )
			return false;

		$output   = array();
		$baseLink = get_bloginfo( 'wpurl' ) . '/wp-admin/index.php?page=history_timeline_page';

		if ( isset( $_GET['typeshow'] ) ) {
			$baseLink = add_query_arg( 'typeshow', $_GET['typeshow'], $baseLink );
		}

		$output[] = '<a href="' . $baseLink . '">All Users</a>';

		foreach ( $users as $row ) {
			if ( (int) $row->user_id === 0 ) {
				$output[] = '<a href="' . add_query_arg( 'usershow', 0, $baseLink ) . '">Guest</a>';
				continue;
			}
			$user = get_user_by( 'id', $row->user_id );

			if ( ! $user ) {
				continue;
			}

			$output[] = '<a href="' . add_query_arg( 'usershow', $user->ID, $baseLink ) . '">' . $user->user_nicename . '</a>';
		}

		return implode( ' | ', $output );
	}

	private function _getWhereByRole() {
		$allowModules = array();

		$user = get_user_by( 'id', get_current_user_id() );
		if ( ! $user ) {
			wp_die( 'No allowed here1' );
		}

		foreach ( $this->_roles as $key => $role ) {
			if ( current_user_can( $key ) ) {
				$allowModules = array_merge( $allowModules, $role );
			}
		}

		if ( empty( $allowModules ) ) {
			wp_die( 'No allowed here2' );
		}

		$allowModules = array_unique( $allowModules );

		$where = array();
		foreach ( $allowModules as $type ) {
			$where[] .= '`object_type` = \'' . $type . '\'';
		}


		$userCap = strtolower( key( $user->caps ) );

		$allowCaps = $whereCaps = array();

		foreach ( $this->_caps as $key => $capAllow ) {
			if ( $key === $userCap ) {
				$allowCaps = array_merge( $allowCaps, $capAllow );
				break;
			}
		}

		if ( empty( $allowCaps ) ) {
			wp_die( 'No allowed here3' );
		}

		$whereCaps = array_unique( $whereCaps );

		foreach ( $allowCaps as $cap ) {
			$whereCaps[] .= '`userCaps` = \'' . $cap . '\'';
		}

		return 'AND (' . implode( ' OR ', $where ) . ') AND (' . implode( ' OR ', $whereCaps ) . ') AND `histTime` > ' . strtotime( '-30 days', current_time( 'timestamp' ) );
	}
}