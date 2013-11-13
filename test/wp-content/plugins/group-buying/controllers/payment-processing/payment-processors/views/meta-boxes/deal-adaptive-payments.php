<p>
	<label for="adaptive_primary"><strong><?php self::_e( 'Primary Receiver Email' ) ?>:</strong></label>
	<input type="text" id="adaptive_primary" name="adaptive_primary" value="<?php echo $primary; ?>" size="15" />
</p>
<p>
	<label for="adaptive_secondary"><strong><?php self::_e( 'Secondary Receiver Email' ) ?>:</strong></label>
	<input type="text" id="adaptive_secondary" name="adaptive_secondary" value="<?php echo $secondary; ?>" size="15" />
</p>
<p>
	<label for="adaptive_secondary_share"><strong><?php self::_e( 'Secondary Receiver Payment' ) ?>:</strong></label>
	<input type="text" id="adaptive_secondary_share" name="adaptive_secondary_share" value="<?php echo $secondary_share; ?>" size="5" />&nbsp;<label><input type="checkbox" value="1" name="adaptive_share_percentage" <?php checked( $is_share_percentage, 1, TRUE ) ?>> <?php self::_e('This is a percentage.') ?></label>
</p>
<p><?php self::_e( '<strong>Notes:</strong> In a chained payment, the customer pays the primary receiver an amount, from which the primary receiver pays secondary receiver(s). The customer only knows about the primary receiver, not the secondary receiver(s). The secondary receiver(s) only know about the primary receiver, not the customer.  The primary receiver must be the larger amount, from which the secondary receiver is paid.' ) ?></p>
