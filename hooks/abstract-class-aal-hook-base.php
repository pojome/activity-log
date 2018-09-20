<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Empty class for now..
 *
 * Class AAL_Hook_Base
 */
abstract class AAL_Hook_Base {

	public $slug = 'activity-log-settings';

	public function __construct() {}

	protected function checkIps() {
		$options = get_option( $this->slug );

		if (isset($options['exclude_ips']) && !empty($options['exclude_ips'])) {
			$ips = $options['exclude_ips'];
			$filtered_ips = explode(',', $ips);
			if (!empty($filtered_ips)) {
				foreach ($filtered_ips as $ip) {
					$ip = trim($ip);
					if (filter_var($ip, FILTER_VALIDATE_IP) && $ip === $_SERVER['REMOTE_ADDR']) {
						return false;
					}
				}
			}
		}

		return true;
	}

}