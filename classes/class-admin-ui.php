<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Admin_Ui {

	public function create_admin_menu() {
		add_dashboard_page( __( 'Activity Log', AAL_TEXT_DOMAIN ), __( 'Activity Log', AAL_TEXT_DOMAIN ), 'edit_pages', 'activity_log_page', array( &$this, 'activity_log_page_func' ) );
	}

	public function activity_log_page_func() {
		$activity_table = new AAL_Activity_Log_List_Table();
		$activity_table->prepare_items();
		
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Activity Log', AAL_TEXT_DOMAIN ); ?></h2>

			<form id="activity-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $activity_table->display(); ?>
			</form>
		</div>

	<?php
	}
	
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ) );
	}
}