<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Settings {

	private $hook;
	private $slug;
	
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'action_admin_menu' ), 30 );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_footer', array( &$this, 'admin_footer' ) );
		add_filter( 'plugin_action_links_' . ACTIVITY_LOG_BASE, array( &$this, 'plugin_action_links' ) );

		add_action( 'wp_ajax_aal_reset_items', array( &$this, 'ajax_aal_reset_items' ) );
	}
	
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://github.com/KingYes/wordpress-aryo-activity-log', __( 'GitHub', 'aryo-aal' ) );
		array_unshift( $links, $settings_link );
		
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=activity-log-settings' ), __( 'Settings', 'aryo-aal' ) );
		array_unshift( $links, $settings_link );
		
		return $links;
	}

	/**
	 * Register the settings page
	 *
	 * @since 1.0
	 */
	public function action_admin_menu() {
		$this->hook = add_submenu_page(
			'activity_log_page',
			__( 'Activity Log Settings', 'aryo-aal' ), 	// <title> tag
			__( 'Settings', 'aryo-aal' ), 			// menu label
			'manage_options', 								// required cap to view this page
			$this->slug = 'activity-log-settings', 			// page slug
			array( &$this, 'display_settings_page' )			// callback
		);
		// this callback will initialize the settings for AAL
		// add_action( "load-$this->hook", array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		// If no options exist, create them.
		if ( ! get_option( $this->slug ) ) {
			update_option( $this->slug, apply_filters( 'aal_default_options', array(
				'logs_lifespan' => '30',
			) ) );
		}

		// First, we register a section. This is necessary since all future options must belong to a 
		add_settings_section(
			'general_settings_section',			// ID used to identify this section and with which to register options
			__( 'Display Options', 'aryo-aal' ),	// Title to be displayed on the administration page
			array( 'AAL_Settings_Fields', 'description' ),	// Callback used to render the description of the section
			$this->slug		// Page on which to add this section of options
		);

		add_settings_field(
			'logs_lifespan',
			__( 'Keep logs for', 'aryo-aal' ),
			array( 'AAL_Settings_Fields', 'number_field' ),
			$this->slug,
			'general_settings_section',
			array(
				'id'      => 'logs_lifespan',
				'page'    => $this->slug,
				'classes' => array( 'small-text' ),
				'type'    => 'number',
				'sub_desc'    => __( 'days.', 'aryo-aal' ),
				'desc'    => __( 'Maximum number of days to keep activity log. Leave blank to keep activity log forever (not recommended).', 'aryo-aal' ),
			)
		);
		
		if ( apply_filters( 'aal_allow_option_erase_logs', true ) ) {
			add_settings_field(
				'raw_delete_log_activities',
				__( 'Delete Log Activities', 'aryo-aal' ),
				array( 'AAL_Settings_Fields', 'raw_html' ),
				$this->slug,
				'general_settings_section',
				array(
					'html' => sprintf( __( '<a href="%s" id="%s">Reset Database</a>', 'aryo-aal' ), add_query_arg( array(
							'action' => 'aal_reset_items',
							'_nonce' => wp_create_nonce( 'aal_reset_items' ),
						), admin_url( 'admin-ajax.php' ) ), 'aal-delete-log-activities' ),
					'desc' => __( 'Warning: Clicking this will delete all activities from the database.', 'aryo-aal' ),
				)
			);
		}
		
		register_setting( 'aal-options', $this->slug );
	}

	public function display_settings_page() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">
		
			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e( 'Activity Log Settings', 'aryo-aal' ); ?></h2>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'aal-options' );
				do_settings_sections( $this->slug );

				submit_button();
				?>
			</form>
			
		</div><!-- /.wrap -->
		<?php
	}
	
	public function admin_notices() {
		switch ( filter_input( INPUT_GET, 'message' ) ) {
			case 'data_erased':
				printf( '<div class="updated"><p>%s</p></div>', __( 'All activities have been successfully deleted.', 'aryo-aal' ) );
				break;
		}
	}
	
	public function admin_footer() {
		// TODO: move to a separate file.
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				$( '#aal-delete-log-activities' ).on( 'click', function( e ) {
					if ( ! confirm( '<?php echo __( 'Are you sure you want to do this action?', 'aryo-aal' ); ?>' ) ) {
						e.preventDefault();
					}
				} );
			} );
		</script>
		<?php
	}
	
	public function ajax_aal_reset_items() {
		if ( ! check_ajax_referer( 'aal_reset_items', '_nonce', false ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.', 'aryo-aal' );
		}
		
		AAL_Main::instance()->api->erase_all_items();
		
		wp_redirect( add_query_arg( array(
				'page' => 'activity-log-settings',
				'message' => 'data_erased',
		), admin_url( 'admin.php' ) ) );
		die();
	}

	public function get_option( $key = '' ) {
		$settings = get_option( 'activity-log-settings' );
		return ! empty( $settings[ $key ] ) ? $settings[ $key ] : false;
	}
	
}

// TODO: Need rewrite this class to useful tool.
final class AAL_Settings_Fields {

	public static function description() {
		?>
		<p><?php _e( 'These are some basic settings for Activity Log.', 'aryo-aal' ); ?></p>
		<?php
	}
	
	public static function raw_html( $args ) {
		if ( empty( $args['html'] ) )
			return;
		
		echo $args['html'];
		if ( ! empty( $args['desc'] ) ) : ?>
			<p class="description"><?php echo $args['desc']; ?></p>
		<?php endif;
	}
	
	public static function text_field( $args ) {
		$args = wp_parse_args( $args, array(
			'classes' => array(),
		) );
		if ( empty( $args['id'] ) || empty( $args['page'] ) )
			return;
		
		?>
		<input type="text" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php printf( '%s[%s]', esc_attr( $args['page'] ), esc_attr( $args['id'] ) ); ?>" value="<?php echo AAL_Main::instance()->settings->get_option( $args['id'] ); ?>" class="<?php echo implode( ' ', $args['classes'] ); ?>" />
		<?php
	}
	
	public static function number_field( $args ) {
		$args = wp_parse_args( $args, array(
			'classes' => array(),
			'min' => '1',
			'step' => '1',
			'desc' => '',
		) );
		if ( empty( $args['id'] ) || empty( $args['page'] ) )
			return;

		?>
		<input type="number" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php printf( '%s[%s]', esc_attr( $args['page'] ), esc_attr( $args['id'] ) ); ?>" value="<?php echo AAL_Main::instance()->settings->get_option( $args['id'] ); ?>" class="<?php echo implode( ' ', $args['classes'] ); ?>" min="<?php echo $args['min']; ?>" step="<?php echo $args['step']; ?>" />
		<?php if ( ! empty( $args['sub_desc'] ) ) echo $args['sub_desc']; ?>
		<?php if ( ! empty( $args['desc'] ) ) : ?>
			<p class="description"><?php echo $args['desc']; ?></p>
		<?php endif;
	}

	public static function select_field( $args ) {
		extract( $args, EXTR_SKIP );

		if ( empty( $options ) || empty( $id ) || empty( $page ) )
			return;
		
		?>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php printf( '%s[%s]', esc_attr( $page ), esc_attr( $id ) ); ?>">
			<?php foreach ( $options as $name => $label ) : ?>
			<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $name, (string) AAL_Main::instance()->settings->get_option( $id ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

}