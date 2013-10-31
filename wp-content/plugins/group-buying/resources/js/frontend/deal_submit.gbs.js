jQuery.noConflict();

gbs_frontend_deal = {};

jQuery(document).ready(function($){
	gbs_frontend_deal.ready($);
});

gbs_frontend_deal.ready = function($) {

	/////////////////
	// Datepicker //
	/////////////////

	$('#deal_expiration').datetimepicker({minDate: 0});
	$('#gb_deal_exp').datetimepicker({minDate: 0});
	$('#gb_deal_start_date').datetimepicker({minDate: 0});
	$('#voucher_expiration_date').datepicker({minDate: 0});
	$('#gb_deal_voucher_expiration').datepicker({minDate: 0});

	///////////////////
	// Media upload //
	///////////////////
	
	// Uploading files
	var media_uploader;
	jQuery('.upload_image_button').live('click', function( event ){
		var button = jQuery( this );
		// If the media uploader already exists, reopen it.
		if ( media_uploader ) {
		  media_uploader.open();
		  return;
		}
		// Create the media uploader.
		media_uploader = wp.media.frames.media_uploader = wp.media({
			title: button.data( 'uploader-title' ),
			// Tell the modal to show only images.
			library: {
				type: 'image',
				query: false
			},
			button: {
				text: button.data( 'uploader-button-text' ),
			},
			multiple: button.data( 'uploader-allow-multiple' )
		});

		// Create a callback when the uploader is called
		media_uploader.on( 'select', function() {
			var	selection = media_uploader.state().get('selection'),
			 	input_name = button.data( 'input-name' ),
			 	bucket = $( '#' + input_name + '-thumbnails');

			 selection.map( function( attachment ) {
			 	attachment = attachment.toJSON();
			 	// console.log(attachment);
			 	bucket.append(function() {
			 		return '<img src="'+attachment.sizes.thumbnail.url+'" width="'+attachment.sizes.thumbnail.width+'" height="'+attachment.sizes.thumbnail.height+'" class="deal_submission_thumb thumbnail" /><input name="'+input_name+'[]" type="hidden" value="'+attachment.id+'" />'
			 	});
			 });
		});

		// Open the uploader
		media_uploader.open();
	  });
};