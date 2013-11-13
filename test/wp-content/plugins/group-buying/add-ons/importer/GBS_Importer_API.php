<?php

/**
 * Class GBS_Importer_API
 */
abstract class GBS_Importer_API {
	/**
	 * Get an array of deals to import.
	 *
	 * @return GBS_Importer_Deal[] An array of prepared importer deal objects
	 */
	abstract public function get_deals_to_import();

	/**
	 * @param array $product
	 *  - deal_id - the ID of the deal
	 *  - quantity - how many of that deal were purchased
	 *  - price - how much was paid per item
	 *  - data - any additional information we're storing about the purchased item
	 *  - payment_method - which payment_processor(s) pay(s) for all or part of this deal
	 *    - key = name of the payment method
	 *    - value = amount this payment method is handling
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public function send_purchase_notification( $product, $purchase ) {
		// override in subclasses to send a notification to the api
		// that a deal has been purchased, get any voucher information
		// from that API, and create a local voucher with those details
	}

	/**
	 * @param array $product A product array (see send_purchase_notification() for details)
	 * @param Group_Buying_Purchase $purchase
	 * @param string $serial_number
	 * @param string $security_code
	 *
	 * @return void
	 */
	protected function create_voucher( $product, $purchase, $serial_number = '', $security_code = '' ) {
		$voucher_id = Group_Buying_Voucher::new_voucher( $purchase->get_id(), $product['deal_id'] );
		$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
		$voucher->set_product_data( $product );
		$voucher->set_serial_number( $serial_number );
		$voucher->set_security_code( $security_code );
		do_action( 'create_voucher_for_purchase', $voucher_id, $purchase, $product );
	}
}
