<?php
/*
 Plugin Name: ARYO History System
 Plugin URI: http://www.aryo.co.il/
 Description: Never mind.. Created by Yakir Sitbon.
 Author: Yakir Sitbon
 Version: 0.1
 Author URI: http://www.yakirs.net/
*/


if ( ! class_exists( 'Aryo_WordPress_History_System' ) ) {
	
	class Aryo_WordPress_History_System {

		public function activated() {
			/** @var $wpdb wpdb */
			global $wpdb;
			
			$wpdb->query(
					"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}aryo_history_system` (
					  `histid` int(11) NOT NULL AUTO_INCREMENT,
					  `userCaps` varchar(70) NOT NULL DEFAULT 'guest',
					  `action` varchar(255) NOT NULL,
					  `object_type` varchar(255) NOT NULL,
					  `object_subtype` varchar(255) NOT NULL DEFAULT '',
					  `object_name` varchar(255) NOT NULL,
					  `object_id` int(11) NOT NULL DEFAULT '0',
					  `user_id` int(11) NOT NULL DEFAULT '0',
					  `histIP` varchar(55) NOT NULL DEFAULT '127.0.0.1',
					  `histTime` int(11) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`histid`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;"
			);
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

			wp_enqueue_style( 'aryo_history_system_style', plugins_url( '/admin-ui/', __FILE__ ) . 'aryo-history-system.css' );

		}

		private function _add_log_attachment( $action, $attachment_id ) {
			$post = get_post( $attachment_id );

			$history = new AryoHistorySystemModel();

			$history->action         = $action;
			$history->object_type    = 'Attachment';
			$history->object_subtype = $post->post_type;
			$history->object_id      = $attachment_id;
			$history->object_name    = get_the_title( $post->ID );

			$history->insert();
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
			$history = new AryoHistorySystemModel();

			$history->action      = 'deactivated';
			$history->object_type = 'Plugin';
			$history->object_id   = 0;
			$history->object_name = $plugin_name;

			$history->insert();
		}

		public function hooks_activated_plugin( $plugin_name ) {
			$history = new AryoHistorySystemModel();

			$history->action      = 'activated';
			$history->object_type = 'Plugin';
			$history->object_id   = 0;
			$history->object_name = $plugin_name;

			$history->insert();
		}

		public function hooks_profile_update( $user_id ) {
			$history = new AryoHistorySystemModel();

			$user = get_user_by( 'id', $user_id );

			$history->action      = 'updated';
			$history->object_type = 'User';
			$history->object_id   = $user->ID;
			$history->object_name = $user->user_nicename;

			$history->insert();
		}

		public function hooks_user_register( $user_id ) {
			$history = new AryoHistorySystemModel();

			$user = get_user_by( 'id', $user_id );

			$history->action      = 'created';
			$history->object_type = 'User';
			$history->object_id   = $user->ID;
			$history->object_name = $user->user_nicename;

			$history->insert();
		}

		public function hooks_delete_user( $user_id ) {
			$history = new AryoHistorySystemModel();

			$user = get_user_by( 'id', $user_id );

			$history->action      = 'deleted';
			$history->object_type = 'User';
			$history->object_id   = $user->ID;
			$history->object_name = $user->user_nicename;

			$history->insert();
		}

		public function hooks_wrong_password() {
			$history = new AryoHistorySystemModel();

			$history->action      = 'wrong_password';
			$history->user_id     = 0;
			$history->object_id   = 0;
			$history->object_type = 'User';
			$history->object_name = $_REQUEST['log'];

			$history->insert();
		}

		public function hooks_wp_login( $user ) {
			$user = get_user_by( 'login', $user );

			$history = new AryoHistorySystemModel();

			$history->action      = 'logged_in';
			$history->user_id     = $user->ID;
			$history->object_type = 'User';
			$history->object_id   = $user->ID;
			$history->object_name = $user->user_nicename;

			$history->insert();
		}

		public function hooks_wp_logout() {
			$user = wp_get_current_user();

			$history = new AryoHistorySystemModel();

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

			$history = new AryoHistorySystemModel();

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

			$history = new AryoHistorySystemModel();

			$history->action         = 'deleted';
			$history->user_id        = get_current_user_id();
			$history->object_type    = 'Post';
			$history->object_subtype = $post->post_type;
			$history->object_id      = $post->ID;
			$history->object_name    = get_the_title( $post->ID );

			$history->insert();
		}

		public function viewPartialHistory( $args = array(), AryoHistorySystemModel $history ) {
			//$history = new AryoHistorySystemModel();

			$rows = $history->getLastResult( $args );

			if ( ! $rows ) {
				echo '<p>No have any logs.</p>';

				return;
			}


			foreach ( $rows as $row ) {
				$user       = false;
				$userText   = 'by ';
				$objectName = $row->object_name;

				if ( ! empty( $row->user_id ) ) {
					$user = get_user_by( 'id', $row->user_id );
				}

				if ( $user ) {
					$userText .= '<a href="user-edit.php?user_id=' . $user->ID . '">' . $user->user_login . '</a>';
				}
				else {
					$userText .= 'Guest';
				}

				$userText .= ' (' . $row->histIP . ')';

				if ( $row->object_type === 'Post' ) {
					$objectName = '<a href="post.php?post=' . $row->object_id . '&action=edit">' . $row->object_name . '</a>';
				}


				?>
				<div id="history-system-item-<?php echo $row->histid; ?>" class="row">
					<div class="type"><?php echo $row->object_type; ?><?php echo ( ! empty( $row->object_subtype ) ) ? ' (' . $row->object_subtype . ')' : ''; ?></div>
					<div class="name"><?php echo $objectName; ?></div>
					<div class="action"> was <?php echo $row->action; ?></div>
					<div class="dateaction"><?php echo human_time_diff( $row->histTime, current_time( 'timestamp' ) ); ?></div>
					<div class="dateshow"><?php echo date( 'd/m/Y H:i', $row->histTime ); ?></div>
					<div class="user"><?php echo $userText; ?></div>
				</div>
			<?php
			}
		}

		public function create_admin_menu() {
			add_dashboard_page( 'History System', 'History System', 'edit_pages', 'history_system_page', array( &$this, 'history_system_page_func' ) );
		}

		public function history_system_page_func() {
			$history = new AryoHistorySystemModel();
			$args    = array();

			if ( ! empty( $_GET['typeshow'] ) ) {
				$args['byObjectType'] = $_GET['typeshow'];
			}

			if ( isset( $_GET['usershow'] ) ) {
				$args['byUserID'] = $_GET['usershow'];
			}

			?>
			<div class="wrap">
				<h2>History System:</h2>

				<div class="aryo-history-system-types">
					Modules: <?php echo $history->getAllObjectTypes(); ?>
				</div>

				<div class="aryo-history-system-users">
					Users: <?php echo $history->getAllUsers(); ?>
				</div>

				<hr />

				<div id="aryo-history-system-wrap">
					<?php $this->viewPartialHistory( $args, $history ); ?>
				</div>

				<div id="aryo-history-paginate-wrap">
					<?php $history->thePaginate(); ?>
				</div>
			</div>

		<?php
		}

		public function __construct() {
			include( 'class/history-model.php' );

			// install
			add_action( 'activate_' . plugin_basename( __FILE__ ), array( &$this, 'activated' ) );

			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );

			add_action( 'admin_menu', array( &$this, 'create_admin_menu' ) );
		}

	} // end Aryo_WordPress_History_System class

	new Aryo_WordPress_History_System();
}
