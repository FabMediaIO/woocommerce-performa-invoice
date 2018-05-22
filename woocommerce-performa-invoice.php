<?php
/**
 *
 * @link              http://faisalawan.me/
 * @since             1.0.0
 * @package           Woocommerce_Performa_Invoice
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Performa Invoice
 * Plugin URI:        http://faisalawan.me/projects/woocommerce-performa-invoice
 * Description:       Woocommerce Performa Invoice.
 * Version:           1.0.0
 * Author:            Faisal Awan
 * Author URI:        http://faisalawan.me/
 * Text Domain:       woocommerce-performa-invoice
 * Domain Path:       /languages
 */

define( 'WOE_VERSION', '1.1.0' );
define( 'WOE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOE_URL', plugin_dir_url( __FILE__ ) );

$files_path = wp_upload_dir();
$basedir_path = $files_path['basedir'];
$files_path = $basedir_path.DIRECTORY_SEPARATOR."woocommerce-order-reports".DIRECTORY_SEPARATOR;
define( 'WOE_REPORTS_PATH', $files_path );


function activate_Woocommerce_Performa_Invoice() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-performa-invoice-activator.php';
        new Woocommerce_Performa_Invoice_Activator();
}

function deactivate_Woocommerce_Performa_Invoice() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-performa-invoice-deactivator.php';
        Woocommerce_Performa_Invoice_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Woocommerce_Performa_Invoice' );
register_deactivation_hook( __FILE__, 'deactivate_Woocommerce_Performa_Invoice' );

require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-performa-invoice.php';

function run_Woocommerce_Performa_Invoice() {

        $plugin = new Woocommerce_Performa_Invoice();
        $plugin->run();

}
run_Woocommerce_Performa_Invoice();