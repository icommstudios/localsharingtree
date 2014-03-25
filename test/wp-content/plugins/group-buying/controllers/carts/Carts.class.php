<?php

/**
 * Carts Controller
 *
 * @package GBS
 * @subpackage Cart
 */
class Group_Buying_Carts extends Group_Buying_Controller {
	const CART_PATH_OPTION = 'gb_cart_path';
	const CART_QUERY_VAR = 'gb_show_cart';
	const ADD_TO_CART_QUERY_VAR = 'add_to_cart';
	private static $cart_path = 'cart';
	private static $instance;
	private $cart = NULL;

	public static function init() {
		self::$cart_path = get_option( self::CART_PATH_OPTION, self::$cart_path );
		self::register_settings();
		self::register_query_var( self::ADD_TO_CART_QUERY_VAR, array( get_class(), 'add_to_cart' ) );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_path_callback' ), 10, 1 );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'gb_url_path_cart' => array(
				'weight' => 105,
				'settings' => array(
					self::CART_PATH_OPTION => array(
						'label' => self::__( 'Cart Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$cart_path
							)
						)
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );
	}

	/**
	 * Register the path callback for the cart page
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_path_callback( GB_Router $router ) {
		$path = str_replace( '/', '-', self::$cart_path );
		$args = array(
			'path' => self::$cart_path,
			'title' => 'Cart',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_cart_page' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$cart_path ).'.php', // non-default cart path
				self::get_template_path().'/cart.php', // theme override
				GB_PATH.'/views/public/cart.php', // default
			),
		);
		$router->add_route( self::CART_QUERY_VAR, $args );
	}

	/**
	 * Something has been added to the cart. Do something about it.
	 *
	 * @static
	 * @return void
	 */
	public static function add_to_cart( $qty = 1, $data = array() ) {
		global $wp;
		if ( !is_numeric($qty) ) {
			$qty = 1;
		}
		if ( isset( $wp->query_vars[self::ADD_TO_CART_QUERY_VAR] ) ) {
			$item_id = (int)$wp->query_vars[self::ADD_TO_CART_QUERY_VAR];
			$qty = ( isset( $_REQUEST['qty'] ) ) ? (int)$_REQUEST['qty'] : $qty ;
			if ( $qty < 1 ) {
				self::set_message( self::__( 'Unable to add item to cart' ) );
			}
			elseif ( $item_id ) {
				$account = Group_Buying_Account::get_instance();
				$data = apply_filters( 'add_to_cart_data', $data, $item_id, $qty );
				if ( is_wp_error( $data ) ) {
					foreach ( $data->get_error_messages() as $message ) {
						self::set_message( $message, self::MESSAGE_STATUS_ERROR );
					}
				} elseif ( $account->can_purchase( $item_id, $data ) ) {
					$cart = Group_Buying_Cart::get_instance();
					if ( $cart->add_item( $item_id, $qty, $data ) ) {
						self::set_message( self::__( 'Cart updated' ) );
					} else {
						self::set_message( self::__( 'Unable to add item to cart' ) );
					}
				} else {
					self::set_message( self::__( 'You are not eligible to purchase this deal.' ), self::MESSAGE_STATUS_ERROR );
				}
			}
			$clean_redirect = apply_filters( 'gb_add_to_cart_clean_url_redirect', TRUE );
			if ( $clean_redirect ) {
				wp_redirect( apply_filters( 'add_to_cart_redirect_url', remove_query_arg( array( self::ADD_TO_CART_QUERY_VAR, 'qty' ) ) ), 303 );
				exit();
			}
		}
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the cart page
	 */
	public static function add_to_cart_url( $id = 0, $path = null ) {
		if ( null === $path ) {
			$path = self::$cart_path;
		}
		if ( !$id ) {
			return NULL;
		}
		if ( self::using_permalinks() ) {
			return add_query_arg( array( self::ADD_TO_CART_QUERY_VAR => $id ), trailingslashit( home_url() ).trailingslashit( $path ) );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( 'gb_show_cart', array( self::ADD_TO_CART_QUERY_VAR => $id ) );
		}
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the cart page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$cart_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::CART_QUERY_VAR );
		}
	}

	/**
	 * We're on the cart page, so handle any form submissions from that page,
	 * and make sure we display the correct information (i.e., the cart)
	 *
	 * @static
	 * @return void
	 */
	public static function on_cart_page() {
		// by instantiating, we process any submitted values
		$cart = self::get_instance();

		// display the cart
		$cart->view_cart();
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a cart page
	 */
	public static function is_cart_page() {
		return GB_Router_Utility::is_on_page( self::CART_QUERY_VAR );
	}

	/**
	 * Prints the Add to Cart form for the given deal
	 *
	 * @static
	 * @param int     $deal_id
	 * @return void
	 */
	public static function add_to_cart_form( $deal_id, $button_text = 'Add to Cart' ) {
		$deal = Group_Buying_Deal::get_instance( $deal_id );
		$account = Group_Buying_Account::get_instance();
		if ( $qty = $account->can_purchase( $deal_id ) ) {
			$fields = apply_filters( 'gb_add_to_cart_form_fields', array(
					'<input type="hidden" name="'.self::ADD_TO_CART_QUERY_VAR.'" value="'.$deal_id.'" />',
				), $deal_id );
			self::load_view( 'add-to-cart', array(
					'deal_id' => $deal_id,
					'button_text' => self::__( $button_text ),
					'fields' => $fields,
				) );
		} else {
			self::load_view( 'sold-out', array(
					'deal_id' => $deal_id,
				) );
		}
	}

	/*
	 * Singleton Design Pattern
	 * ------------------------------------------------------------- */
	private function __clone() {
		// cannot be cloned
		trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
	}
	private function __sleep() {
		// cannot be serialized
		trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
	}
	/**
	 *
	 *
	 * @static
	 * @return Group_Buying_Carts
	 */
	public static function get_instance() {
		if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		self::do_not_cache(); // don't want to cache any page where we're loading a dynamic cart
		$this->cart = Group_Buying_Cart::get_instance();
		do_action( 'gb_processing_cart', $this->cart );
		if ( isset( $_POST['gb_cart_action-update'] ) ) {
			$this->update_cart();
		} elseif ( isset( $_POST['gb_cart_action-checkout'] ) ) {
			$this->update_cart();
			do_action( 'gb_proceeding_to_checkout', $this->cart );
			wp_redirect( Group_Buying_Checkouts::get_url(), 303 );
			exit();
		}
	}

	/**
	 * The cart form was submitted. Updated the cart.
	 *
	 * @return void
	 */
	public function update_cart() {
		$updated = FALSE;
		if ( isset( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
			foreach ( $_POST['items']  as $key => $item ) {
				$deal_id = $item['id'];
				if ( isset( $item['data'] ) && $item['data'] ) {
					$data = unserialize( stripslashes( $item['data'] ) );
				} else {
					$data = array();
				}
				if ( ( isset( $item['remove'] ) && $item['remove'] )
					|| !isset( $item['qty'] )
					|| 0 == (int)$item['qty']
				) {
					$this->cart->remove_item( $deal_id, $data );
					$updated = TRUE;
				} else {
					$qty = $this->cart->get_quantity( $deal_id, $data );
					if ( $qty != $item['qty'] ) {
						$this->cart->set_quantity( $deal_id, $item['qty'], $data );
						$updated = TRUE;
					}
				}
			}
		}
		if ( $updated ) {
			self::set_message( self::__( 'Cart updated' ) );
			do_action( 'gb_cart_updated' );
		}
	}


	/**
	 * Print the cart
	 *
	 * @return void
	 */
	public function view_cart() {
		remove_filter( 'the_content', 'wpautop' );
		self::load_view( 'cart/cart', self::get_view_variables( $this->cart ) );
	}

	/**
	 * Get an array of variables to be sent to the cart view
	 *
	 * @static
	 * @param Group_Buying_Cart $cart
	 * @param bool    $static Whether this is an interactive cart (i.e., a form), or just for display
	 * @return array
	 */
	public static function get_view_variables( $cart, $static = FALSE ) {
		$columns = array(
			'remove' => self::__( 'Remove' ),
			'name' => self::__( 'Deal Name' ),
			'quantity' => self::__( 'Quantity' ),
			'price' => self::__( 'Price' ),
		);
		if ( $static ) {
			unset( $columns['remove'] );
		}
		$account = Group_Buying_Account::get_instance();
		$items = array();
		foreach ( $cart->get_items() as $key => $item ) {
			$deal = Group_Buying_Deal::get_instance( $item['deal_id'] );
			$max_quantity = $account->can_purchase( $item['deal_id'], $item['data'] );
			if ( $max_quantity == Group_Buying_Account::NO_MAXIMUM ) {
				$max_quantity = round( $item['quantity']+10, -1 );
			}
			if ( !is_object( $deal ) || !$deal->is_open() || $max_quantity < 1 ) {
				$cart = Group_Buying_Cart::get_instance();
				$cart->remove_item( $item['deal_id'], $item['data'] );
			} else {
				$price = $deal->get_price( NULL, $item['data'] )*$item['quantity'];
				$row = array(
					'remove' => sprintf( '<input type="checkbox" value="remove" name="items[%d][remove]" />', $key ),
					'name' => '<a href="'.get_permalink( $deal->get_ID() ).'">'.$deal->get_title( $item['data'] ).'</a>',
					'quantity' => $static ? $item['quantity']: gb_get_quantity_select( '1', $max_quantity, $item['quantity'], 'items['.$key.'][qty]' ),
					'price' => gb_get_formatted_money( $price ),
				);
				if ( $static ) {
					unset( $row['remove'] );
				} else {
					$row['name'] .= sprintf( '<input type="hidden" value="%s" name="items[%d][id]" />', $item['deal_id'], $key );
					$row['name'] .= sprintf( '<input type="hidden" value="%s" name="items[%d][data]" />', $item['data']?esc_attr( serialize( $item['data'] ) ):'', $key );
				}
				$items[] = $row;
			}
		}
		$line_items = array(
			'subtotal' => array(
				'label' => self::__( 'Subtotal' ),
				'data' => gb_get_formatted_money( $cart->get_subtotal() ),
				'weight' => 10,
			),
			'total' => array(
				'label' => self::__( 'Total' ),
				'data' => gb_get_formatted_money( $cart->get_total() ),
				'weight' => 1000,
			),
		);
		$line_items = apply_filters( 'gb_cart_line_items', $line_items, $cart );
		uasort( $line_items, array( get_class(), 'sort_by_weight' ) );

		$controls = array(
			'update' => '<input type="submit" class="form-submit" value="'.self::__( 'Update' ).'" name="gb_cart_action-update" />',
			'checkout' => '<input type="submit" class="form-submit alignright checkout_next_step" value="'.self::__( 'Checkout' ).'" name="gb_cart_action-checkout" />',
		);
		return array(
			'columns' => apply_filters( 'gb_cart_columns', $columns, $cart ),
			'items' => apply_filters( 'gb_cart_items', $items, $cart ),
			'line_items' => $line_items, // filtered above
			'cart_controls' => apply_filters( 'gb_cart_controls', $controls, $cart ),
		);
	}

	/**
	 * Filter 'the_title' to display the title of the cart rather than the user name
	 *
	 * @static
	 * @param string  $title
	 * @return string
	 */
	public static function get_title( $title ) {
		$user = wp_get_current_user();
		if ( $user && $user->ID ) {
			if ( $user->display_name ) {
				$name = $user->display_name;
			} else {
				$name = $user->user_login;
			}
			return sprintf( self::__( "%s&rsquo;s Cart" ), $name );
		} else {
			return self::__( "Your Cart" );
		}
	}
}
