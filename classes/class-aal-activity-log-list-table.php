<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


class AAL_Activity_Log_List_Table extends WP_List_Table {
	
	protected $_roles = array();
	
	protected $_caps = array();

	protected function _get_where_by_role() {
		$allow_modules = array();

		$user = get_user_by( 'id', get_current_user_id() );
		if ( ! $user )
			wp_die( 'No allowed here.' );

		foreach ( $this->_roles as $key => $role ) {
			if ( current_user_can( $key ) ) {
				$allow_modules = array_merge( $allow_modules, $role );
			}
		}

		if ( empty( $allow_modules ) )
			wp_die( 'No allowed here.' );

		$allow_modules = array_unique( $allow_modules );

		$where = array();
		foreach ( $allow_modules as $type )
			$where[] .= '`object_type` = \'' . $type . '\'';

		$user_cap   = strtolower( key( $user->caps ) );
		$allow_caps = $where_caps = array();
		
		foreach ( $this->_caps as $key => $cap_allow ) {
			if ( $key === $user_cap ) {
				$allow_caps = array_merge( $allow_caps, $cap_allow );
				break;
			}
		}

		// TODO: Find better way to Multisite compatibility.
		if ( is_super_admin() )
			$allow_caps = $this->_caps['administrator'];

		if ( empty( $allow_caps ) )
			wp_die( 'No allowed here.' );

		$allow_caps = array_unique( $allow_caps );
		foreach ( $allow_caps as $cap )
			$where_caps[] .= '`user_caps` = \'' . $cap . '\'';

		return 'AND (' . implode( ' OR ', $where ) . ') AND (' . implode( ' OR ', $where_caps ) . ')';
	}
	
	public function __construct() {
		$this->_roles = apply_filters( 'aal_init_roles', array(
			// admin
			'manage_options' => array( 'Post', 'Taxonomy', 'User', 'Options', 'Attachment', 'Plugin', 'Widget', 'Theme', 'Menu' ),
			// editor
			'edit_pages'     => array( 'Post', 'Taxonomy', 'Attachment' ),
		) );

		$this->_caps = array(
			'administrator' => array( 'administrator', 'editor', 'author', 'guest' ),
			'editor'        => array( 'editor', 'author', 'guest' ),
			'author'        => array( 'author', 'guest' ),
		);

		parent::__construct( array(
			'singular'  => 'activity',
		) );
	}

	public function get_columns() {
		$columns = array(
			'type'		=> _x( 'Type', 'main table', 'aryo-aal' ),
			'name'		=> _x( 'Name', 'main table', 'aryo-aal' ),
			'action'	=> _x( 'Action', 'main table', 'aryo-aal' ),
			'date'		=> _x( 'Date', 'main table', 'aryo-aal' ),
		);

		return $columns;
	}

	public function column_default( $item, $column_name ) {
		$return = '';
		
		switch ( $column_name ) {
			case 'action' :
				$return = __( $item->action, 'aryo-aal' );
				break;
			case 'date' :
				$return = sprintf( '<strong>' . __( '%s ago', 'aryo-aal' ) . '</strong>', human_time_diff( $item->hist_time, current_time( 'timestamp' ) ) );
				$return .= '<br />' . date( 'd/m/Y H:i', $item->hist_time );
				break;
			default :
				if ( isset( $item->$column_name ) )
					$return = $item->$column_name;
		}
		
		return $return;
	}

	public function column_type( $item ) {
		$return = __( $item->object_type, 'aryo-aal' );
		
		if ( ! empty( $item->object_subtype ) ) {
			$pt = get_post_type_object( $item->object_subtype );
			$label = ! empty( $pt->label ) ? $pt->label : $item->object_subtype;
			$return .= sprintf( '<span class="aal-pt" title="%s">%s</span>', $label, $item->object_subtype );
		}

		$user       = false;
		$return     .= '<br />' . __( 'by ', 'aryo-aal' );
		if ( ! empty( $item->user_id ) )
			$user = get_user_by( 'id', $item->user_id );

		if ( $user )
			$return .= '<a href="user-edit.php?user_id=' . absint( $user->ID ) . '">' . esc_html( $user->user_login ) . '</a>';
		else
			$return .= __( 'Guest', 'aryo-aal' );
		
		$return .= ' (<code>' . $item->hist_ip . '</code>)';
		
		return $return;
	}
	
	public function column_name( $item ) {
		$return = $item->object_name;
		
		switch ( $item->object_type ) {
			case 'Post' :
				$return = '<a href="post.php?post=' . $item->object_id . '&action=edit">' . $item->object_name . '</a>';
				break;
			
			case 'Taxonomy' :
				if ( ! empty( $item->object_id ) )
					$return = sprintf( '<a href="edit-tags.php?action=edit&taxonomy=%s&tag_ID=%d">%s</a>', $item->object_subtype, $item->object_id, $item->object_name );
				break;
		}
		
		return $return;
	}

	public function extra_tablenav( $which ) {
		global $wpdb;

		if ( 'top' !== $which )
			return;

		echo '<div class="alignleft actions">';

		$users = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM `%1$s`
				WHERE 1 = 1
				' . $this->_get_where_by_role() . '
					GROUP BY `user_id`
					ORDER BY `user_id`
					;',
			$wpdb->activity_log
		) );

		if ( $users ) {
			if ( ! isset( $_REQUEST['usershow'] ) )
				$_REQUEST['usershow'] = '';

			$output = array();
			foreach ( $users as $_user ) {
				if ( 0 === (int) $_user->user_id ) {
					$output[0] = __( 'Guest', 'aryo-aal' );
					continue;
				}

				$user = get_user_by( 'id', $_user->user_id );
				if ( $user )
					$output[ $user->ID ] = $user->user_nicename;
			}

			if ( ! empty( $output ) ) {
				echo '<select name="usershow" id="hs-filter-usershow">';
				printf( '<option value="">%s</option>', __( 'All Users', 'aryo-aal' ) );
				foreach ( $output as $key => $value ) {
					printf( '<option value="%s"%s>%s</option>', $key, selected( $_REQUEST['usershow'], $key, false ), $value );
				}
				echo '</select>';
			}
		}

		$types = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM `%1$s`
				WHERE 1 = 1
				' . $this->_get_where_by_role() . '
				GROUP BY `object_type`
				ORDER BY `object_type`
			;',
			$wpdb->activity_log
		) );

		if ( $types ) {
			if ( ! isset( $_REQUEST['typeshow'] ) )
				$_REQUEST['typeshow'] = '';

			$output = array();
			foreach ( $types as $type )
				$output[] = sprintf( '<option value="%1$s"%2$s>%1$s</option>', $type->object_type, selected( $_REQUEST['typeshow'], $type->object_type, false ) );

			echo '<select name="typeshow" id="hs-filter-typeshow">';
			printf( '<option value="">%s</option>', __( 'All Types', 'aryo-aal' ) );
			echo implode( '', $output );
			echo '</select>';
		}

		if ( $users || $types )
			submit_button( __( 'Filter', 'aryo-aal' ), 'button', false, false, array( 'id' => 'activity-query-submit' ) );

		echo '</div>';
	}
	
	public function prepare_items() {
		global $wpdb;
	
		/** @todo: add setting page with this value. */
		$items_per_page        = 20;
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$where                 = ' WHERE 1=1';
		
		if ( ! empty( $_REQUEST['typeshow'] ) ) {
			$where .= $wpdb->prepare( ' AND `object_type` = \'%s\'', $_REQUEST['typeshow'] );
		}

		if ( isset( $_REQUEST['usershow'] ) && '' !== $_REQUEST['usershow'] ) {
			$where .= $wpdb->prepare( ' AND `user_id` = %d', $_REQUEST['usershow'] );
		}

		$offset = ( $this->get_pagenum() - 1 ) * $items_per_page;
		
		$total_items = $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(`histid`) FROM `%1$s`
				' . $where . '
					' . $this->_get_where_by_role(),
			$wpdb->activity_log,
			$offset,
			$items_per_page
		) );
		
		$this->items = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM `%1$s`
				' . $where . '
					' . $this->_get_where_by_role() . '
					ORDER BY `hist_time` DESC
					LIMIT %2$d, %3$d;',
			$wpdb->activity_log,
			$offset,
			$items_per_page
		) );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $items_per_page,
			'total_pages' => ceil( $total_items / $items_per_page )
		) );
	}
	
}