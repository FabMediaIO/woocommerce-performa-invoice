<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://faisalawan.me/
 * @since      1.1.0
 *
 * @package    Woocommerce_Performa_Invoice
 * @subpackage Woocommerce_Performa_Invoice/includes
 */

class Woocommerce_Performa_Invoice {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		if ( defined( 'WOE_VERSION' ) ) {
			$this->version = WOE_VERSION;
		} else {
			$this->version = '1.1.0';
		}
		$this->plugin_name = 'woocommerce-performa-invoice';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_general_hooks();
		$this->define_admin_hooks();
	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-performa-invoice-general.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-performa-invoice-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-performa-invoice-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-performa-invoice-admin.php';

		$this->loader = new Woocommerce_Performa_Invoice_Loader();

	}

	private function set_locale() {
		$plugin_i18n = new Woocommerce_Performa_Invoice_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_general_hooks() {
		$plugin_general = new Woocommerce_Performa_Invoice_General( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_general, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_general, 'enqueue_scripts' );

		$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_general, 'woocommerce_new_order', 10, 1 );

		$this->loader->add_filter( 'woocommerce_email_attachments', $plugin_general, 'attach_terms_conditions_pdf_to_email', 10, 3); 
	}

	private function define_admin_hooks() {
		$plugin_admin = new Woocommerce_Performa_Invoice_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action('admin_notices', $plugin_admin, 'check_wc' );
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_submenu_page', 1000 );
		$this->loader->add_action('admin_init', $plugin_admin, 'page_init', 100, 1);
		$this->loader->add_filter('manage_edit-shop_order_columns', $plugin_admin, 'show_Woocommerce_Performa_Invoice_column', 15);
		$this->loader->add_action('manage_shop_order_posts_custom_column', $plugin_admin, 'Woocommerce_Performa_Invoice_column_callback', 10, 2);
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'woe_add_meta_boxes' );
		
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
