<?php

/**
 *
 * @link       http://faisalawan.me/
 * @since      1.0.0
 *
 * @package    Woocommerce_Performa_Invoice
 * @subpackage Woocommerce_Performa_Invoice/includes
 */

class Woocommerce_Performa_Invoice_General {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name.'-general', WOE_URL . 'public/css/woocommerce-performa-invoice-general.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name.'-general', WOE_URL . 'public/js/woocommerce-performa-invoice-general.js', array( 'jquery' ), $this->version, false );

	}

	function woocommerce_new_order($order_id) {
		require_once(WOE_PATH."/admin/partials/woocommerce-performa-invoice-excel-generate.php");
	}

	function attach_terms_conditions_pdf_to_email ( $attachments, $status , $order ) {
		$this->options = get_option( 'wox_option_data' );
		$allowed_statuses = array( 'new_order' );
		$invoice_in_admin_email = false;
		if (isset($this->options['attach_invoice_in_admin_email']) && $this->options['attach_invoice_in_admin_email']) {
			$invoice_in_admin_email = true;
		}

		if( isset( $status ) && in_array ( $status, $allowed_statuses ) && $invoice_in_admin_email ) {
		    $order_id = $order->get_id();
	        $your_pdf_path = WOE_REPORTS_PATH.'orders'.DIRECTORY_SEPARATOR."order-$order_id.docx"; 
	        $attachments[] = $your_pdf_path;
		} 
		return $attachments; 
		}
}
