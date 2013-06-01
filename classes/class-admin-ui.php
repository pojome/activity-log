<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Admin_Ui {

	public function create_admin_menu() {
		add_dashboard_page( __( 'Activity Log', 'aryo-aal' ), __( 'Activity Log', 'aryo-aal' ), 'edit_pages', 'activity_log_page', array( &$this, 'activity_log_page_func' ) );
	}

	public function activity_log_page_func() {
		$activity_table = new AAL_Activity_Log_List_Table();
		$activity_table->prepare_items();
		
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Activity Log', 'aryo-aal' ); ?></h2>

			<form id="activity-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $activity_table->display(); ?>
			</form>
		</div>

		<?php /* @todo move to a separate file */ ?>
		<style>

			.aal-pt {
				color: white;
				padding: 1px 4px;
				margin: 0 5px;
				font-size: 1em;
				border-radius: 3px;
				background: gray;
				font-family: inherit;
			}

		</style>
		<?php
	}
	
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ) );
	}
}
