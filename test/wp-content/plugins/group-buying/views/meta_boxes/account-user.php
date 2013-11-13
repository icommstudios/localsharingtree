<?php 
	$account_id = $account->get_id();
	$user_id = $account->get_user_id();
	$user = get_userdata( $user_id );
 ?>

<p><?php self::_e('User ID:') ?> <?php echo $user_id; ?></p>
<p><?php self::_e('Profile:') ?> <?php printf( '<a href="user-edit.php?user_id=%s">%s</a>', $user_id, $user->user_login ) ?></p>

<strong><?php self::_e('Activity') ?></strong>
<p>
	<?php
		printf( '<a href="admin.php?page=group-buying/payment_records&account_id=%s" class="alt_button">%s</a>', $account_id, self::__( 'Payments' ) ); ?>&nbsp;&nbsp;
	<?php
		printf( '<a href="admin.php?page=group-buying/purchase_records&account_id=%s" class="alt_button">%s</a>', $account_id, self::__( 'Orders') ); ?>&nbsp;&nbsp;
	<?php
		printf( '<a href="admin.php?page=group-buying/voucher_records&account_id=%s" class="alt_button">%s</a>', $account_id, self::__( 'Vouchers') ); ?>&nbsp;&nbsp;
	<?php
		printf( '<a href="admin.php?page=group-buying/gift_records&account_id=%s" class="alt_button">%s</a>', $account_id, self::__( 'Gifts') ); ?>
</p>