jQuery(document).ready(function($) {
    jQuery('#wox_word_template_button').click(function() {
        tb_show('Upload a Word Template', 'media-upload.php?referer=woocommerce-performa-invoice&type=file&TB_iframe=true', false);
        return false;
    });
});


window.send_to_editor = function(html) {
	html = jQuery.parseHTML( html );
	var image_url = jQuery(html[0]).attr('src')
    //var image_url = jQuery('img',html).attr('src');
    jQuery('#wox_word_template').val(image_url);

    console.log("Start");
	console.log(html);
	console.log("End");


	console.log("Start");
	console.log(image_url);
	console.log("End");

    tb_remove();
}