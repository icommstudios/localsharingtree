<?php
	$claimed = FALSE;
	if ( isset($_GET['gb_voucher_claim']) && $_GET['gb_voucher_claim'] != '' ) {
		$vouchers = Group_Buying_Voucher::get_voucher_by_security_code($_GET['gb_voucher_claim']);
		if ( !empty($vouchers) ) {
			$voucher = Group_Buying_Voucher::get_instance($vouchers[0]);
			if ( is_a($voucher,'Group_Buying_Voucher') ) {
				$claimed = $voucher->get_claimed_date();
			}
		}
	}
	?>	
<?php if ( FALSE != $claimed ) : ?>
	<div id="voucher_claimed_warning" class="main_block clearfix">
    
		<?php if ( !isset($_POST['gb_voucher_claim'] ) ) : ?>
       	 	<p class="warning button font_xx_large"><?php gb_e('Voucher Already Redeemed!') ?></p>
        	<?php $redemption_data = $voucher->get_redemption_data(); ?>
			<span class="voucher_info_title gb_ff"><?php gb_e('Voucher marked claimed:') ?></span> <span class="voucher_info button warning"><?php echo date(get_option('date_format'),$claimed) ?></span>
      		<div class="alert"><small>*this voucher is no longer valid - do not accept</small></div>
        <?php else : ?>
        	<?php $redemption_data = $voucher->get_redemption_data(); ?>
			<span class="voucher_info_title gb_ff"><?php gb_e('Voucher marked claimed:') ?></span> <span class="voucher_info button success"><?php echo date(get_option('date_format'),$claimed) ?></span>
        <?php endif; ?>
	
	</div>
<?php else: ?>


	<form id="claim_voucher"  class="main_block registration_layout"  action="" method="post">

	<table class="collapsable form-table">
		<tbody>
			<tr>
				<td>
					<?php gb_form_label('claim', array('label'=>'Security Code'), 'voucher'); ?>
				</td>
				<td class="gb-form-field gb-form-field-text">
					<?php 
						$code = (isset($_GET['gb_voucher_claim'])&&$_GET['gb_voucher_claim']!='') ? $_GET['gb_voucher_claim'] : '' ;
						gb_form_field('claim', array('type'=>'text','default'=>$code), 'voucher'); 
						?>
				</td>
			</tr>
		
		</tbody>
	</table>
	<?php 
		if ( isset($_GET['redirect_to']) && $_GET['redirect_to'] != '') {
			echo '<input type="hidden" value="'.$_GET['redirect_to'].'">';
		}
	 ?>
	<input type="submit" class="form-submit" value="<?php gb_e('Record as Claimed') ?>">
</form>
<?php endif ?>

