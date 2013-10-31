<?php

class Group_Buying_Accounts_Upgrade {
	public static function upgrade_3_0() {

		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->users} LEFT JOIN {$wpdb->usermeta} ON {$wpdb->users}.ID = {$wpdb->usermeta}.user_id AND {$wpdb->usermeta}.meta_key = 'gb_account_upgraded' WHERE (  {$wpdb->usermeta}.meta_value IS NULL OR {$wpdb->usermeta}.meta_value LIKE '0')";
		$users = $wpdb->get_results( $sql );

		$user_count = count( $users ); $i = 0;
		foreach ( $users as $user ) {
			$i++;
			// Create Account
			$account = Group_Buying_Account::get_instance( $user->ID );
			// Display
			printf( '<p style="margin-left: 20px">%s of %s &mdash; User #%s</p>', $i, $user_count, $user->ID );
			flush();

			if ( !empty( $user->user_firstname ) ) {
				$account->set_name( 'first', $user->user_firstname );
			} else { // If there is no first name attempt to make one.
				$display_name = explode( ' ', $user->display_name );
				$account->set_name( 'first', $display_name[0] );
			}

			if ( !empty( $user->user_lastname ) ) {
				$account->set_name( 'last', $user->user_lastname );
			} else { // If there is no last name attempt to make one.
				$display_name = explode( ' ', $user->display_name );
				$account->set_name( 'last', $display_name[1] );
			}

			// Capture old credits
			$old_credits = get_user_meta( $user->ID, '_totalCredits', true );
			$account->add_credit( $old_credits, Group_Buying_Affiliates::CREDIT_TYPE );
			do_action( 'gb_upgrade_account', $account, $user );
			add_user_meta( $user->ID, 'gb_account_upgraded', '1', TRUE );
			unset( $account ); // for memory issues.
		}
	}
}