<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Admin_Ui {

	/**
	 * @var AAL_Activity_Log_List_Table
	 */
	protected $_list_table = null;

	protected $_screens = array();

	public function create_admin_menu() {
		$menu_capability = current_user_can( 'view_all_aryo_activity_log' ) ? 'view_all_aryo_activity_log' : apply_filters( 'aal_menu_page_capability', 'edit_pages' );

		$this->_screens['main'] = add_menu_page( _x( 'Activity Log', 'Page and Menu Title', 'aryo-activity-log' ), _x( 'Activity Log', 'Page and Menu Title', 'aryo-activity-log' ), $menu_capability, 'activity-log-page', array( &$this, 'activity_log_page_func' ), '', '2.1' );

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
			.toplevel_page_activity-log-page .manage-column {
				width: auto;
			}
			.toplevel_page_activity-log-page .column-description {
				width: 20%;
			}
			#adminmenu #toplevel_page_activity-log-page div.wp-menu-image:before {
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
			.aal-table-promotion-row td {
				padding: 0;
			}
			.aal-table-promotion-inner {
				position: relative;
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 20px;
				background: white;
				border: 1px solid #4C43E5;
				border-inline-start-width: 3px;
			}
			.aal-promotion-cta {
				margin-inline-start: 5px;
				font-weight: bold;
				color: #4C43E5;
			}
			.aal-promotion-dismiss {
				display: flex;
				align-items: center;
				transition: all .1s ease-in-out;
				border: none;
				margin: 0;
				padding: 0;
				background: none;
				cursor: pointer;
				color: #7c7c7c;
			}
			.aal-promotion-dismiss::before {
				content: '\f335';
				display: block;
				font: normal 20px/20px dashicons;
				height: 20px;
				width: 20px;
				text-align: center;
			}
			.aal-promotion-dismiss:hover {
				color: #4C43E5;
			}
			@media (max-width: 767px) {
				.toplevel_page_activity-log-page .manage-column {
					width: auto;
				}
				.toplevel_page_activity-log-page .column-date,
				.toplevel_page_activity-log-page .column-author {
					display: table-cell;
					width: auto;
				}
				.toplevel_page_activity-log-page .column-ip,
				.toplevel_page_activity-log-page .column-description,
				.toplevel_page_activity-log-page .column-label {
					display: none;
				}
				.toplevel_page_activity-log-page .column-author .avatar {
					display: none;
				}
			}
		</style>
		<script>
			jQuery( document ).ready( ( $ ) => {
				const aalPromotionWrapSelector = 'tr.aal-table-promotion-row';
				$( '.aal-promotion-dismiss', aalPromotionWrapSelector ).on( 'click', function( event ) {
					event.preventDefault();

					const $promotionWrap = $( this ).closest( aalPromotionWrapSelector );

					$promotionWrap.hide();

					$.post( ajaxurl, {
						action: 'aal_promotion_dismiss',
						promotion_id: $promotionWrap.data( 'promotion-id' ),
						nonce: $promotionWrap.data( 'nonce' ),
					} );
				} );

				$( '.aal-promotion-cta', aalPromotionWrapSelector ).on( 'click', function( event ) {
					const $promotionWrap = $( this ).closest( aalPromotionWrapSelector );

					$.post( ajaxurl, {
						action: 'aal_promotion_campaign',
						promotion_id: $promotionWrap.data( 'promotion-id' ),
						nonce: $promotionWrap.data( 'nonce' ),
					} );
				} );
			} );
		</script>
		<?php
	}

	public function admin_header() {
		// TODO: move to a separate file.
		?><style>
			#adminmenu #toplevel_page_activity-log-page div.wp-menu-image:before {
				content: "\f321";
			}
		</style>
	<?php
	}

	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'create_admin_menu' ), 20 );
		add_action( 'admin_head', array( &$this, 'admin_header' ) );

		add_action( 'wp_ajax_aal_promotion_dismiss', [ $this, 'ajax_aal_promotion_dismiss' ] );
		add_action( 'wp_ajax_aal_promotion_campaign', [ $this, 'ajax_aal_promotion_campaign' ] );
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

	public function ajax_aal_promotion_dismiss() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aal_promotion' ) ) {
			wp_send_json_error();
		}

		if ( empty( $_POST['promotion_id'] ) ) {
			wp_send_json_error();
		}

		$promotion_id = sanitize_key( $_POST['promotion_id'] );

		update_user_meta( get_current_user_id(), "_aal_promotion_{$promotion_id}_notice_viewed", 'true'  );

		wp_send_json_success();
	}

	public function ajax_aal_promotion_campaign() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aal_promotion' ) ) {
			wp_send_json_error();
		}

		if ( empty( $_POST['promotion_id'] ) ) {
			wp_send_json_error();
		}

		if ( 'emails' === $_POST['promotion_id'] ) {
			$campaign_data = [
				'source' => 'sm-aal-install',
				'campaign' => 'sm-plg',
				'medium' => 'wp-dash',
			];

			set_transient( 'elementor_site_mailer_campaign', $campaign_data, 30 * DAY_IN_SECONDS );
		}

		if ( 'media' === $_POST['promotion_id'] ) {
			$campaign_data = [
				'source' => 'io-aal-install',
				'campaign' => 'io-plg',
				'medium' => 'wp-dash',
			];

			set_transient( 'elementor_image_optimization_campaign', $campaign_data, 30 * DAY_IN_SECONDS );
		}

		wp_send_json_success();
	}
}
