<fieldset id="gb-account-account-info">
	<legend class="section_heading contrast gb_ff"><?php sec_e( 'Daily E-Mails' ); ?></legend>
	<table class="collapsable subscription form-table">
		<tbody>
				<tr>
					<td width="160">
						<label><?php sec_e( 'Locations' ) ?></label><br/>
						<small><?php sec_e( "Select the locations you'd like to get alerted for." ) ?></small>
					</td>
					<td>
						<?php
							$locations = gb_get_locations( FALSE );
							foreach ( $locations as $location ) {
								$checked = ( $optin == 'checked' || in_array( $location->slug, (array)$options ) ) ? 'checked="checked"' : '' ;
								echo '<span class="location_pref_input_wrap"><input type="checkbox" name="'.$name.'[]" value="'.$location->slug.'" '.$checked.'>'.$location->name.'</span> ';
							} ?>
					</td>
				</tr>
		</tbody>
	</table>
</fieldset>
