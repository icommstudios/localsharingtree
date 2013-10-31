<?php

/**
 * Class GBS_Importer_Deal
 * @property-read deal_id
 * @property string source
 * @property string source_id
 * @property string source_url
 * @property string title
 * @property string description
 * @property string fine_print
 * @property int start_date
 * @property int end_date
 * @property string image_url
 * @property string price
 * @property string value
 * @property string merchant_name
 * @property string merchant_url
 */
class GBS_Importer_Deal {
	protected $deal_id;
	protected $source = '';
	protected $source_id = '';
	protected $source_url = '';
	protected $title = '';
	protected $description = '';
	protected $price = '';
	protected $value = '';
	protected $fine_print = '';
	protected $start_date = 0;
	protected $end_date = 0;
	protected $image_url = '';

	protected $merchant_name = '';
	protected $merchant_url = '';

	public function __construct() {

	}

	public function __get( $name ) {
		if ( method_exists($this, 'get_'.$name) ) {
			return $this->{'get_'.$name}();
		} else {
			$trace = debug_backtrace();
			throw new InvalidArgumentException(sprintf(__('Undefined property via __get(): %s in %s on line %s'), $name, $trace[0]['file'], $trace[0]['line']));
		}
	}

	public function __set( $name, $value ) {
		if ( method_exists($this, 'set_'.$name) ) {
			return $this->{'set_'.$name}( $value );
		} else {
			$trace = debug_backtrace();
			throw new InvalidArgumentException(sprintf(__('Undefined property via __set(): %s in %s on line %s'), $name, $trace[0]['file'], $trace[0]['line']));
		}
	}

	public function is_imported() {
		$id = $this->get_deal_id();
		if ( empty( $id ) ) {
			return FALSE;
		}
		return TRUE;
	}

	public function import() {
		$deal = $this->get_deal();
		if ( !$deal ) {
			return;
		}

		update_post_meta( $deal->get_id(), GBS_Importer_Framework::META_KEY_IMPORT_SOURCE, $this->get_source() );
		update_post_meta( $deal->get_id(), GBS_Importer_Framework::META_KEY_IMPORT_URL, $this->get_source_url() );
		update_post_meta( $deal->get_id(), GBS_Importer_Framework::META_KEY_IMPORT_ID, $this->get_source_id() );

		$deal->set_prices(array($this->get_price()));
		$deal->set_value($this->get_value());
		$deal->set_fine_print($this->get_fine_print());
		$deal->set_expiration_date($this->get_end_date());
		$deal->set_max_purchases(Group_Buying_Deal::NO_MAXIMUM);

		if ( $this->get_image_url() && !has_post_thumbnail($deal->get_id()) ) {
			$thumbnail = $this->media_sideload_image( $this->get_image_url(), $deal->get_id() );
			if ( $thumbnail && !is_wp_error($thumbnail) ) {
				update_post_meta($deal->get_id(), '_thumbnail_id', $thumbnail);
			}
		}

		if ( $this->merchant_name ) {
			$merchant = get_page_by_title($this->get_merchant_name(), OBJECT, Group_Buying_Merchant::POST_TYPE);
			if ( empty($merchant) ) {
				$post = array(
					'post_title' => $this->merchant_name,
					'post_status' => 'pending',
					'post_type' => Group_Buying_Merchant::POST_TYPE
				);
				$merchant_id = wp_insert_post( $post );
				$deal->set_merchant_id($merchant_id);
			} else {
				$deal->set_merchant_id($merchant->ID);
			}
			if ( $this->merchant_url ) {
				$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
				if ( is_a( $merchant, 'Group_Buying_Merchant' ) ) {
					$merchant->set_website( $this->get_merchant_url() );
				}
			}
		}
	}

	/**
	 * @return Group_Buying_Deal
	 * @throws RuntimeException
	 */
	private function get_deal() {
		$id = $this->get_deal_id();
		if ( $id ) {
			return Group_Buying_Deal::get_instance($id);
		}
		if ( empty($this->title) ) {
			throw new RuntimeException(gb__('Cannot import deal without a title.'));
		}
		$post = array(
			'post_type' => Group_Buying_Deal::POST_TYPE,
			'post_status' => 'pending',
			'post_title' => $this->get_title(),
			'post_content' => $this->get_description(),
		);
		if ( $start = $this->get_start_date() ) {
			$post['post_date_gmt'] = date('Y-m-d H:i:s', $start);
			$post['post_date'] = get_date_from_gmt($post['post_date_gmt']);
		}
		$id = wp_insert_post($post);
		if ( $id ) {
			$this->deal_id = $id;
			return Group_Buying_Deal::get_instance($id);
		}
		return NULL;
	}

	public function get_deal_id() {
		if ( isset($this->deal_id) ) {
			return $this->deal_id;
		}
		$deals = Group_Buying_Post_Type::find_by_meta( Group_Buying_Deal::POST_TYPE, array(
			GBS_Importer_Framework::META_KEY_IMPORT_URL => $this->source_url,
		));
		if ( empty($deals) ) {
			$this->deal_id = 0;
		} else {
			$this->deal_id = reset($deals);
		}
		return $this->deal_id;
	}

	public function set_description( $description ) {
		$this->description = $description;
	}

	public function get_description() {
		return $this->description;
	}

	public function set_end_date( $end_date ) {
		if ( !is_numeric($end_date) ) {
			$end_date = strtotime($end_date);
		}
		$this->end_date = (int)$end_date;
	}

	public function get_end_date() {
		return $this->end_date;
	}

	public function set_fine_print( $fine_print ) {
		$this->fine_print = $fine_print;
	}

	public function get_fine_print() {
		return $this->fine_print;
	}

	public function set_image_url( $image_url ) {
		$this->image_url = $image_url;
	}

	public function get_image_url() {
		return $this->image_url;
	}

	public function set_merchant_name( $merchant_name ) {
		$this->merchant_name = $merchant_name;
	}

	public function get_merchant_name() {
		return $this->merchant_name;
	}

	public function set_merchant_url( $merchant_url ) {
		$this->merchant_url = $merchant_url;
	}

	public function get_merchant_url() {
		return $this->merchant_url;
	}

	public function set_price( $price ) {
		$this->price = $price;
	}

	public function get_price() {
		return $this->price;
	}

	public function set_source( $source ) {
		$this->source = $source;
	}

	public function get_source() {
		return $this->source;
	}

	public function set_source_id( $source_id ) {
		$this->source_id = $source_id;
	}

	public function get_source_id() {
		return $this->source_id;
	}

	public function set_source_url( $source_url ) {
		$this->source_url = $source_url;
	}

	public function get_source_url() {
		return $this->source_url;
	}

	public function set_start_date( $start_date ) {
		if ( !is_numeric($start_date) ) {
			$start_date = strtotime($start_date);
		}
		$this->start_date = $start_date;
	}

	public function get_start_date() {
		return $this->start_date;
	}

	public function set_title( $title ) {
		$this->title = $title;
	}

	public function get_title() {
		return $this->title;
	}

	public function set_value( $value ) {
		$this->value = $value;
	}

	public function get_value() {
		return $this->value;
	}

	/**
	 * Just like media_sideload_image(), but returns the ID instead of HTML
	 *
	 * @param string $file
	 * @param int $post_id
	 * @param string $desc
	 *
	 * @return int|WP_Error
	 */
	private function media_sideload_image( $file, $post_id, $desc = NULL ) {
		if ( ! empty($file) ) {
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			// Download file to temp location
			$tmp = download_url( $file );

			// Set variables for storage
			// fix file filename for query strings
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink
			if ( is_wp_error($id) ) {
				@unlink($file_array['tmp_name']);
				return $id;
			}

			$src = wp_get_attachment_url( $id );
		}

		// Finally check to make sure the file has been saved, then return the html
		if ( !empty($id) && !empty($src) ) {
			return $id;
		}
		return 0;
	}
}
