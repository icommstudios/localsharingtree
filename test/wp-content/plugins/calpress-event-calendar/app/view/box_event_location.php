<h4 class="calp-section-title"><?php _e( 'Event location details', CALP_PLUGIN_NAME ); ?></h4>
<table class="calp-form calp-location-form">
	<tbody>
		<tr>
			<td class="calp-first">
				<label for="calp_venue">
					<?php _e( 'Venue name:', CALP_PLUGIN_NAME ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="calp_venue" id="calp_venue" value="<?php echo $venue; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="calp_address">
					<?php _e( 'Address:', CALP_PLUGIN_NAME ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="calp_address" id="calp_address" value="<?php echo $address; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="calp_google_map">
					<?php _e( 'Show Google Map:', CALP_PLUGIN_NAME ); ?>
				</label>
			</td>
			<td>
				<input type="checkbox" value="1" name="calp_google_map" id="calp_google_map" <?php echo $google_map; ?> />
			</td>
		</tr>
	</tbody>
</table>
<div class="calp_box_map <?php if( $show_map ) echo 'calp_box_map_visible' ?>">
	<div id="calp_map_canvas"></div>
</div>
<input type="hidden" name="calp_city" 				id="calp_city" 				value="<?php echo $city; ?>" />
<input type="hidden" name="calp_province" 		id="calp_province" 		value="<?php echo $province; ?>" />
<input type="hidden" name="calp_postal_code" id="calp_postal_code"	value="<?php echo $postal_code; ?>" />
<input type="hidden" name="calp_country" 		id="calp_country" 			value="<?php echo $country; ?>" />
