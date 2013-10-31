jQuery.noConflict();

gbs_frontend_checkout = {};

jQuery(document).ready(function($){
	gbs_frontend_checkout.ready($);
});

gbs_frontend_checkout.ready = function($) {

	//////////////
	// Checkout //
	//////////////

	/**
	 * Hide options if guest selection is checked on checkout
	 */
	var $guest_check = jQuery('#checkout_registration_form #gb_user_guest_purchase');
	$guest_check.on( 'change', function() {
		$('#checkout_login_register_wrap').slideToggle();
		$('#checkout_login_register_wrap').before( $('#checkout_registration_form [for="gb_user_guest_purchase"]') ); // Move the option out of the selection
	});

	/**
	 * copy billing to shipping
	 */
	var $copy_billing_option = $('#gb_shipping_copy_billing');
	var $shipping_option_cache = {}; // cache options
	$copy_billing_option.bind( 'change', function() {
		// Loop over all gb_shipping options, unknowingly what is actually set because of customizations.
		$('#gb-shipping [name^="gb_shipping_"]').each(function () {
			if ( $( $copy_billing_option ).is(':checked') ) {
				var $billing_name = this.name.replace('gb_shipping_', 'gb_billing_'); // Search for a matching field
				$shipping_option_cache[this.name] = $(this).val(); // Cache the original option so it can be used later.
				$( this ).val( $( '[name="' + $billing_name + '"]' ).val() ); // set the value
			}
			else {
				$( this ).val( $shipping_option_cache[this.name] );
			};
		});
	});
};