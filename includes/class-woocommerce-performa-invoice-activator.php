<?php

/**
 * Fired during plugin activation
 *
 * @link       http://faisalawan.me/
 * @since      1.0.0
 *
 * @package    Woocommerce_Performa_Invoice
 * @subpackage Woocommerce_Performa_Invoice/includes
 */

class Woocommerce_Performa_Invoice_Activator {

	public function __construct() {

		// Start working from here;

		$files_path = wp_upload_dir();
		$files_path = $files_path['basedir'];
		$files_path = $files_path.DIRECTORY_SEPARATOR."woocommerce-order-reports";
		//$prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
		$src = WOE_PATH.DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."orignal-files";

		$this->recurse_copy($src, $files_path);

		$options = get_option( 'wox_option_data' );

		if(!$options){
			$wox_option_data = [
				'wox_word_template' => 'default.docx',
				'attach_invoice_in_admin_email' => true
			];
    		update_option( 'wox_option_data', $wox_option_data );
		}

		

	}

	public function recurse_copy($src,$dst) { 
	    $dir = opendir($src); 
	    @mkdir($dst); 
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                $this->recurse_copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	} 

}
