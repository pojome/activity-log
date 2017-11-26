<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Exporter_csv {
    /**
	 * Exporter name
	 *
	 * @var string
	 */
	public $name = 'CSV';

	/**
	 * Exporter ID
	 *
	 * @var string
	 */
	public $id = 'csv';

	/**
	 * Writes CSV data for download
	 *
	 * @param array $data Array of data to output.
	 * @param array $columns Column names included in data set.
	 * @return void
	 */
	public function write( $data, $columns ) {
		$is_test_mode_off = ! defined( 'AAL_TESTMODE' ) || ( defined( 'AAL_TESTMODE' ) && ! AAL_TESTMODE );
		
		if ( $is_test_mode_off ) {
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename="activity-log-export.csv"' );
		}

		$output = join( ',', array_values( $columns ) ) . "\n";
		foreach ( $data as $row ) {
			$output .= join( ',', $row ) . "\n";
		}

		echo $output; // @codingStandardsIgnoreLine text-only output

		if ( $is_test_mode_off ) {
			exit;
		}
	}
}
