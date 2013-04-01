<?php

class HS_Hooks {

	private function _add_log_attachment( $action, $attachment_id ) {
		$post = get_post( $attachment_id );

		$history = new HS_Model();

		$history->action         = $action;
		$history->object_type    = 'Attachment';
		$history->object_subtype = $post->post_type;
		$history->object_id      = $attachment_id;
		$history->object_name    = get_the_title( $post->ID );

		$history->insert();
	}
	
	public function init() {
		add_filter( 'wp_login_failed', array( &$this, 'hooks_wrong_password' ) );
		add_action( 'wp_login', array( &$this, 'hooks_wp_login' ) );
		add_action( 'wp_logout', array( &$this, 'hooks_wp_logout' ) );
		add_action( 'delete_user', array( &$this, 'hooks_delete_user' ) );
		add_action( 'user_register', array( &$this, 'hooks_user_register' ) );
		add_action( 'profile_update', array( &$this, 'hooks_profile_update' ) );

		add_action( 'activated_plugin', array( &$this, 'hooks_activated_plugin' ) );
		add_action( 'deactivated_plugin', array( &$this, 'hooks_deactivated_plugin' ) );
	}

	public function admin_init() {
		add_action( 'transition_post_status', array( &$this, 'hooks_transition_post_status' ), 10, 3 );
		add_action( 'delete_post', array( &$this, 'hooks_delete_post' ) );

		add_action( 'add_attachment', array( &$this, 'hooks_add_attachment' ) );
		add_action( 'edit_attachment', array( &$this, 'hooks_edit_attachment' ) );
		add_action( 'delete_attachment', array( &$this, 'hooks_delete_attachment' ) );
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
		$history = new HS_Model();

		$history->action      = 'deactivated';
		$history->object_type = 'Plugin';
		$history->object_id   = 0;
		$history->object_name = $plugin_name;

		$history->insert();
	}

	public function hooks_activated_plugin( $plugin_name ) {
		$history = new HS_Model();

		$history->action      = 'activated';
		$history->object_type = 'Plugin';
		$history->object_id   = 0;
		$history->object_name = $plugin_name;

		$history->insert();
	}

	public function hooks_profile_update( $user_id ) {
		$history = new HS_Model();

		$user = get_user_by( 'id', $user_id );

		$history->action      = 'updated';
		$history->object_type = 'User';
		$history->object_id   = $user->ID;
		$history->object_name = $user->user_nicename;

		$history->insert();
	}

	public function hooks_user_register( $user_id ) {
		$history = new HS_Model();

		$user = get_user_by( 'id', $user_id );

		$history->action      = 'created';
		$history->object_type = 'User';
		$history->object_id   = $user->ID;
		$history->object_name = $user->user_nicename;

		$history->insert();
	}

	public function hooks_delete_user( $user_id ) {
		$history = new HS_Model();

		$user = get_user_by( 'id', $user_id );

		$history->action      = 'deleted';
		$history->object_type = 'User';
		$history->object_id   = $user->ID;
		$history->object_name = $user->user_nicename;

		$history->insert();
	}

	public function hooks_wrong_password() {
		$history = new HS_Model();

		$history->action      = 'wrong_password';
		$history->user_id     = 0;
		$history->object_id   = 0;
		$history->object_type = 'User';
		$history->object_name = $_REQUEST['log'];

		$history->insert();
	}

	public function hooks_wp_login( $user ) {
		$user = get_user_by( 'login', $user );

		$history = new HS_Model();

		$history->action      = 'logged_in';
		$history->user_id     = $user->ID;
		$history->object_type = 'User';
		$history->object_id   = $user->ID;
		$history->object_name = $user->user_nicename;

		$history->insert();
	}

	public function hooks_wp_logout() {
		$user = wp_get_current_user();

		$history = new HS_Model();

		$history->action      = 'logged_out';
		$history->user_id     = $user->ID;
		$history->object_type = 'User';
		$history->object_id   = $user->ID;
		$history->object_name = $user->user_nicename;

		$history->insert();
	}

	public function hooks_transition_post_status( $new_status, $old_status, $post ) {
		$action = '';

		if ( $old_status === 'auto-draft' && ( $new_status !== 'auto-draft' && $new_status !== 'inherit' ) ) {
			// page created
			$action = 'created';
		}
		elseif ( $new_status === 'auto-draft' || ( $old_status === 'new' && $new_status === 'inherit' ) ) {
			// nvm.. ignore it.
			return;
		}
		elseif ( $new_status === "trash" ) {
			// page was deleted.
			$action = 'deleted';
		}
		else {
			// page updated. i guess.
			$action = 'updated';
		}

		if ( $post->post_type === 'revision' ) {
			// don't log revisions
			return;
		}

		if ( wp_is_post_revision( $post->ID ) ) {
			return;
		}

		$history = new HS_Model();

		$history->action         = $action;
		$history->user_id        = get_current_user_id();
		$history->object_type    = 'Post';
		$history->object_subtype = $post->post_type;
		$history->object_id      = $post->ID;
		$history->object_name    = get_the_title( $post->ID );

		$history->insert();
	}

	public function hooks_delete_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( $post->post_status === 'auto-draft' || $post->post_status === 'inherit' ) {
			return;
		}

		$history = new HS_Model();

		$history->action         = 'deleted';
		$history->user_id        = get_current_user_id();
		$history->object_type    = 'Post';
		$history->object_subtype = $post->post_type;
		$history->object_id      = $post->ID;
		$history->object_name    = get_the_title( $post->ID );

		$history->insert();
	}
	
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}
}
new HS_Hooks();
