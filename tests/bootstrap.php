<?php
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Create our own test case to prevent repeating ourself
require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function() {
		// Manually load plugin
		require dirname( dirname( __FILE__ ) ) . '/aryo-activity-log.php';

		// Call Activate plugin function
		AAL_Maintenance::activate( false );
	}
);

// Removes all sql tables on shutdown
// Do this action last
tests_add_filter( 'shutdown', 'drop_tables', 999999 );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';