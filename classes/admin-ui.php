<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HT_Admin_Ui {
	
	public function admin_init() {
		//wp_enqueue_style( 'history-timeline', plugins_url( '/admin-ui/', HISTORY_TIMELINE_BASE ) . 'history-timeline.css' );
	}

	public function create_admin_menu() {
		add_dashboard_page( 'History Timeline', 'History Timeline', 'edit_pages', 'history_timeline_page', array( &$this, 'history_timeline_page_func' ) );
	}

	public function history_timeline_page_func() {
		$history_table = new HT_History_List_Table();
		$history_table->prepare_items();
		
		?>
		<div class="wrap">
			<h2>History Timeline:</h2>

			<form id="history-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $history_table->display(); ?>
			</form>
		</div>

	<?php
	}
	
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ) );
	}
}