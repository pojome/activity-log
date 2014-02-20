<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Integration_WooCommerce {
	
	public function init() {
		if ( ! class_exists( 'Woocommerce' ) )
			return;
		
		add_filter( 'aal_whitelist_options', array( &$this, 'wc_aal_whitelist_options' ) );
	}
	
	public function wc_aal_whitelist_options( $whitelist_options ) {
		// I just add few options for base code, later I'll add more.
		// TODO: Use with any filter from WC for auto make this array.
		$wc_options = array(
			'woocommerce_currency',
			'woocommerce_enable_coupons',
			'woocommerce_weight_unit',
			'woocommerce_dimension_unit',
			'woocommerce_enable_sku',
			'woocommerce_default_gateway',
			'woocommerce_gateway_order',
			'woocommerce_calc_shipping',
			'woocommerce_default_shipping_method',
		);
		
		return array_unique( array_merge( $whitelist_options, $wc_options ) );
	}
	
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}
	
}
new AAL_Integration_WooCommerce();
