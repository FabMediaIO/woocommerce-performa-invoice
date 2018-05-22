<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://faisalawan.me/
 * @since      1.0.0
 *
 * @package    Woocommerce_Performa_Invoice
 * @subpackage Woocommerce_Performa_Invoice/includes
 */

class Woocommerce_Performa_Invoice_i18n {

	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-performa-invoice',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
