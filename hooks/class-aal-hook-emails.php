<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AAL_Hook_Emails extends AAL_Hook_Base {

	public function hooks_wp_mail_succeeded( $mail_data ) {
		if ( 'no' === AAL_Main::instance()->settings->get_option( 'logs_email' ) ) {
			return;
		}

		if ( empty( $mail_data['subject'] ) ) {
			$mail_data['subject'] = '[no subject]';
		}

		$caller_source = $this->get_caller_source();

		aal_insert_log( array(
			'action' => 'sent',
			'object_type' => 'Emails',
			'object_name' => $mail_data['subject'],
			'object_subtype' => $caller_source,
		) );
	}

	private function get_caller_source() {
		$caller_source = '';

		$backtrace_history = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		foreach ( $backtrace_history as $call ) {
			if ( ! empty( $call['function'] ) && 'wp_mail' === $call['function'] && ! empty( $call['file'] ) ) {
				$caller_source = $this->get_caller_source_by_file( $call['file'] );

				break;
			}
		}

		return $caller_source;
	}

	private function get_caller_source_by_file( $file ) {
		$methods = [
			'get_plugin_name_by_file',
			'get_mu_plugin_name_by_file',
			'get_theme_name_by_file',
			'get_wp_core_name_by_file',
		];

		foreach ( $methods as $method ) {
			$caller_source = $this->$method( $file );

			if ( ! empty( $caller_source ) ) {
				return $caller_source;
			}
		}

		return 'Unknown';
	}

	private function get_plugin_name_by_file( $file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( $file );

		return $plugin_data['Name'];
	}

	private function get_mu_plugin_name_by_file( $file ) {
		if ( ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$mu_plugins = get_mu_plugins();
		$plugin_data = $mu_plugins[ plugin_basename( $file ) ] ?? [];

		return $plugin_data['Name'] ?? '';
	}

	private function get_theme_name_by_file( $file ) {
		$theme_data = wp_get_theme();

		if ( $theme_data->exists() && $theme_data->get_stylesheet_directory() === dirname( $file ) ) {
			return $theme_data->get( 'Name' );
		}

		return '';
	}

	private function get_wp_core_name_by_file( $file ) {
		$core_directories = [
			ABSPATH . WPINC,
			ABSPATH . 'wp-admin',
		];

		foreach ( $core_directories as $core_directory ) {
			if ( 0 === strpos( $file, $core_directory ) ) {
				return 'Core';
			}
		}

		return '';
	}

	/**
	 * @param \WP_Error $error
	 *
	 * @return void
	 */
	public function hooks_wp_mail_failed( $error ) {
		if ( 'no' === AAL_Main::instance()->settings->get_option( 'logs_email' ) ) {
			return;
		}

		$caller_source = $this->get_caller_source();

		aal_insert_log( array(
			'action' => 'failed',
			'object_type' => 'Emails',
			'object_name' => $error->get_error_message(),
			'object_subtype' => $caller_source,
		) );
	}

	public function __construct() {
		add_action( 'wp_mail_succeeded', array( $this, 'hooks_wp_mail_succeeded' ) );

		add_action( 'wp_mail_failed', array( $this, 'hooks_wp_mail_failed' ) );

		parent::__construct();
	}
}
