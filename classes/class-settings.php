<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Settings {

	private $hook;
	private $slug;
	
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
	}

	/**
	 * Register the settings page
	 *
	 * @since 1.0
	 */
	public function action_admin_menu() {
		$this->hook = add_options_page(
			__( 'Activity Log Settings', 'aryo-aal' ), 	// <title> tag
			__( 'Activity Log', 'aryo-aal' ), 			// menu label
			'manage_options', 								// required cap to view this page
			$this->slug = 'activity-log-settings', 			// page slug
			array( &$this, 'display_settings_page' )			// callback
		);

		// this callback will initialize the settings for AAL
		// add_action( "load-$this->hook", array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		// If no options exist, create them.
		if ( ! get_option( 'aal_options' ) ) {
			update_option( 'aal_options', apply_filters( 'aal_default_options', array(
				'logs_lifespan' => 'forever',
			) ) );
		}

		// First, we register a section. This is necessary since all future options must belong to a 
		add_settings_section(
			'general_settings_section',			// ID used to identify this section and with which to register options
			__( 'Display Options', 'sandbox' ),	// Title to be displayed on the administration page
			array( 'AAL_Settings_Fields', 'description' ),	// Callback used to render the description of the section
			$this->slug		// Page on which to add this section of options
		);

		add_settings_field(
			'logs_lifespan',
			__( 'Keep logs for', 'sandbox' ),
			array( 'AAL_Settings_Fields', 'select' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'logs_lifespan',
				'page' => $this->slug,
				'options' => array(
					'forever' => __( 'Forever', 'aryo-aal' ),
					'365' => __( 'A year', 'aryo-aal' ),
					'90' => __( '6 months', 'aryo-aal' ),
					'30' => __( 'A month', 'aryo-aal' ),
				),
			)
		);

		register_setting( $this->slug, 'aal_options' );
	}

	public function display_settings_page() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">
		
			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e( 'ARYO Activity Log Settings', 'aryo-aal' ); ?></h2>
			<?php settings_errors(); ?>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( $this->slug );
				do_settings_sections( $this->slug );

				submit_button();
				?>
			</form>
			
		</div><!-- /.wrap -->
		<?php
	}
	
}

final class AAL_Settings_Fields {

	public static function description() {
		?>
		<p><?php _e( 'These are some basic settings for Activity Log.', 'aryo-aal' ); ?></p>
		<?php
	}

	public static function select( $args ) {
		extract( $args, EXTR_SKIP );

		if ( empty( $options ) || empty( $id ) || empty( $page ) )
			return;
		
		?>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php printf( '%s[%s]', esc_attr( $page ), esc_attr( $id ) ); ?>">
			<?php foreach ( $options as $name => $label ) : ?>
			<option name="<?php echo esc_attr( $name ); ?>" <?php selected( $name, (string) self::get_option( $id ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function get_option( $key = '' ) {
		$settings = get_option( 'aal_options' );
		return ( ! empty( $settings[ $key ] ) ) ? $settings[ $key ] : false;
	}

}