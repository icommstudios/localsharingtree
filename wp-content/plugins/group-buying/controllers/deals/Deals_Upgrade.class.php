<?php

class Group_Buying_Deals_Upgrade extends Group_Buying {

	public static function upgrade_3_0() {
		global $wpdb;

		$old_posts = get_posts( array(
				'numberposts' => apply_filters( 'gb_migrate_deals_at_a_time', -1 ),
				'post_status' => 'any',
				'post_type' => 'deal'
			) );

		// Also upgrade trashed posts
		$old_trash_posts = get_posts( array(
				'numberposts' => apply_filters( 'gb_migrate_deals_at_a_time', -1 ),
				'post_status' => 'trash',
				'post_type' => 'deal'
			) );

		$old_posts = array_merge( $old_posts, $old_trash_posts );

		foreach ( $old_posts as $old_post ) {

			// 5 minutes for each deal, in case there are a lot of purchases
			set_time_limit( 5*60 );
			$post_id = $old_post->ID;

			// Pull this value out early so it isn't overwritten
			$voucher_logo = get_post_meta( $post_id, '_voucher_logo', true );

			printf( '<p style="margin-left: 20px">' . self::__( 'Updating Deal "%s"' ) . "</p>\n", $old_post->post_title );
			flush();

			wp_update_post( array(
					'ID' => $post_id,
					'post_type' => Group_Buying_Deal::POST_TYPE
				) );

			$deal = Group_Buying_Deal::get_instance( $post_id );

			// Update Meta Keys
			$amount_saved = get_post_meta( $post_id, '_dealSavings', true );
			$deal->set_amount_saved( $amount_saved );

			$base_price = get_post_meta( $post_id, '_dealCreditCost', true );
			$dynamic_price = get_post_meta( $post_id, '_dealDynCosts', true );
			$dynamic_price[0] = $base_price;
			$deal->set_prices( $dynamic_price );

			$expiration = get_post_meta( $post_id, '_dealExpiration', true );
			$expiration_status = get_post_meta( $post_id, '_meta_deal_complete_status', true );
			$expiration_disable = get_post_meta( $post_id, '_dealExpirationDisable', true );
			if ( 'disable' == $expiration_disable ) {
				$expiration = Group_Buying_Deal::NO_EXPIRATION_DATE;
			} elseif ( empty( $expiration ) ) {
				if ( empty( $expiration_status ) ) {
					$expiration = Group_Buying_Deal::NO_EXPIRATION_DATE;
				} else {
					$expiration = $expiration_status;
				}
			}
			$deal->set_expiration_date( $expiration );

			$fine_print = get_post_meta( $post_id, 'voucher_fine_print', true );
			$deal->set_fine_print( $fine_print );

			$highlights = get_post_meta( $post_id, 'dealHighlights', true );
			$deal->set_highlights( $highlights );

			$max_purchases = get_post_meta( $post_id, '_dealThresholdMax', true );
			if ( !$max_purchases ) {
				$max_purchases = Group_Buying_Deal::NO_MAXIMUM;
			}
			$deal->set_max_purchases( $max_purchases );

			$purchases_per_user = get_post_meta( $post_id, '_allowMultiplePurchases', true );
			$deal->set_max_purchases_per_user( $purchases_per_user );

			$min_purchases = get_post_meta( $post_id, '_dealThreshold', true );
			$deal->set_min_purchases( $min_purchases );

			$rss_excerpt = get_post_meta( $post_id, 'rss_excerpt', true );
			$deal->set_rss_excerpt( $rss_excerpt );

			$deal_value = get_post_meta( $post_id, '_dealWorth', true );
			$deal->set_value( $deal_value );

			$voucher_expiration = get_post_meta( $post_id, 'voucher_expiration', true );
			$deal->set_voucher_expiration_date( $voucher_expiration );

			$voucher_how_to_use = get_post_meta( $post_id, 'how_to_use', true );
			$deal->set_voucher_how_to_use( $voucher_how_to_use );

			$voucher_prefix = get_post_meta( $post_id, '_voucher_prefix', true );
			$deal->set_voucher_id_prefix( $voucher_prefix );

			$voucher_locations = array();
			$voucher_locations[] = get_post_meta( $post_id, 'deal_address_1', true );
			$voucher_locations[] = get_post_meta( $post_id, 'deal_address_2', true );
			$voucher_locations[] = get_post_meta( $post_id, 'deal_address_3', true );
			$voucher_locations[] = get_post_meta( $post_id, 'deal_address_4', true );
			$voucher_locations[] = get_post_meta( $post_id, 'deal_address_5', true );
			$deal->set_voucher_locations( $voucher_locations );

			$deal->set_voucher_logo( $voucher_logo );

			$voucher_map = get_post_meta( $post_id, 'google_maps_iframe', true );
			$deal->set_voucher_map( $voucher_map );

			$voucher_serials = get_post_meta( $post_id, '_voucher_serials', true );
			if ( is_array( $voucher_serials ) ) {
				$deal->set_voucher_serial_numbers( $voucher_serials );
			}

			// Update deal purchases
			$purchases = get_post_meta( $post_id, '_purchaseRecords' );

			if ( count( $purchases ) ) {
				// Set import version
				update_post_meta( $post_id, '_import_version', '2.3' );
			}

			if ( !empty( $purchases ) ) {

				printf( '<p style="margin-left: 20px">' . self::__( 'Updating %d Voucher(s) and Purchase(s) for Deal "%s"' ) . "</p>\n", count( $purchases ), $old_post->post_title );
				flush();

				// Allow one second per purchase, to avoid execution issues
				set_time_limit( 300 + count( $purchases ) );

				foreach ( $purchases as $old_purchase ) {
					$old_purchase = (object) $old_purchase;
					$user_id = $old_purchase->userID;
					$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
					$voucher_code = $old_purchase->coupon_code;
					$transaction_id = $old_purchase->transID;
					$security_code = $old_purchase->security_code;
					$purchase_date = date( 'Y-m-d H:i:s', $old_purchase->time );
					$item_value = $old_purchase->item_value;

					$purchase_id = wp_insert_post( array(
							'post_title' => sprintf( self::__( 'Order #%d' ), $transaction_id ),
							'post_status' => 'publish',
							'post_type' => Group_Buying_Purchase::POST_TYPE,
							'post_date' => $purchase_date
						) );
					$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
					$purchase->set_title( sprintf( self::__( 'Order #%d' ), $purchase_id ) );
					$purchase->set_user( $user_id );
					$purchase->set_original_user( $user_id );
					$purchase->set_total( $item_value );
					$purchase->set_products( array( array(
								'deal_id' => $post_id,
								'quantity' => 1,
								'unit_price' => $item_value,
								'price' => $item_value
							) ) );

					$voucher_id = Group_Buying_Voucher::new_voucher( $purchase_id, $deal->get_id() );
					wp_publish_post( $voucher_id );

					$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
					$voucher->set_serial_number( $voucher_code );
					$voucher->set_security_code( $security_code );
					$voucher->set_purchase( $purchase_id );
					$voucher->set_deal( $post_id );
					// Set import version
					update_post_meta( $voucher_id, '_import_version', '2.3' );
					$voucher->activate();
				}
			}

			$codes = get_post_meta( $post_id, '_dealsCodesArray', true );

			if ( count( $codes ) ) {
				// Set import version
				update_post_meta( $post_id, '_import_version', '<= 2.1' );
			}

			if ( !empty( $codes ) ) {

				printf( '<p style="margin-left: 20px">' . self::__( 'Updating %d Voucher(s) and Purchase(s) for Deal "%s"' ) . "</p>\n", count( $codes ), $old_post->post_title );
				flush();

				// Allow one second per purchase, to avoid execution issues
				set_time_limit( 300 + count( $codes ) );

				foreach ( $codes as $user_id => $code ) {
					$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
					$voucher_code = $code;
					$transaction_id = get_user_meta( $user_id, '_' . $deal->get_id() . '_transaction_id', true );

					$purchase_id = wp_insert_post( array(
							'post_title' => sprintf( self::__( 'Order #%d' ), $transaction_id ),
							'post_status' => 'publish',
							'post_type' => Group_Buying_Purchase::POST_TYPE
						) );
					$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
					$purchase->set_title( sprintf( self::__( 'Order #%d' ), $purchase_id ) );
					$purchase->set_user( $user_id );
					$purchase->set_original_user( $user_id );
					$purchase->set_total( $deal->get_price( 0 ) );
					$purchase->set_products( array( array(
								'deal_id' => $post_id,
								'quantity' => 1,
								'unit_price' => $deal->get_price( 0 ),
								'price' => $deal->get_price( 0 )
							) ) );

					$voucher_id = Group_Buying_Voucher::new_voucher( $purchase_id, $deal->get_id() );
					wp_publish_post( $voucher_id );

					$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
					$voucher->set_serial_number( $voucher_code );
					$voucher->set_purchase( $purchase_id );
					$voucher->set_deal( $post_id );

					// Set import version
					update_post_meta( $voucher_id, '_import_version', '<= 2.1' );
				}
			}
		}
	}
}