<form id="gb_merchant_register" class="registration_layout main_block" method="post"  enctype="multipart/form-data" action="<?php gb_merchant_edit_url(); ?>">
	<input type="hidden" name="gb_merchant_action" value="<?php echo Group_Buying_Merchants_Edit::FORM_ACTION; ?>" />
	<table class="collapsable form-table">
		<tbody>
			<?php foreach ( $fields as $key => $data ): ?>
				<tr>
					<?php if ( $data['type'] != 'checkbox' ): ?>
						<td><?php gb_form_label($key, $data, 'contact'); ?></td>
						<td><?php gb_form_field($key, $data, 'contact'); ?></td>
					<?php else: ?>
						<td colspan="2">
							<label for="gb_contact_<?php echo $key; ?>"><?php gb_form_field($key, $data, 'contact'); ?> <?php echo $data['label']; ?></label>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php self::load_view('merchant/edit-controls', array()); ?>
</form>