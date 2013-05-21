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
				padding: 1px 5px;
				margin-left: 5px;
				font-size: 0.8em;
				border-radius: 3px;
				background: #1e5799;
				font-family: Consolas,Monaco,monospace;
				background: -moz-linear-gradient(top,  #1e5799 0%, #2989d8 50%, #207cca 51%, #7db9e8 100%);
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#1e5799), color-stop(50%,#2989d8), color-stop(51%,#207cca), color-stop(100%,#7db9e8));
				background: -webkit-linear-gradient(top,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%);
				background: -o-linear-gradient(top,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%);
				background: -ms-linear-gradient(top,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%);
				background: linear-gradient(to bottom,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=0 );
			}

		</style>
		<?php
	}
	
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ) );
	}
}