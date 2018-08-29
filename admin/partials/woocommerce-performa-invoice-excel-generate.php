<?php

if(!$order_id) die("Order Not Exist");

$woe_upload_path = WOE_REPORTS_PATH;

$order = wc_get_order( $order_id );

if($order->get_formatted_shipping_address()){
    $get_formatted_billing_address = str_replace('<br/>', '<w:br />', $order->get_formatted_shipping_address());

} else {
    $get_formatted_billing_address = str_replace('<br/>', '<w:br />', $order->get_formatted_billing_address());
}

$order_items = $order->get_items();
$get_order_item_totals = $order->get_order_item_totals();
$symbol = get_woocommerce_currency_symbol($order->get_currency());

$additional_notes = $order->customer_note;

$cart_subtotal = $get_order_item_totals['cart_subtotal']['value'];
$payment_method = $get_order_item_totals['payment_method']['value'];
$order_total = $get_order_item_totals['order_total']['value'];

$cart_subtotal = strip_tags(str_replace($symbol, '', $cart_subtotal));
$payment_method = strip_tags(str_replace($symbol, '', $payment_method));
$order_total = strip_tags(str_replace($symbol, '', $order_total));

$wox_option_data = get_option( 'wox_option_data' );
$wox_word_template = $wox_option_data['wox_word_template'];
if($wox_word_template){
    $woe_template_file = $woe_upload_path."templates".DIRECTORY_SEPARATOR.$wox_word_template;
} else {
    $woe_template_file = $woe_upload_path."templates".DIRECTORY_SEPARATOR."default.docx";
}

$file_title = $wox_option_data['wox_file_title'];


$header_content = str_replace(array('<br/>', '<br />', '</br>'), '<w:br />', $header_content);
$thankyou_message = $wox_option_data['wox_thankyou_message'];

$keys = array(
    'title',
    'file_title',
    //'header_content',
    'description',
    'date',
    'order',
    'customeraddress',
    'customerphone',
    'customeremail',
    'customername',
    'subtotalamount',
    'paymentmethod',
    'totalamount',
    'additional_notes',
    'thankyou_message',
    'total_items'
);

$values = array(
    get_option( 'blogname' ),
    $file_title,
    //$header_content,
    get_option( 'blogdescription' ),
    wc_format_datetime( $order->get_date_created() ),
    $order->get_order_number(),
    $get_formatted_billing_address,
    $order->get_billing_phone(),
    $order->get_billing_email(),
    $order->get_billing_first_name()." ".$order->get_billing_last_name(),
    $cart_subtotal,
    $payment_method,
    $order_total,
    $additional_notes,
    $thankyou_message,
    $order->get_item_count()
);

$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($woe_template_file);

$templateProcessor->setValue($keys, $values);

$templateProcessor->cloneRow('productname', count($order_items));

$order_i = 1;
foreach ( $order_items as $item_id => $item ) {
    
    $product = $item->get_product();
    $product_id = $item->get_product_id();
    $product_title = $product->get_title();
    $get_sku = $product->get_sku();
    

    $get_quantity = $item->get_quantity();
    $single_price = wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency() ) );
    $single_price = strip_tags(str_replace($symbol, '', $single_price));
    $subtotal = $order->get_formatted_line_subtotal( $item );
    $subtotal = strip_tags(str_replace($symbol, '', $subtotal));

    $variable = '';

    // Only for product variation
    if($product->is_type('variation')){

         // Get the variation attributes
        $variation_attributes = $product->get_variation_attributes();
        // Loop through each selected attributes
        foreach($variation_attributes as $attribute_taxonomy => $term_slug){
            $attribute_taxonomy = str_replace('attribute_', '', $attribute_taxonomy);
            $attribute_taxonomy = ucwords($attribute_taxonomy);
            $variable .= $attribute_taxonomy.' - '.$term_slug.'<w:br />';
        }
    }
    
    $templateProcessor->setValue("productname#$order_i", strip_tags($product_title));
    $templateProcessor->setValue("productsku#$order_i", $get_sku);
    $templateProcessor->setValue("product_var#$order_i", $variable);
    $templateProcessor->setValue("productunit#$order_i", strip_tags($single_price));
    $templateProcessor->setValue("productcount#$order_i", $get_quantity);
    $templateProcessor->setValue("productamount#$order_i", strip_tags($subtotal));

    //$templateProcessor->setValue("product_var#$order_i", $variable);
    

    $order_i++;
}


if(isset($test)){
    $order_id = wp_generate_password(13, false, false);
}

$order_file = $woe_upload_path."orders".DIRECTORY_SEPARATOR."order-$order_id.docx";

$templateProcessor->saveAs($order_file);
update_post_meta( $order_id, 'woe_file_url', "order-$order_id.docx" );


$wox_option_data = get_option( 'wox_option_data' );
$wox_dropbox_active = $wox_option_data['wox_dropbox_active'];
$wox_dropbox_foldername = $wox_option_data['wox_dropbox_foldername'];
if($wox_dropbox_foldername){
    $wox_dropbox_foldername = $wox_dropbox_foldername;
} else {
    $wox_dropbox_foldername = "Performa Invoice";
}
$wox_gd_accesstoken = $wox_option_data['wox_gd_accesstoken'];

if($wox_dropbox_active && $wox_gd_accesstoken){
    $dropbox = new Dropbox\Dropbox($wox_gd_accesstoken);
    $upload = $dropbox->files->upload("/".$wox_dropbox_foldername."/order-$order_id.docx", $order_file);
}