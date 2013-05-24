<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Hooks {

	protected function _add_log_attachment( $action, $attachment_id ) {
		$post = get_post( $attachment_id );

		aal_insert_log( array(
				'action'         => $action,
				'object_type'    => 'Attachment',
				'object_subtype' => $post->post_type,
				'object_id'      => $attachment_id,
				'object_name'    => get_the_title( $post->ID ),
			) );
	}

	protected function _add_log_plugin( $action, $plugin_name ) {
		//get plugin name if is a path
		if ( false !== strpos( $plugin_name, "/" ) ) {
			$plugin_dir = explode( "/", $plugin_name );
			$data = get_plugins( "/".$plugin_dir[0] );
			foreach ( (array)$data as $file => $p ) {
				$plugin_name = $p['Name'];
			}
		}
		aal_insert_log( array(
				'action'      => $action,
				'object_type' => 'Plugin',
				'object_id'   => 0,
				'object_name' => $plugin_name,
			) );
	}

	public function init() {
		// User
		add_filter( 'wp_login_failed', array( &$this, 'hooks_wrong_password' ) );
		add_action( 'wp_login', array( &$this, 'hooks_wp_login' ), 10, 2 );
		add_action( 'wp_logout', array( &$this, 'hooks_wp_logout' ) );
		add_action( 'delete_user', array( &$this, 'hooks_delete_user' ) );
		add_action( 'user_register', array( &$this, 'hooks_user_register' ) );
		add_action( 'profile_update', array( &$this, 'hooks_profile_update' ) );

		// Plugins
		add_action( 'activated_plugin', array( &$this, 'hooks_activated_plugin' ) );
		add_action( 'deactivated_plugin', array( &$this, 'hooks_deactivated_plugin' ) );
		add_filter( 'wp_redirect', array( &$this, 'hooks_plugin_modify' ), 10, 2 );

		// Theme
		add_filter( 'wp_redirect', array( &$this, 'hooks_theme_modify' ), 10, 2 );

		// Theme customizer
		add_action( 'customize_save', array( &$this, 'hooks_theme_customizer_modified' ), 10 );
		add_action( 'customize_preview_init', array( &$this, 'hooks_theme_customizer_modified' ), 10 );

	}

	public function admin_init() {
		// Posts
		add_action( 'transition_post_status', array( &$this, 'hooks_transition_post_status' ), 10, 3 );
		add_action( 'delete_post', array( &$this, 'hooks_delete_post' ) );

		// Attachments
		add_action( 'add_attachment', array( &$this, 'hooks_add_attachment' ) );
		add_action( 'edit_attachment', array( &$this, 'hooks_edit_attachment' ) );
		add_action( 'delete_attachment', array( &$this, 'hooks_delete_attachment' ) );

		// Widgets
		add_filter( 'widget_update_callback', array( &$this, 'hooks_widget_update_callback' ), 9999, 4 );
	}

	public function hooks_delete_attachment( $attachment_id ) {
		$this->_add_log_attachment( 'deleted', $attachment_id );
	}

	public function hooks_edit_attachment( $attachment_id ) {
		$this->_add_log_attachment( 'updated', $attachment_id );
	}

	public function hooks_add_attachment( $attachment_id ) {
		$this->_add_log_attachment( 'added', $attachment_id );
	}

	public function hooks_deactivated_plugin( $plugin_name ) {
		$this->_add_log_plugin( 'deactivated', $plugin_name );
	}

	public function hooks_activated_plugin( $plugin_name ) {
		$this->_add_log_plugin( 'activated', $plugin_name );
	}

	public function hooks_profile_update( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		aal_insert_log( array(
				'action'      => 'updated',
				'object_type' => 'User',
				'object_id'   => $user->ID,
				'object_name' => $user->user_nicename,
			) );
	}

	public function hooks_user_register( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		aal_insert_log( array(
				'action'      => 'created',
				'object_type' => 'User',
				'object_id'   => $user->ID,
				'object_name' => $user->user_nicename,
			) );
	}

	public function hooks_delete_user( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		aal_insert_log( array(
				'action'      => 'deleted',
				'object_type' => 'User',
				'object_id'   => $user->ID,
				'object_name' => $user->user_nicename,
			) );
	}

	public function hooks_wrong_password( $username ) {
		aal_insert_log( array(
				'action'      => 'wrong_password',
				'object_type' => 'User',
				'user_id'     => 0,
				'object_id'   => 0,
				'object_name' => $username,
			) );
	}

	public function hooks_wp_login( $user_login, $user ) {
		aal_insert_log( array(
				'action'      => 'logged_in',
				'object_type' => 'User',
				'user_id'     => $user->ID,
				'object_id'   => $user->ID,
				'object_name' => $user->user_nicename,
			) );
	}

	public function hooks_wp_logout() {
		$user = wp_get_current_user();

		aal_insert_log( array(
				'action'      => 'logged_out',
				'object_type' => 'User',
				'user_id'     => $user->ID,
				'object_id'   => $user->ID,
				'object_name' => $user->user_nicename,
			) );
	}

	public function hooks_transition_post_status( $new_status, $old_status, $post ) {
		$action = '';

		if ( 'auto-draft' === $old_status && ( 'auto-draft' !== $new_status && 'inherit' !== $new_status ) ) {
			// page created
			$action = 'created';
		}
		elseif ( 'auto-draft' === $new_status || ( 'new' === $old_status && 'inherit' === $new_status ) ) {
			// nvm.. ignore it.
			return;
		}
		elseif ( 'trash' === $new_status ) {
			// page was deleted.
			$action = 'deleted';
		}
		else {
			// page updated. i guess.
			$action = 'updated';
		}

		if ( wp_is_post_revision( $post->ID ) )
			return;

		aal_insert_log( array(
				'action'         => $action,
				'object_type'    => 'Post',
				'object_subtype' => $post->post_type,
				'object_id'      => $post->ID,
				'object_name'    => get_the_title( $post->ID ),
			) );
	}

	public function hooks_delete_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) )
			return;

		$post = get_post( $post_id );

		if ( in_array( $post->post_status, array( 'auto-draft', 'inherit' ) ) )
			return;

		aal_insert_log( array(
				'action'         => 'deleted',
				'object_type'    => 'Post',
				'object_subtype' => $post->post_type,
				'object_id'      => $post->ID,
				'object_name'    => get_the_title( $post->ID ),
			) );
	}

	public function hooks_widget_update_callback( $instance, $new_instance, $old_instance, WP_Widget $widget ) {
		$aal_args = array(
			'action'         => 'updated',
			'object_type'    => 'Widget',
			'object_subtype' => 'sidebar_unknown',
			'object_id'      => 0,
			'object_name'    => $widget->id_base,
		);

		if ( ! empty( $_REQUEST['sidebar'] ) )
			$aal_args['object_subtype'] = strtolower( $_REQUEST['sidebar'] );

		/** @todo: find any way to widget deleted detected */
		/*if ( isset( $_REQUEST['delete_widget'] ) && '1' === $_REQUEST['delete_widget'] ) {
			$aal_args['action'] = 'deleted';
		}*/

		aal_insert_log( $aal_args );

		// We are need return the instance, for complete the filter.
		return $instance;
	}

	public function hooks_theme_modify( $location, $status ) {
		if ( false !== strpos( $location, 'theme-editor.php?file=' ) ) {
			if ( ! empty( $_POST ) && 'update' === $_POST['action'] ) {
				$aal_args = array(
					'action'         => 'file_updated',
					'object_type'    => 'Theme',
					'object_subtype' => 'theme_unknown',
					'object_id'      => 0,
					'object_name'    => 'file_unknown',
				);

				if ( ! empty( $_POST['file'] ) )
					$aal_args['object_name'] = $_POST['file'];

				if ( ! empty( $_POST['theme'] ) )
					$aal_args['object_subtype'] = $_POST['theme'];

				aal_insert_log( $aal_args );
			}
		}

		// We are need return the instance, for complete the filter.
		return $location;
	}

	public function hooks_plugin_modify( $location, $status ) {
		if ( false !== strpos( $location, 'plugin-editor.php' ) ) {
			if ( ( ! empty( $_POST ) && 'update' === $_REQUEST['action'] ) ) {
				$aal_args = array(
					'action'         => 'file_updated',
					'object_type'    => 'Plugin',
					'object_subtype' => 'plugin_unknown',
					'object_id'      => 0,
					'object_name'    => 'file_unknown',
				);

				if ( ! empty( $_REQUEST['file'] ) ) {
					$aal_args['object_name'] = $_REQUEST['file'];
					// Get plugin name
					$plugin_dir  = explode( '/', $_REQUEST['file'] );
					$plugin_data = array_shift( array_values( get_plugins( '/' . $plugin_dir[0] ) ) );
					
					$aal_args['object_subtype'] = $plugin_data['Name'];
				}
				aal_insert_log( $aal_args );
			}
		}

		// We are need return the instance, for complete the filter.
		return $location;
	}

	public function hooks_theme_customizer_modified( $obj ) {
		$aal_args = array(
			'action'         => 'updated',
			'object_type'    => 'Theme',
			'object_subtype' => $obj->theme()->display( 'Name' ),
			'object_id'      => 0,
			'object_name'    => 'theme customizer',
		);

		if ( 'customize_preview_init' === current_filter() )
			$aal_args['action'] = 'accessed';

		aal_insert_log( $aal_args );
	}

	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}
}
