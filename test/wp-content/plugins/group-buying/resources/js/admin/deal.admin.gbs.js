jQuery.noConflict();

gbs_admin_deal = {};

jQuery(document).ready(function($){
	gbs_admin_deal.ready($);
});

gbs_admin_deal.ready = function($) {

	//////////////
	// Dyn Cost //
	//////////////

	// Initialize a counter var
	inputCount = 0;
	// Now let's say you click the "add text box" button
	$('#add_dyn_cost').click(function(){
		var value_total = $('#dynamic_purchase_total').val();
		var value_cost = $('#dynamic_purchase_cost').val();
		var table = '<tr id="dyn_cost_'+inputCount+'"><td class="centered_text">'+value_total+'</td><td>'+gb_currency_symbol+'<input id="dyn_cost_' + inputCount + '" type="text" name="deal_dynamic_price['+value_total+']" value="'+value_cost+'" class="tiny-text" size="5"></td><td><a id="delete_dyn_cost_'+inputCount+'" class="delete-dyn-cost button hide-if-no-js" onclick="jQuery(this).parent().parent().remove();">Remove</a></td></tr>';
		if ( value_total.length != 0 & value_cost.length != 0 ) {
			$(' '+table+' ').appendTo('#dynamic_costs');
		};
		inputCount++;
	});
	$('.delete-dyn-cost').live('click', function(){
		$(this).parent().parent().remove();
	});

	/////////////////
	// Datepicker //
	/////////////////

	$('#deal_expiration').datetimepicker({minDate: 0});
	$('#gb_deal_exp').datetimepicker({minDate: 0});
	$('#gb_deal_start_date').datetimepicker({minDate: 0});
	$('#voucher_expiration_date').datepicker({minDate: 0});
	$('#gb_deal_voucher_expiration').datepicker({minDate: 0});
};