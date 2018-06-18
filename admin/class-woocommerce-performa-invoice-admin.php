<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://faisalawan.me/
 * @since      1.0.0
 *
 * @package    Woocommerce_Performa_Invoice
 * @subpackage Woocommerce_Performa_Invoice/admin
 */

class Woocommerce_Performa_Invoice_Admin {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-performa-invoice-admin.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-performa-invoice-admin.js', array( 'jquery' ), $this->version, false );

		wp_register_script( $this->plugin_name.'-upload', plugin_dir_url( __FILE__ ) .'/js/woocommerce-performa-invoice-admin-upload.js', array('jquery','media-upload','thickbox') );
		if ( 'woocommerce_page_woocommerce-performa-invoice' == get_current_screen()->id ) {
	        wp_enqueue_script('jquery');
	 
	        wp_enqueue_script('thickbox');
	        wp_enqueue_style('thickbox');
	 
	        wp_enqueue_script('media-upload');
	        wp_enqueue_script($this->plugin_name.'-upload');
	 
	    }

	}

	public function check_wc() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			?>
			<div class="notice notice-error">
				<p><strong><?php _e( 'WooCommerce Performa Invoice', 'woocommerce-performa-invoice' ); ?></strong>: <?php _e( 'is enabled but not effective. It requires WooCommerce in order to work.', 'woocommerce-performa-invoice' ); ?></p>
		    </div>
		    <?php
		}

		if(isset($_GET['woe_file_url'])){
			?>
			<div class="notice notice-success">
				<p>Order File Generate Successfully</p>
		    </div>
		    <?php
		}
	}

	/**
	 * Adds a submenu page under a custom post type parent.
	 */
	function add_submenu_page() {
	    add_submenu_page(
	        'woocommerce',
	        __( 'Performa Invoice', 'woocommerce-performa-invoice' ),
	        __( 'Performa Invoice', 'woocommerce-performa-invoice' ),
	        'manage_options',
	        'woocommerce-performa-invoice',
	        [$this, 'Woocommerce_Performa_Invoice_adminpage_callback']
	    );
	}

	function woocommerce_new_order($order_id) {
		require_once("partials/woocommerce-performa-invoice-excel-generate.php");
	}

	public function show_Woocommerce_Performa_Invoice_column($columns) {

		$new_columns = (is_array($columns)) ? $columns : array();

		//remove column
		unset($new_columns['column_to_unset']);

		//add custom column
		$new_columns['Woocommerce_Performa_Invoice'] = __( 'Export', 'woocommerce' );

		return $new_columns;
	}

	public function Woocommerce_Performa_Invoice_column_callback($column) {

		global $post, $woocommerce, $the_order;

		switch ($column) {

		case 'Woocommerce_Performa_Invoice' :
			$file_url = $this->is_order_file_exist($post->ID);
			if($file_url){
				$output = "<a class='Woocommerce_Performa_Invoice_link' href='$file_url'>";
				$output .= '<span class="dashicons dashicons-media-document"></span>';
				$output .= "</a>";

				echo $output;
			}
		}
	}


    /**
     * Options page callback
     */
    public function Woocommerce_Performa_Invoice_adminpage_callback()
    {
        // Test Google Drive
        /*$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $client = new Google_Client();
        $client->setClientId('');
        $client->setClientSecret('');
        $client->setRedirectUri($url);
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $client->addScope(Google_Service_Drive::DRIVE);
        if (isset($_GET['code'])) {
            $_SESSION['accessToken'] = $client->authenticate($_GET['code']);
            header('location:'.$url);exit;
        } elseif (!isset($_SESSION['accessToken'])) {
            $client->useApplicationDefaultCredentials();
        }*/

        /*if(isset($_GET['do_create_gd_directory'])){
            $client->setAccessToken($_SESSION['accessToken']);
            $service = new Google_Service_Drive($client);

            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => 'Invoices '.rand(50, 52222),
                'mimeType' => 'application/vnd.google-apps.folder'));
            $file = $service->files->create($fileMetadata, array(
                'fields' => 'id'));

            print_r($file->id);
        }*/

        // Set class property
        $this->options = get_option( 'wox_option_data' );
        ?>
        <div class="wrap">
            <h1>Performa Invoice</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'wox_option_group' );
                do_settings_sections( 'wox-setting-admin' );
                submit_button();
            ?>
            </form>
            <?php require_once(WOE_PATH."/admin/partials/woocommerce-performa-invoice-admin-display.php"); ?>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {     
    	global $pagenow;   
        register_setting(
            'wox_option_group', // Option group
            'wox_option_data', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Performa Invoice Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'wox-setting-admin' // Page
        );  

        add_settings_field(
            'wox_word_template', // ID
            'Word Template', // Title 
            array( $this, 'wox_word_template' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        ); 

        add_settings_field(
            'wox_file_title', // ID
            'File Title', // Title 
            array( $this, 'wox_file_title' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        );  

        add_settings_field(
            'wox_email_active', // ID
            'Attach Invoice in Admin Email', // Title 
            array( $this, 'wox_attach_invoice_in_admin_email' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        ); 

        add_settings_field(
            'wox_dropbox_active', // ID
            'Upload On Dropbox', // Title 
            array( $this, 'wox_dropbox_active' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'wox_dropbox_foldername', // ID
            'Dropbox Folder Name', // Title 
            array( $this, 'wox_dropbox_foldername' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        ); 

        add_settings_field(
            'wox_gd_accesstoken', // ID
            'Generated access token', // Title 
            array( $this, 'wox_gd_accesstoken' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'wox_thankyou_message', // ID
            'Thank You Message', // Title 
            array( $this, 'wox_thankyou_message' ), // Callback
            'wox-setting-admin', // Page
            'setting_section_id' // Section           
        );

	    if(
	    	'post.php' === $pagenow &&
	    	isset($_GET['post']) &&
	    	'shop_order' === get_post_type($_GET['post']) &&
	    	isset($_GET['woe_do_generate_file'])
	    ){
	    	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	    	$actual_link = remove_query_arg('woe_do_generate_file', $actual_link);
    		

	    	$order_id = $_GET['post'];
	    	require_once("partials/woocommerce-performa-invoice-excel-generate.php");

	    	$file_name = "order-$order_id.docx";

	    	$woe_file_url = update_post_meta( $order_id, 'woe_file_url', $file_name );

	    	$actual_link = add_query_arg('woe_file_url', 'true', $actual_link);

	    	wp_redirect($actual_link);
	    	exit;
	    }
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['wox_word_template'] ) )
            $new_input['wox_word_template'] = $input['wox_word_template'];

        if( isset( $input['attach_invoice_in_admin_email'] ) )
            $new_input['attach_invoice_in_admin_email'] = $input['attach_invoice_in_admin_email'];

        if( isset( $input['wox_dropbox_active'] ) )
            $new_input['wox_dropbox_active'] = $input['wox_dropbox_active'];

        if( isset( $input['wox_dropbox_foldername'] ) )
            $new_input['wox_dropbox_foldername'] = $input['wox_dropbox_foldername'];

        if( isset( $input['wox_gd_accesstoken'] ) )
            $new_input['wox_gd_accesstoken'] = $input['wox_gd_accesstoken'];

        if( isset( $input['wox_thankyou_message'] ) )
            $new_input['wox_thankyou_message'] = $input['wox_thankyou_message'];

        if( isset( $input['wox_file_title'] ) )
            $new_input['wox_file_title'] = $input['wox_file_title'];
        

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function wox_word_template()
    {
    	//print_r($this->options);
	    ?>
	    <select class="wox_word_template_list" name="wox_option_data[wox_word_template]" id="wox_word_template" >
		    <?php foreach($this->getTemplates() as $file) { 
		    	$file_name = str_replace('.docx', '', $file);
		    	$file_name = str_replace(array('-'), ' ', $file_name);
		    	$file_name = ucfirst($file_name);
		    	?>
		    	<option value="<?php echo $file; ?>" <?php if($this->options['wox_word_template'] == $file){ echo "selected"; } ?>><?php echo $file_name; ?> - Performa Invoice</option>
		    	<?php } ?>
		</select>
	    <?php
    }

    public function wox_attach_invoice_in_admin_email()
    {
        $checked = " ";
        if (isset($this->options['attach_invoice_in_admin_email']) && $this->options['attach_invoice_in_admin_email']) {
            $checked = " checked='checked' ";
        }
        ?>
        <input type="checkbox" id="attach_invoice_in_admin_email" name="wox_option_data[attach_invoice_in_admin_email]" value="true" <?php echo $checked; ?> />
        <?php
    }

    public function wox_dropbox_active()
    {
        $checked = " ";
        if (isset($this->options['wox_dropbox_active']) && $this->options['wox_dropbox_active']) {
            $checked = " checked='checked' ";
        }
        ?>
        <input type="checkbox" id="wox_dropbox_active" name="wox_option_data[wox_dropbox_active]" value="true" <?php echo $checked; ?> />
        <?php
    }

    public function wox_dropbox_foldername()
    {
        $wox_dropbox_foldername = "Performa Invoice";
        if (isset($this->options['wox_dropbox_foldername']) && $this->options['wox_dropbox_foldername']) {
            $wox_dropbox_foldername = $this->options['wox_dropbox_foldername'];
        }
        ?>
        <input type="text" id="wox_dropbox_foldername" name="wox_option_data[wox_dropbox_foldername]" value="<?php echo $wox_dropbox_foldername; ?>"  />
        <?php
    }

    public function wox_gd_accesstoken()
    {
        $wox_gd_accesstoken = "";
        if (isset($this->options['wox_gd_accesstoken']) && $this->options['wox_gd_accesstoken']) {
            $wox_gd_accesstoken = $this->options['wox_gd_accesstoken'];
        }
        ?>
        <input type="text" id="wox_gd_accesstoken" name="wox_option_data[wox_gd_accesstoken]" value="<?php echo $wox_gd_accesstoken; ?>"  />
        <code><a href="https://www.dropbox.com/developers/apps/create" target="_blank">Create a new app on the Dropbox Platform?</a></code>
        <?php
    }

    public function wox_thankyou_message()
    {
        $wox_thankyou_message = "";
        if (isset($this->options['wox_thankyou_message']) && $this->options['wox_thankyou_message']) {
            $wox_thankyou_message = $this->options['wox_thankyou_message'];
        }
        ?>
        <input type="text" id="wox_thankyou_message" name="wox_option_data[wox_thankyou_message]" value="<?php echo $wox_thankyou_message; ?>"  />
        <?php
    }

    public function wox_file_title()
    {
        $wox_file_title = "";
        if (isset($this->options['wox_file_title']) && $this->options['wox_file_title']) {
            $wox_file_title = $this->options['wox_file_title'];
        }
        ?>
        <input type="text" id="wox_file_title" name="wox_option_data[wox_file_title]" value="<?php echo $wox_file_title; ?>"  />
        <?php
    }

    function getTemplates(){
	    $files=array();
	    if($dir=opendir(WOE_REPORTS_PATH."templates")){
	        while($file=readdir($dir)){
	            if($file!='.' && $file!='..' && $file!=basename(__FILE__)){
	            	if (strpos($file, '.docx') !== false) $files[]=$file;
	                
	            }   
	        }
	        closedir($dir);
	    }
	    natsort($files); //sort
	    return $files;
	}

    function woe_add_other_fields_for_packaging()
    {
        global $post;

        $file_url = $this->is_order_file_exist($post->ID);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    	$actual_link = add_query_arg('woe_do_generate_file', 'true', $actual_link);

        if($file_url){ ?>
        	<a href="<?php echo esc_url($file_url); ?>" download><span class="dashicons dashicons-media-text"></span> Download</a>
        	<br />
        	<br />
        	<a href="<?php echo $actual_link; ?>"><span class="dashicons dashicons-update"></span> Regenerate</a>
    	<?php } else {
    		
    		?>
    		<a href="<?php echo $actual_link; ?>"><span class="dashicons dashicons-media-text"></span> Generate Performa Invoice Order</a>
    	<?php } ?>
		<?php
    }

    function woe_add_meta_boxes()
    {
        add_meta_box( 'woe_file_url', __('Performa Invoice Order','woocommerce'), [$this, 'woe_add_other_fields_for_packaging'], 'shop_order', 'side', 'core' );
    }

    function is_order_file_exist($order_id) {
    	$meta_field_data = get_post_meta( $order_id, 'woe_file_url', true ) ? get_post_meta( $order_id, 'woe_file_url', true ) : '';

    	if($meta_field_data){
    		$files_path = wp_upload_dir();
    		$basedir_path = $files_path['basedir'];
    		$files_path = $basedir_path.DIRECTORY_SEPARATOR."woocommerce-order-reports".DIRECTORY_SEPARATOR."orders".DIRECTORY_SEPARATOR;

    		if(!file_exists($files_path.$meta_field_data)) {
    			delete_post_meta( $order_id, 'woe_file_url' );
    			return false;
    		} else {
    			$file_url = site_url( 'wp-content/uploads/woocommerce-order-reports/orders/'.$meta_field_data );
    			return $file_url;
    		}
    	} else {
    		return false;
    	}
    }
}

