
<p>
	<label for="gbs_adv_voucher_expiry_onoff"><?php self::_e('Turn Adv. Voucher Expiry On/Off'); ?>:</label>&nbsp;&nbsp;&nbsp;
	<select name="gbs_adv_voucher_expiry_onoff" id="gbs_adv_voucher_expiry_onoff">
		<?php
			// Get selected
			$select_onoff = array('off' => 'Off', 'on' => 'On');
			foreach ($select_onoff as $slug => $select_item) {
				echo '<option value="'.$slug.'" '.selected($expiry_onoff,$slug,FALSE).'>'.$select_item.'</option>';
			}
		?>
	</select>
</p>
<p>
	<label for="gbs_adv_voucher_expiry_count"><?php self::_e('Voucher expires'); ?>:</label>&nbsp;&nbsp;&nbsp;
	<input type="text" name="gbs_adv_voucher_expiry_count" value="<?php echo $expiry_count; ?>" id="gbs_adv_voucher_expiry_count" class="small-text" size="5"> days after purchase
</p>

