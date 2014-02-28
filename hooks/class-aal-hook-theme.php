<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Hook_Theme extends AAL_Hook_Base {

	public function hooks_theme_modify( $location, $status ) {
		if ( false !== strpos( $location, 'theme-editor.php?file=' ) ) {
			if ( ! empty( $_POST ) && 'update' === $_POST['action'] ) {
				$aal_args = array(
					'action'         => 'file_updated',
					'object_type'    => 'Theme',
					'object_subtype' => 'theme_unknown',
					'object_id'      => 0,
					'object_name'    => 'file_unknown',
				);

				if ( ! empty( $_POST['file'] ) )
					$aal_args['object_name'] = $_POST['file'];

				if ( ! empty( $_POST['theme'] ) )
					$aal_args['object_subtype'] = $_POST['theme'];

				aal_insert_log( $aal_args );
			}
		}

		// We are need return the instance, for complete the filter.
		return $location;
	}

	public function hooks_switch_theme( $new_name, WP_Theme $new_theme ) {
		aal_insert_log( array(
			'action'         => 'activated',
			'object_type'    => 'Theme',
			'object_subtype' => $new_theme->get_stylesheet(),
			'object_id'      => 0,
			'object_name'    => $new_name,
		) );
	}

	public function hooks_theme_customizer_modified( WP_Customize_Manager $obj ) {
		$aal_args = array(
			'action'         => 'updated',
			'object_type'    => 'Theme',
			'object_subtype' => $obj->theme()->display( 'Name' ),
			'object_id'      => 0,
			'object_name'    => 'Theme Customizer',
		);

		if ( 'customize_preview_init' === current_filter() )
			$aal_args['action'] = 'accessed';

		aal_insert_log( $aal_args );
	}

	public function __construct() {
		add_filter( 'wp_redirect', array( &$this, 'hooks_theme_modify' ), 10, 2 );
		add_action( 'switch_theme', array( &$this, 'hooks_switch_theme' ), 10, 2 );

		// Theme customizer
		add_action( 'customize_save', array( &$this, 'hooks_theme_customizer_modified' ) );
		//add_action( 'customize_preview_init', array( &$this, 'hooks_theme_customizer_modified' ) );

		parent::__construct();
	}

}