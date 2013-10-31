<?php

class GBS_Importer_Framework {
	const META_KEY_IMPORT_SOURCE = '_gbs_import_source';
	const META_KEY_IMPORT_URL = '_gbs_import_source_url';
	const META_KEY_IMPORT_ID = '_gbs_import_source_id';

	/** @var GBS_Importer_Framework */
	private static $instance;

	/** @var GBS_Importer_API[] */
	private $importers = array();

	public function run_imports() {
		$importers = $this->get_importers();

		foreach ( $importers as $i ) {
			if ( $i instanceof GBS_Importer_API ) {
				foreach ( $i->get_deals_to_import() as $deal ) {
					try {
						$deal->import();
					} catch ( Exception $e ) {
						error_log( 'Error importing deal. Details: '.print_r($deal, TRUE) );
					}
				}
			}
		}
	}

	/**
	 * Users shouldn't be able to add a syndicated deal to their carts
	 *
	 * @param int $qty
	 * @param int $deal_id
	 * @return int|bool
	 */
	public function prevent_purchase_of_external_deal( $qty, $deal_id ) {
		if ( get_post_meta($deal_id, self::META_KEY_IMPORT_URL, TRUE) ) {
			return FALSE;
		}
		return $qty;
	}

	/**
	 * The add to cart button should be replaced with a link to the original item
	 *
	 * @param string $button
	 * @return string
	 */
	public function filter_add_to_cart_button( $button ) {
		$id = get_the_ID();
		$link = get_post_meta($id, self::META_KEY_IMPORT_URL, TRUE);
		if ( !$link ) {
			return $button;
		}
		$source = get_post_meta($id, self::META_KEY_IMPORT_SOURCE, TRUE);

		// if an importer is going to allow local purchases, it should return FALSE here
		if ( !apply_filters('gb_importer_redirect_cart_button', TRUE, $id, $source) ) {
			return $button;
		}

		if ( $source ) {
			$text = sprintf( gb__('Purchase at %s'), $source );
		} else {
			$text = gb__('Purchase');
		}
		$a = sprintf('<a href="%s" class="gb-importer-link">%s</a>', esc_url($link), $text);

		// This is an opportune time to add affiliate links
		return apply_filters('gb_importer_external_link', $a, esc_url($link), $id);
	}

	/**
	 * The add to cart button should be replaced with a link to the original item
	 *
	 * @param string  $button
	 * @return string
	 */
	public function filter_add_to_cart_url( $url ) {
		$id = get_the_ID();
		$link = get_post_meta( $id, self::META_KEY_IMPORT_URL, TRUE );
		if ( !$link ) {
			return $url;
		}
		return apply_filters( 'gb_importer_external_url', $link, esc_url($link), $id );
	}

	/**
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public function notify_apis_of_purchase( $purchase ) {
		foreach ( $purchase->get_products() as $product ) {
			if ( $affiliate = get_post_meta( $product['deal_id'], self::META_KEY_IMPORT_SOURCE, TRUE ) ) {
				$importers = isset($importers)?$importers:$this->get_importers();
				if ( isset($importers[$affiliate]) ) {
					$importers[$affiliate]->send_purchase_notification( $product, $purchase );
				}
			}
		}
	}

	/**
	 * @return GBS_Importer_API[]
	 */
	private function get_importers() {
		if ( empty($this->importers) ) {
			$this->importers = apply_filters( 'gbs_importer_apis', array() );
		}
		return $this->importers;
	}

	private function add_hooks() {
		add_action( 'gb_cron', array( $this, 'run_imports' ), 10, 0 );
		add_filter('gb_get_add_to_cart_form', array($this, 'filter_add_to_cart_button'), 100, 1);
		add_filter( 'gb_get_add_to_cart_url', array( $this, 'filter_add_to_cart_url' ), 100, 1 );
		add_filter('account_can_purchase', array($this, 'prevent_purchase_of_external_deal'), 500, 2);
		add_action( 'purchase_completed', array( $this, 'notify_apis_of_purchase' ), 10, 1 );
	}

	/********** Singleton *************/

	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return GBS_Importer_Framework
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	final public function __clone() {
		trigger_error("Singleton. No cloning allowed!", E_USER_ERROR);
	}

	final public function __wakeup() {
		trigger_error("Singleton. No serialization allowed!", E_USER_ERROR);
	}

	protected function __construct() {
		$this->add_hooks();
	}
}
