<?php
	
	// Create a "field" with jQuery AJAX bits
	ob_start();
	?>
		<script type="text/javascript">
			jQuery(document).ready( function($) {

				var $add_to_cart_class = '.select-checkout-charity';
				var $dropdowns = $($add_to_cart_class + ' .gb-charity-category-selections');

				// Create an array of the selected taxonomy term_ids
				var check_availability = function() {
					disable_submit();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						data: {
							action: 'gbs_ajax_query_charities',
							selections: $dropdowns.serialize() // serialize all of the selection dropdowns for quering
						},
						success: function(results) {
							var $charity_ids = results;
							$($add_to_cart_class + ' #ajax_gif').fadeOut();
							enable_submit($charity_ids);
							
							//Display no matches?
							var selectsize = $($add_to_cart_class + " select[name='gb_charity'] option").size();
							if ( selectsize > 1 ) {
								//has options
								$($add_to_cart_class + " #ajax_no_matches").addClass('cloak');
								$($add_to_cart_class + " select[name='gb_charity']").removeClass('cloak').fadeTo('slow', 1);
								$($add_to_cart_class + " input[type='submit']").removeAttr('disabled');
							} else{
								$($add_to_cart_class + " #ajax_no_matches").removeClass('cloak').fadeTo('slow', 1);
							}
						}
					});
				};
				var enable_submit = function( charity_ids ) {
					
					var $select_charities = $($add_to_cart_class + " select[name='gb_charity']");
					$select_charities.empty();
					$select_charities.append($("<option>", { value: '', html: '<?php echo gb__( '-- Select a Non Profit --' ); ?>' }));
					$(charity_ids).each(function(i, v){ 
						$select_charities.append($("<option>", { value: v.id, html: v.title }));
					});
					$($add_to_cart_class + " input[type='submit']").removeAttr('disabled');
				};
				var disable_submit = function() {
					// Disable and style the add to cart button right away
					$($add_to_cart_class + " input[type='submit']").attr('disabled','disabled'); 
					$($add_to_cart_class + " select[name='gb_charity']").addClass('cloak');
					$($add_to_cart_class + " #ajax_no_matches").addClass('cloak');
					$('.charity_thumb').fadeOut();
					$($add_to_cart_class + ' #ajax_gif').fadeIn();

				};
				// hide the selection
				//$($add_to_cart_class + " select[name='gb_charity']").hide();
				// check the availability whenever the dropdowns are changed
				$dropdowns.change(check_availability);
				// check on load in case the first selections do not have an available attribute
				//check_availability();
			});
		</script>
	<?php
	$fields[] = ob_get_clean();
	
	// Add the category selections
	$drop_down = wp_dropdown_categories( array(
				'taxonomy' => 'gb_location',
				'name' => 'gb_location',
				'class' => 'gb-charity-category-selections',
				'hide_empty' => FALSE,
				'echo' => 0,
				'hierarchical' => true,
				'show_option_none'   => ' -- Select one -- ',
			) );
	$fields[] = '<span class="category_charity_selection clearfix"><label for="gb_location">Filter by Location: </label>' . $drop_down . '</span>';

	// Add the category selections
	$drop_down = wp_dropdown_categories( array(
				'taxonomy' => 'gb_charity_type',
				'name' => 'gb_charity_type',
				'class' => 'gb-charity-category-selections',
				'hide_empty' => FALSE,
				'echo' => 0,
				'hierarchical' => true,
				'show_option_none'   => ' -- Select one -- ',
			) );
	$fields[] = '<span class="category_charity_selection clearfix"><label for="gb_charity_type">Filter by Type: </label>' . $drop_down . '</span>';

	
	?>

<div class="checkout_block left_form clearfix">

	<div class="paymentform_info">
		<h2 class="table_heading section_heading background_alt font_medium gb_ff"><?php self::_e( 'Select a Non Profit Organization' ); ?></h2>
	</div>
	<fieldset id="gb-charity">
		<table class="charity">
			<tbody>
				<tr>
					<td valign="top" style="vertical-align: top;"><label for="gb_charity"><?php printf(self::__("Non Profit Organization to receive %s donation:"), $donation_percentage."%"); ?></label></td>
					<td valign="top" class="select-checkout-charity">
                    <?php
					 //Category locations
					$locations = get_terms( array( gb_get_location_tax_slug() ), array( 'hide_empty'=>true, 'fields'=>'all' ) );
					$types = get_terms( array( 'gb_charity_type' ), array( 'hide_empty'=>true, 'fields'=>'all' ) );
					
					foreach ( $fields as $field ) {
						echo $field;	
					}
					/*
					 <select name="gb_billing_zone" id="gb_billing_zone">
							<optgroup label="">
									</optgroup>
							<optgroup label="United States">
								<option value="AL" selected="selected">Alabama</option>
								
							<optgroup label="UK">
											<option value="Avon">Avon</option>
											<option value="Bedfordshire">Bedfordshire</option>
											
											<option value="Worcestershire">Worcestershire</option>
									</optgroup>
					</select>
                    */
					?>
                    	<hr>
                   		<select name="gb_charity" id="gb_charity"/>

							<?php
								$selected = ( isset( $_POST[ 'gb_charity' ] ) ) ? $_POST[ 'gb_charity' ] : '' ;
								echo '<option value="">'.gb__( ' -- Select a Non Profit -- ' ).'</option>';
								foreach ( $charity_ids as $charity_id ) {
									$option = '<option value="'.$charity_id.'" '.selected( $selected, $charity_id ).'>'.get_the_title( $charity_id ).'</option>';
									print $option;
								}
								?>
						</select>
                        <div id="ajax_gif" class="cloak"><img src="<?php echo get_admin_url() ?>/images/wpspin_light.gif" valign="middle"> <em>Updating, please wait...</em></div>
                        <div id="ajax_no_matches" class="cloak"><strong>No matches</strong> for the selected filters. <br>Please try again.</div>
						<div class="charity_thumbs clearfix">
							<?php 
								foreach ( $charity_ids as $charity_id ) {
									echo '<span id="charity_thumb_' . $charity_id. '" class="charity_thumb cloak">' . get_the_post_thumbnail( $charity_id, array( 120, 120 ) ) . '</span>';
								}
							 ?>
						</div><!--  .charity_thumbs -->
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>

<script type="text/javascript">
jQuery(document).ready( function($) {
	
	var show_charity_thumb = function(e) {
		var $select = $(this);
    	var $value = $select.val();
    	var $thumbs_class = $('.charity_thumb');
    	//console.log( $value );
    	// Hide all thumbs
		$thumbs_class.fadeOut();
		// Show the thumb selected
		$( '#charity_thumb_' + $value ).fadeIn();

	};
	$('#gb_charity').live( 'change', show_charity_thumb );
	
	//on load
	var $select = $('#gb_charity');
	var $value = $select.val();
	var $thumbs_class = $('.charity_thumb');
	//console.log( $value );
	// Hide all thumbs
	$thumbs_class.fadeOut();
	// Show the thumb selected
	$( '#charity_thumb_' + $value ).fadeIn();
});
</script>

                
<style type="text/css">
#gb_charity {
	width: 55%;
	float: left;
}
.charity_thumbs {
	float: right;
	width: 40%;
	height: 120px;
	position: relative;
}
.charity_thumb {
	position: absolute;
	top: 0px;
	left: 0px;
}
	
</style>
