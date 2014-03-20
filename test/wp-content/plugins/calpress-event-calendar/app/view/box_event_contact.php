<h4 class="calp-section-title"><?php _e( 'Organizer contact info', CALP_PLUGIN_NAME ); ?></h4>
<table class="calp-form">
	<tbody>
		<tr>
			<td class="calp-first">
				<label for="calp_contact_name">
					<?php _e( 'Contact name:', CALP_PLUGIN_NAME ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="calp_contact_name" id="calp_contact_name" value="<?php echo $contact_name; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="calp_contact_phone">
					<?php _e( 'Phone:', CALP_PLUGIN_NAME ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="calp_contact_phone" id="calp_contact_phone" value="<?php echo $contact_phone; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="calp_contact_email">
					<?php _e( 'E-mail:', CALP_PLUGIN_NAME ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="calp_contact_email" id="calp_contact_email" value="<?php echo $contact_email; ?>" />
			</td>
		</tr>
	</tbody>
</table>
