<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Admin_Ui {

	/**
	 * @var AAL_Activity_Log_List_Table
	 */
	protected $_list_table = null;
	
	protected $_screens = array();

	public function create_admin_menu() {
		$menu_capability = current_user_can( 'view_all_aryo_activity_log' ) ? 'view_all_aryo_activity_log' : 'edit_pages';
		
		$this->_screens['main'] = add_menu_page( _x( 'Activity Log', 'Page and Menu Title', 'aryo-activity-log' ), _x( 'Activity Log', 'Page and Menu Title', 'aryo-activity-log' ), $menu_capability, 'activity_log_page', array( &$this, 'activity_log_page_func' ), '', '2.1' );
		
		// Just make sure we are create instance.
		add_action( 'load-' . $this->_screens['main'], array( &$this, 'get_list_table' ) );
	}

	public function activity_log_page_func() {
		$this->get_list_table()->prepare_items();
		?>
		<div class="wrap">
			<h1 class="aal-page-title"><?php _ex( 'Activity Log', 'Page and Menu Title', 'aryo-activity-log' ); ?></h1>

			<form id="activity-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
				<?php $this->get_list_table()->display(); ?>
			</form>
		</div>
		
		<?php // TODO: move to a separate file. ?>
		<style>
			#record-actions-submit {
				margin-top: 10px;
			}
			.aal-pt {
				color: #ffffff;
				padding: 1px 4px;
				margin: 0 5px;
				font-size: 1em;
				border-radius: 3px;
				background: #808080;
				font-family: inherit;
			}
			.toplevel_page_activity_log_page .manage-column {
				width: auto;
			}
			.toplevel_page_activity_log_page .column-description {
				width: 20%;
			}
			#adminmenu #toplevel_page_activity_log_page div.wp-menu-image:before {
				content: "\f321";
			}
			h1.aal-page-title:before {
				content: "\f321";
				font: 400 25px/1 dashicons !important;
				speak: none; /* accessibility thing. do not read the contents of this icon */
				color: #030303;
				display: inline-block;
				padding-inline-end: .2em;
				vertical-align: -18%;
			}
			#aal-reset-filter {
				display: inline-block;
				margin-inline-start: 5px;
				line-height: 30px;
				text-decoration: none;
			}
			#aal-reset-filter .dashicons {
				font-size: 15px;
				line-height: 30px;
				text-decoration: none;
			}
			@media (max-width: 767px) {
				.toplevel_page_activity_log_page .manage-column {
					width: auto;
				}
				.toplevel_page_activity_log_page .column-date,
				.toplevel_page_activity_log_page .column-author {
					display: table-cell;
					width: auto;
				}
				.toplevel_page_activity_log_page .column-ip,
				.toplevel_page_activity_log_page .column-description,
				.toplevel_page_activity_log_page .column-label {
					display: none;
				}
				.toplevel_page_activity_log_page .column-author .avatar {
					display: none;
				}
			}
		</style>
		<?php
	}
	
	public function admin_header() {
		// TODO: move to a separate file.
		?><style>
			#adminmenu #toplevel_page_activity_log_page div.wp-menu-image:before {
				content: "\f321";
			}
		</style>
	<?php
	}
	
	public function ajax_aal_install_elementor_set_admin_notice_viewed() {
		update_user_meta( get_current_user_id(), '_aal_elementor_install_notice', 'true' );
	}

	public function admin_notices() {
		if ( ! current_user_can( 'install_plugins' ) || $this->_is_elementor_installed() )
			return;
		

		if ( 'true' === get_user_meta( get_current_user_id(), '_aal_elementor_install_notice', true ) )
			return;
		
		if ( ! in_array( get_current_screen()->id, array( 'toplevel_page_activity_log_page', 'dashboard', 'plugins', 'plugins-network' ) ) ) {
			return;
		}

		add_action( 'admin_footer', array( &$this, 'print_js' ) );

		$install_url = self_admin_url( 'plugin-install.php?tab=search&s=elementor' );
		?>
		<style>
			body h1.aal-page-title:before {
				content: "\f321";
				font: 400 25px/1 dashicons !important;
				speak: none; /* accessibility thing. do not read the contents of this icon */
				color: #030303;
				display: inline-block;
				padding-inline-end: .2em;
				vertical-align: -18%;
			}
			#aal-reset-filter {
				display: inline-block;
				margin-inline-start: 5px;
				line-height: 30px;
				text-decoration: none;
			}
			#aal-reset-filter .dashicons {
				font-size: 15px;
				line-height: 30px;
				text-decoration: none;
			}
			.notice.aal-notice {
				border-left-color: #92003B !important;
				padding: 20px;
			}
			.rtl .notice.aal-notice {
				border-right-color: #92003B !important;
			}
			.notice.aal-notice .aal-notice-inner {
				display: table;
				width: 100%;
			}
			.notice.aal-notice .aal-notice-inner .aal-notice-icon,
			.notice.aal-notice .aal-notice-inner .aal-notice-content,
			.notice.aal-notice .aal-notice-inner .aal-install-now {
				display: table-cell;
				vertical-align: middle;
			}
			.notice.aal-notice .aal-notice-icon {
				color: #92003B;
				font-size: 50px;
				width: 50px;
				height: 50px;
			}
			.notice.aal-notice .aal-notice-content {
				padding: 0 20px;
			}
			.notice.aal-notice p {
				padding: 0;
				margin: 0;
			}
			.notice.aal-notice h3 {
				margin: 0 0 5px;
			}
			.notice.aal-notice .aal-install-now {
				text-align: center;
			}
			.notice.aal-notice .aal-install-now .aal-install-button {
				background-color: #92003B;
				color: #fff;
				border-color: #92003B;
				box-shadow: 0 1px 0 #92003B;
				padding: 5px 30px;
				height: auto;
				line-height: 20px;
				text-transform: capitalize;
			}
			.notice.aal-notice .aal-install-now .aal-install-button i {
				padding-right: 5px;
			}
			.rtl .notice.aal-notice .aal-install-now .aal-install-button i {
				padding-right: 0;
				padding-left: 5px;
			}
			.notice.aal-notice .aal-install-now .aal-install-button:hover {
				background-color: #92003B;
			}
			.notice.aal-notice .aal-install-now .aal-install-button:active {
				box-shadow: inset 0 1px 0 #92003B;
				transform: translateY(1px);
			}
			@media (max-width: 767px) {
				.notice.aal-notice {
					padding: 10px;
				}
				.notice.aal-notice .aal-notice-inner {
					display: block;
				}
				.notice.aal-notice .aal-notice-inner .aal-notice-content {
					display: block;
					padding: 0;
				}
				.notice.aal-notice .aal-notice-inner .aal-notice-icon,
				.notice.aal-notice .aal-notice-inner .aal-install-now {
					display: none;
				}
			}
		</style>
		<div class="notice updated is-dismissible aal-notice aal-install-elementor">
			<div class="aal-notice-inner">
				<div class="aal-notice-icon">
					<svg width="50" height="50" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M14.0035 3.98515e-07C8.34084 -0.00134594 3.23499 3.40874 1.06704 8.64C-1.10092 13.8713 0.0960255 19.8934 4.09968 23.898C8.10333 27.9026 14.1252 29.1009 19.3569 26.9342C24.5887 24.7675 28 19.6625 28 13.9998C28 6.26922 21.7341 0.00183839 14.0035 3.98515e-07Z" fill="#92003B"/>
						<rect x="8.1687" y="8.16504" width="2.3333" height="11.6665" fill="white"/>
						<rect x="12.8352" y="17.498" width="6.9999" height="2.3333" fill="white"/>
						<rect x="12.8352" y="12.8315" width="6.9999" height="2.3333" fill="white"/>
						<rect x="12.8352" y="8.16504" width="6.9999" height="2.3333" fill="white"/>
					</svg>
				</div>
				
				<div class="aal-notice-content">
					<h3><?php _e( 'Do You Like Activity Log? You\'ll Love Elementor!', 'aryo-activity-log' ); ?></h3>
					<p><?php _e( 'Create high-end, pixel perfect websites at record speeds. Any theme, any page, any design. The most advanced frontend drag & drop page builder.', 'aryo-activity-log' ); ?>
						<a href="https://go.elementor.com/learn/" target="_blank"><?php _e( 'Learn more about Elementor', 'aryo-activity-log' ); ?></a>.</p>
				</div>

				<div class="aal-install-now">
					<a class="button aal-install-button" href="<?php echo $install_url; ?>"><i class="dashicons dashicons-download"></i><?php _e( 'Install Now For Free!', 'aryo-activity-log' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	public function print_js() {
		?>
		<script>jQuery( function( $ ) {
				$( 'div.notice.aal-install-elementor' ).on( 'click', 'button.notice-dismiss', function( event ) {
					event.preventDefault();

					$.post( ajaxurl, {
						action: 'aal_install_elementor_set_admin_notice_viewed'
					} );
				} );
			} );</script>
		<?php
	}
	
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ), 20 );
		add_action( 'admin_head', array( &$this, 'admin_header' ) );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'wp_ajax_aal_install_elementor_set_admin_notice_viewed', array( &$this, 'ajax_aal_install_elementor_set_admin_notice_viewed' ) );
	}
	
	private function _is_elementor_installed() {
		$file_path = 'elementor/elementor.php';
		$installed_plugins = get_plugins();

		return isset( $installed_plugins[ $file_path ] );
	}

	/**
	 * @return AAL_Activity_Log_List_Table
	 */
	public function get_list_table() {
		if ( is_null( $this->_list_table ) ) {
			$this->_list_table = new AAL_Activity_Log_List_Table( array( 'screen' => $this->_screens['main'] ) );
			do_action( 'aal_admin_page_load', $this->_list_table );
		}
		
		return $this->_list_table;
	}
}
