jQuery.noConflict();

gbs_admin = {};

jQuery(document).ready(function($){
	gbs_admin.select2($);
	gbs_admin.ajax_settings($);
});
gbs_admin.ajax_settings = function($) {
	var form = $(".ajax_save");
	var form_dialog = $("#ajax_saving");
	var failed_save = false;

	form.change( function( e ) {

		// If the form is failing don't attempt again.
		if ( failed_save ) { return };

		// handle the payments form differently, only post if the payment selector is chosen.
		if ( $(this).hasClass('group-buying/payment') ) {
			if ( e.target.id == 'gb_payment_processor' ) {
				ajax_post_form();
				return;
			};
		}
		// handle the full page ajax pages differently 
		else if ( $(this).hasClass( 'full_page_ajax' ) ) {
			ajax_post_form();
			return;
		}
		else {
			ajax_post_options();
			return;
		};
	});

	// Use wp_ajax to update each option without a full page return.
	ajax_post_options = function() {
		show_dialog();
		$.post( ajaxurl, { action: 'gb_save_options', options: form.serialize() },
			function( data ) {
				form_dialog.html(data).delay(2000).fadeOut('slow');
			}
		);
	};

	// submit the form in the background and replace the DOM
	ajax_post_form = function() {
		show_dialog();
		$.ajax( {
			type: "POST",
			url: form.attr( 'action' ),
			data: form.serialize(),
			success: function( response ) {
				var new_form = $('<div />').html(response).find('form.ajax_save').html();
				if ( new_form.length > 0 ) {
					form_dialog.html('Saved').fadeOut('slow');
					form.html(new_form);
				}
				else {
					form_dialog.html('Auto save failed, use "Save Changes" button.').delay(2000).fadeOut('slow');
					failed_save = true;
				};
			}
		});
	};

	show_dialog = function() {
		form_dialog.html( $('#ajax_saving').data( 'message' ) );
		form_dialog
			.css('position', 'fixed')
			.css('left', '45%')
			.css('top', '45%')
			.show();
	};

};

gbs_admin.select2 = function($) {
	$('.select2').select2();
	$('#gb_signup_redirect, #gb_signup_not_found, #gb_nodeal_content, #gb_pp_page, #gb_tos_page, #gb_added_deal_id').select2({
		width: 'element'
	});
};

/**
 * ScrollTo with a modification to the top css
 */
(function(b){if(b.ScrollTo)window.console.warn("$.ScrollTo has already been defined...");else{b.ScrollTo={config:{duration:400,easing:"swing",callback:undefined,durationMode:"each"},configure:function(c){b.extend(b.ScrollTo.config,c||{});return this},scroll:function(c,d){var f=b.ScrollTo,a=c.pop(),e=a.$container,g=a.$target;a=b("<span/>").css({position:"absolute",top:"80px",left:"0px"});var h=e.css("position");e.css("position","relative");a.appendTo(e);var i=a.offset().top;g=g.offset().top-i;a.remove();
e.css("position",h);e.animate({scrollTop:g+"px"},d.duration,d.easing,function(j){if(c.length===0)typeof d.callback==="function"&&d.callback.apply(this,[j]);else f.scroll(c,d);return true});return true},fn:function(c){var d=b.ScrollTo,f=b(this);if(f.length===0)return this;var a=f.parent(),e=[];for(config=b.extend({},d.config,c);a.length===1&&!a.is("body")&&a.get(0)!==document;){c=a.get(0);if(a.css("overflow-y")!=="visible"&&c.scrollHeight!==c.clientHeight){e.push({$container:a,$target:f});f=a}a=a.parent()}e.push({$container:b(b.browser.msie?
"html":"body"),$target:f});if(config.durationMode==="all")config.duration/=e.length;d.scroll(e,config);return this},construct:function(c){var d=b.ScrollTo;b.fn.ScrollTo=d.fn;d.config=b.extend(d.config,c);return this}};b.ScrollTo.construct()}})(jQuery);