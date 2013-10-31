<?php


class Group_Buying_Merchants_Upgrade {
	public static function upgrade_3_0() {
		$merchant_posts = get_posts( array(
				'numberposts' => -1,
				'post_status' => 'any',
				'post_type' => Group_Buying_Deal::POST_TYPE
			) );
		foreach ( $merchant_posts as $merchant_post ) {

			$post_id = $merchant_post->ID;
			$deal = Group_Buying_Deal::get_instance( $post_id );

			$merchant_id = $deal->get_merchant_id();
			if ( empty( $merchant_id ) ) {
				$merchant_name = get_post_meta( $post_id, '_merchant_name', true );
				$merchant_address = get_post_meta( $post_id, '_merchant_address', true );
				$merchant_city = get_post_meta( $post_id, '_merchant_city', true );
				$merchant_state = get_post_meta( $post_id, '_merchant_state', true );
				$merchant_zip = get_post_meta( $post_id, '_merchant_zip', true );
				$merchant_country = get_post_meta( $post_id, '_merchant_country', true );
				$merchant_phone = get_post_meta( $post_id, '_merchant_phone', true );
				$merchant_website = get_post_meta( $post_id, '_merchant_website', true );
				if ( !empty( $merchant_name ) ) {
					$merchant_id = wp_insert_post( array(
							'post_type' => Group_Buying_Merchant::POST_TYPE,
							'post_title' => $merchant_name
						) );
					wp_publish_post( $merchant_id );
					$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
					$merchant->set_contact_name( $merchant_name );
					$merchant->set_contact_street( $merchant_address );
					$merchant->set_contact_city( $merchant_city );
					$merchant->set_contact_state( $merchant_state );
					$merchant->set_contact_postal_code( $merchant_zip );
					$merchant->set_contact_country( $merchant_country );
					$merchant->set_contact_phone( $merchant_phone );
					$merchant->set_website( $merchant_website );
					$deal->set_merchant_id( $merchant_id );
					do_action( 'gb_upgrade_merchant', $merchant );
				}
			}
		}
	}
}
