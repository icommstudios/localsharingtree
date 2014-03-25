<?php

/**
 * Vouchers controller
 *
 * @package GBS
 * @subpackage Voucher
 */
class Group_Buying_Vouchers extends Group_Buying_Controller {
	const SETTINGS_PAGE = 'voucher_records';
	const FILTER_QUERY_VAR = 'filter_gb_vouchers';
	const FILTER_EXPIRED_QUERY_VAR = 'expired';
	const FILTER_USED_QUERY_VAR = 'used';
	const FILTER_ACTIVE_QUERY_VAR = 'active';
	const VOUCHER_OPTION_EXP_PATH = 'gb_voucher_path_expired';
	const VOUCHER_OPTION_USED_PATH = 'gb_voucher_path_used';
	const VOUCHER_OPTION_ACTIVE_PATH = 'gb_voucher_path_active';
	const VOUCHER_OPTION_LOGO = 'gb_voucher_logo';
	const VOUCHER_OPTION_FINE_PRINT = 'gb_voucher_fine_print';
	const VOUCHER_OPTION_SUPPORT1 = 'gb_voucher_support_1';
	const VOUCHER_OPTION_SUPPORT2 = 'gb_voucher_support_2';
	const VOUCHER_OPTION_LEGAL = 'gb_voucher_legal';
	const VOUCHER_OPTION_PREFIX = 'gb_voucher_prefix';
	const VOUCHER_OPTION_IDS = 'gb_voucher_ids_options';
	private static $expired_path;
	private static $used_path;
	private static $active_path;
	private static $voucher_logo;
	private static $voucher_fine_print;
	private static $voucher_support1;
	private static $voucher_support2;
	private static $voucher_legal;
	private static $voucher_prefix;
	private static $voucher_ids_option;

	protected static $settings_page;

	public static function init() {
		add_action( 'payment_captured', array( get_class(), 'activate_vouchers' ), 10, 2 );
		add_action( 'purchase_completed', array( get_class(), 'create_vouchers_for_purchase' ), 5, 1 );
		add_filter( 'template_include', array( get_class(), 'override_template' ) );

		self::$expired_path = get_option( self::VOUCHER_OPTION_EXP_PATH, self::FILTER_EXPIRED_QUERY_VAR );
		self::$used_path = get_option( self::VOUCHER_OPTION_USED_PATH, self::FILTER_USED_QUERY_VAR );
		self::$active_path = get_option( self::VOUCHER_OPTION_ACTIVE_PATH, self::FILTER_ACTIVE_QUERY_VAR );

		add_action( 'generate_rewrite_rules', array( get_class(), 'add_voucher_rewrite_rules' ), 10, 1 );
		self::register_query_var( self::FILTER_QUERY_VAR );
		
		add_action( 'pre_get_posts', array( get_class(), 'filter_voucher_query' ), 50, 1 );
		add_action( 'parse_query', array( get_class(), 'filter_voucher_query' ), 50, 1 );

		if ( is_admin() ) {
			add_action( 'parse_request', array( get_class(), 'manually_activate_vouchers' ), 1, 0 );
		}

		self::$voucher_logo = get_option( self::VOUCHER_OPTION_LOGO );
		self::$voucher_fine_print = get_option( self::VOUCHER_OPTION_FINE_PRINT );
		self::$voucher_support1 = get_option( self::VOUCHER_OPTION_SUPPORT1 );
		self::$voucher_support2 = get_option( self::VOUCHER_OPTION_SUPPORT2 );
		self::$voucher_legal = get_option( self::VOUCHER_OPTION_LEGAL );
		self::$voucher_prefix = get_option( self::VOUCHER_OPTION_PREFIX );
		self::$voucher_ids_option = get_option( self::VOUCHER_OPTION_IDS, 'random' );
		self::register_settings();

		// AJAX Functions
		add_action( 'wp_ajax_gb_mark_voucher', array( get_class(), 'mark_voucher' ) );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		
		// Option page
		$args = array(
			'slug' => self::SETTINGS_PAGE,
			'title' => self::__( 'Vouchers' ),
			'menu_title' => self::__( 'Vouchers' ),
			'weight' => 16,
			'reset' => FALSE, 
			'section' => 'records',
			'callback' => array( get_class(), 'display_table' )
			);
		do_action( 'gb_settings_page', $args );

		// Settings
		$settings = array(

			'gb_url_path_vouchers' => array(
				'weight' => 190,
				'settings' => array(
					self::VOUCHER_OPTION_EXP_PATH => array(
						'label' => self::__( 'Expired Voucher Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ).'vouchers/', // TODO use archive link (doesn't work)
							'type' => 'text',
							'default' => self::$expired_path
							)
						),
					self::VOUCHER_OPTION_USED_PATH => array(
						'label' => self::__( 'Used Voucher Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ).'vouchers/', // TODO use archive link (doesn't work)
							'type' => 'text',
							'default' => self::$used_path
							)
						),
					self::VOUCHER_OPTION_ACTIVE_PATH => array(
						'label' => self::__( 'Active Voucher Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ).'vouchers/', // TODO use archive link (doesn't work)
							'type' => 'text',
							'default' => self::$active_path
							)
						)
					)
				),
			'gb_general_voucher_settings' => array(
				'title' => self::__( 'Voucher Settings' ),
				'weight' => 250,
				'settings' => array(
					self::VOUCHER_OPTION_LOGO => array(
						'label' => self::__( 'Voucher Logo' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$voucher_logo
							)
						),
					self::VOUCHER_OPTION_FINE_PRINT => array(
						'label' => self::__( 'Voucher Fine Print' ),
						'option' => array(
							'type' => 'textarea',
							'default' => self::$voucher_fine_print
							)
						),
					self::VOUCHER_OPTION_SUPPORT1 => array(
						'label' => self::__( 'Voucher Support Contact' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$voucher_support1
							)
						),
					self::VOUCHER_OPTION_SUPPORT2 => array(
						'label' => self::__( 'Voucher Support Contact' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$voucher_support2
							)
						),
					self::VOUCHER_OPTION_LEGAL => array(
						'label' => self::__( 'Voucher Legal Info' ),
						'option' => array(
							'type' => 'textarea',
							'default' => self::$voucher_legal
							)
						),
					self::VOUCHER_OPTION_PREFIX => array(
						'label' => self::__( 'Voucher Voucher Prefix' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$voucher_prefix
							)
						),
					/*/
					self::VOUCHER_OPTION_IDS => array(
						'label' => self::__( 'Voucher IDs' ),
						'option' => array(
							'type' => 'select',
							'options' => array(
								'random' => self::__('Random'),
								'sequential' => self::__('Sequential'),
								'none' => self::__('None'),
								),
							'default' => self::$voucher_ids_option
							)
						)
					/**/
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );
	}

	/**
	 * A purchase has been completed. Create all the necessary vouchers
	 * and tie them to that purchase
	 *
	 * @static
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public static function create_vouchers_for_purchase( Group_Buying_Purchase $purchase ) {
		$products = apply_filters( 'gb_create_vouchers_for_purchase_products', $purchase->get_products(), $purchase );
		foreach ( $products as $product ) {
			$deal = Group_Buying_Deal::get_instance( $product['deal_id'] );
			if ( !$deal ) {
				self::set_message( sprintf( self::__( 'We experienced an error creating a voucher for deal ID %d. Please contact a site administrator for asssistance.' ), $product['deal_id'] ) );
				continue; // nothing else we can do
			}
			for ( $i = 0 ; $i < $product['quantity'] ; $i++ ) {
				$voucher_id = Group_Buying_Voucher::new_voucher( $purchase->get_id(), $product['deal_id'] );
				$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
				$voucher->set_product_data( $product );
				$voucher->set_serial_number();
				$voucher->set_security_code();
				do_action( 'create_voucher_for_purchase', $voucher_id, $purchase, $product );
			}
		}
	}

	public static function override_template( $template ) {
		if ( Group_Buying_Voucher::is_voucher_query() ) {

			// require login unless it's a validated temp access
			if ( !Group_Buying_Voucher::temp_voucher_access_attempt() ) {
				self::login_required();
			}

			if ( is_single() ) {
				$template = self::locate_template( array(
						'account/voucher.php',
						'vouchers/single-voucher.php',
						'vouchers/single.php',
						'vouchers/voucher.php',
						'voucher.php',
					), $template );
			} else {
				$status = get_query_var( self::FILTER_QUERY_VAR );
				$template = self::locate_template( array(
						'account/'.$status.'-vouchers.php',
						'vouchers/'.$status.'-vouchers.php',
						'vouchers/'.$status.'.php',
						'account/vouchers.php',
						'vouchers/vouchers.php',
						'vouchers/index.php',
						'vouchers/archive.php',
						'vouchers.php',
					), $template );
			}
		}
		return $template;
	}

	/**
	 * Add the rewrite rules for filtering vouchers
	 *
	 * @param array   $vars
	 * @return array
	 */
	public function add_voucher_rewrite_rules( $wp_rewrite ) {
		$new_rules = array();
		$new_rules[trailingslashit( Group_Buying_Voucher::REWRITE_SLUG ).self::$expired_path.'(/page/?([0-9]{1,}))?/?$'] = 'index.php?post_type='.Group_Buying_Voucher::POST_TYPE.'&paged='.$wp_rewrite->preg_index( 2 ).'&'.self::FILTER_QUERY_VAR.'='.self::FILTER_EXPIRED_QUERY_VAR;
		$new_rules[trailingslashit( Group_Buying_Voucher::REWRITE_SLUG ).self::$used_path.'(/page/?([0-9]{1,}))?/?$'] = 'index.php?post_type='.Group_Buying_Voucher::POST_TYPE.'&paged='.$wp_rewrite->preg_index( 2 ).'&'.self::FILTER_QUERY_VAR.'='.self::FILTER_USED_QUERY_VAR;
		$new_rules[trailingslashit( Group_Buying_Voucher::REWRITE_SLUG ).self::$active_path.'(/page/?([0-9]{1,}))?/?$'] = 'index.php?post_type='.Group_Buying_Voucher::POST_TYPE.'&paged='.$wp_rewrite->preg_index( 2 ).'&'.self::FILTER_QUERY_VAR.'='.self::FILTER_ACTIVE_QUERY_VAR;
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}


	/**
	 * Edit the query to remove other users vouchers
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public static function filter_voucher_query( WP_Query $query ) {
		
		// we only care if this is the query for vouchers
		if ( $query->is_main_query() && Group_Buying_Voucher::is_voucher_query( $query ) && !is_admin() && get_query_var( self::FILTER_QUERY_VAR ) && !isset( $query->query_vars['gb_bypass_filter'] ) ) {
			
			if ( get_query_var( self::FILTER_QUERY_VAR ) == self::FILTER_USED_QUERY_VAR ) {
				if ( !isset( $query->query_vars['meta_query'] ) || !is_array( $query->query_vars['meta_query'] ) ) {
					$query->query_vars['meta_query'] = array();
				}
				$query->query_vars['meta_query'][] = array(
					'key' => '_claimed',
					'value' => 0,
					'compare' => '>'
				);
			}
			if ( get_query_var( self::FILTER_ACTIVE_QUERY_VAR ) == self::FILTER_ACTIVE_QUERY_VAR ) {
				if ( !isset( $query->query_vars['meta_query'] ) || !is_array( $query->query_vars['meta_query'] ) ) {
					$query->query_vars['meta_query'] = array();
				}
				$query->query_vars['meta_query'][] = array(
					'key' => '_claimed',
					'compare' => 'NOT EXISTS',
				);
			}
			if (
				get_query_var( self::FILTER_QUERY_VAR ) == self::FILTER_EXPIRED_QUERY_VAR
				|| get_query_var( self::FILTER_QUERY_VAR ) == self::FILTER_ACTIVE_QUERY_VAR
			) {
				// TODO needs some SQL love so non expired vouchers are returned.
				// get all the user's purchases
				$purchases = Group_Buying_Purchase::get_purchases( array(
						'user' => get_current_user_id(),
					) );
				if ( $purchases ) {
					$args = array(
						'post_type' => Group_Buying_Voucher::POST_TYPE,
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'fields' => 'ids',
						'gb_bypass_filter' => TRUE,
						'meta_query' => array(
							'key' => '_purchase_id',
							'value' => $purchases,
							'compare' => 'IN',
							'type' => 'NUMERIC',
						)
					);
					$vouchers = new WP_Query( $args );
					$filtered_vouchers = array();
					foreach ( $vouchers->posts as $voucher_id ) {
						$deal_id = get_post_meta( $voucher_id, '_voucher_deal_id', TRUE );
						if ( !in_array( $voucher_id, $filtered_vouchers ) ) {
							// If expired query remove the expired vouchers, plus those without exp
							if ( get_query_var( self::FILTER_QUERY_VAR ) == self::FILTER_EXPIRED_QUERY_VAR ) {
								$exp = get_post_meta( $deal_id, '_voucher_expiration_date', TRUE );
								if ( $exp && current_time( 'timestamp' ) > $exp ) { // expired
									$filtered_vouchers[] = $voucher_id;
								}
							}
							// If active query removed expired vouchers, keep those without exp
							elseif ( get_query_var( self::FILTER_QUERY_VAR ) == self::FILTER_ACTIVE_QUERY_VAR ) {
								$exp = get_post_meta( $deal_id, '_voucher_expiration_date', TRUE );
								if ( !$exp || current_time( 'timestamp' ) < $exp ) { // not expired
									$filtered_vouchers[] = $voucher_id;
								}
							} else {
								$filtered_vouchers[] = $voucher_id;
							}
						}
					}
					$query->query_vars['post__in'] = $filtered_vouchers;

				}
			}

		}
	}

	/**
	 * Get the deal IDs from a user's vouchers.
	 *
	 * @param string  $status 'any' all deal ids, 'used' all deals with claimed vouchers, 'active' all deals with unclaimed vouchers
	 * @return string
	 */
	public static function get_deal_ids( $status = NULL ) {
		// This could possibly be done more efficiently in a single SQL query, but that's an
		// optimization for a later date. All the vouchers and deals will likely be loaded on this
		// page, anyway, so it's probably not costing us much extra doing it this way.

		// self::filter_voucher_query() should filter out vouchers that don't belong to the current user
		$args = array(
			'post_type' => Group_Buying_Voucher::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		$deal_ids = array();
		$status = ( NULL === $status && !get_query_var( Group_Buying_Vouchers::FILTER_QUERY_VAR ) ) ? 'any' : get_query_var( Group_Buying_Vouchers::FILTER_QUERY_VAR );
		foreach ( get_posts( $args ) as $voucher_id ) {
			$deal_id = get_post_meta( $voucher_id, '_voucher_deal_id', TRUE );
			if ( !in_array( $deal_id, $deal_ids ) ) {
				$claimed = get_post_meta( $voucher_id, '_claimed', TRUE );
				$exp = get_post_meta( $deal_id, '_voucher_expiration_date', TRUE );
				if ( $status == self::FILTER_USED_QUERY_VAR ) {
					if ( current_time( 'timestamp' ) < $exp || $claimed ) { // Returning expired or claimed vouchers
						$deal_ids[] = $deal_id;
					}
				}
				elseif ( $status == self::FILTER_EXPIRED_QUERY_VAR ) {
					if ( !empty( $exp ) && current_time( 'timestamp' ) > $exp ) { // Returning expired vouchers
						$deal_ids[] = $deal_id;
					}
				}
				elseif ( $status == self::FILTER_ACTIVE_QUERY_VAR ) { // return all non-expired deals if active query
					if ( !$claimed ) { // Don't included claimed vouchers
						if ( empty( $exp ) || ( !empty( $exp ) && current_time( 'timestamp' ) < $exp ) ) {
							$deal_ids[] = $deal_id;
						}
					}
				} else {
					$deal_ids[] = $deal_id;
				}
			}
		}
		return $deal_ids;
	}

	/**
	 * Add the filter query var
	 *
	 * @param array   $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		array_push( $vars, self::FILTER_QUERY_VAR );
		return $vars;
	}


	public static function get_url() {
		return get_post_type_archive_link( Group_Buying_Voucher::POST_TYPE );
	}

	public static function get_active_url() {
		return get_post_type_archive_link( Group_Buying_Voucher::POST_TYPE ).self::$active_path;
	}

	public static function get_expired_url() {
		return get_post_type_archive_link( Group_Buying_Voucher::POST_TYPE ).self::$expired_path;
	}

	public static function get_used_url() {
		return get_post_type_archive_link( Group_Buying_Voucher::POST_TYPE ).self::$used_path;
	}

	/**
	 * Activate any pending vouchers if the purchased deal is now successful
	 *
	 * @static
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public static function activate_vouchers( Group_Buying_Payment $payment, $items_captured ) {
		$purchase_id = $payment->get_purchase();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		$products = $purchase->get_products();
		foreach ( $products as $product ) {
			/*/
			if ( count( $product['payment_method'] ) > 1 ) { // Check to see if the item payment is split
				foreach ( $product['payment_method'] as $payment_method ) {
					// TODO! get instance of each payment associated with this deal
					// Confirm all payments are complete before activating the voucher
					if ( $payment->get_status() !== Group_Buying_Payment::STATUS_COMPLETE ) {
						continue;
					}
				}
			}
			/**/
			if ( in_array( $product['deal_id'], $items_captured ) ) {
				$deal = Group_Buying_Deal::get_instance( $product['deal_id'] );
				if ( $deal->is_successful() ) {
					$vouchers = Group_Buying_Voucher::get_pending_vouchers( $product['deal_id'], $purchase_id );
					foreach ( $vouchers as $voucher_id ) {
						$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
						$voucher->activate();
					}
				}
			}
		}
	}

	public static function manually_activate_vouchers( $voucher_id = null ) {
		if ( !current_user_can( 'edit_posts' ) ) {
			return; // security check
		}
		if ( isset( $_REQUEST['activate_voucher'] ) && $_REQUEST['activate_voucher'] != '' ) {
			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'activate_voucher' ) ) {
				$voucher_id = $_REQUEST['activate_voucher'];
			}
		}
		if ( is_numeric( $voucher_id ) ) {
			$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
			if ( is_a( $voucher, 'Group_Buying_Voucher' ) && !$voucher->is_active() ) {
				$voucher->activate();
				return;
			}
		}
	}

	public static function mark_voucher() {

		if ( isset( $_REQUEST['voucher_id'] ) && $_REQUEST['voucher_id'] ) {
			$voucher = Group_Buying_Voucher::get_instance( $_REQUEST['voucher_id'] );
			// If destroying claim date
			if ( isset( $_REQUEST['unmark_voucher'] ) && $_REQUEST['unmark_voucher'] ) {
				$marked = $voucher->set_claimed_date( TRUE );
				gb_e( 'Voucher Claim Date Removed.' );
				exit();
			}

			$marked = $voucher->set_claimed_date();
			$data = array(
				'date' => date( get_option( 'date_format' ), current_time( 'timestamp', 1 ) ),
				'notes' => gb__( 'Customer marked voucher as redeemed.' )
			);
			$voucher->set_redemption_data( $data );
			echo apply_filters( 'gb_ajax_mark_voucher', date( get_option( 'date_format' ), $marked ) );
			exit();
		}
		exit();
	}

	public static function get_voucher_logo() {
		return self::$voucher_logo;
	}
	public static function get_voucher_fine_print() {
		return self::$voucher_fine_print;
	}
	public static function get_voucher_support1() {
		return self::$voucher_support1;
	}
	public static function get_voucher_support2() {
		return self::$voucher_support2;
	}
	public static function get_voucher_legal() {
		return self::$voucher_legal;
	}
	public static function get_voucher_prefix() {
		return self::$voucher_prefix;
	}
	public static function get_voucher_option_ids() {
		return self::$voucher_ids_option;
	}

	public static function display_table() {
		//Create an instance of our package class...
		$wp_list_table = new Group_Buying_Vouchers_Table();
		//Fetch, prepare, sort, and filter our data...
		$wp_list_table->prepare_items();

		?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function($){
				jQuery(".gb_activate").on('click', function(event) {
					event.preventDefault();
						if( confirm( '<?php gb_e( "Are you sure? This will make the voucher immediately available for download." ) ?>' ) ){
							var $link = $( this ),
							voucher_id = $link.attr( 'ref' );
							url = $link.attr( 'href' );
							$( "#"+voucher_id+"_activate" ).fadeOut('slow');
							$.post( url, { activate_voucher: voucher_id },
								function( data ) {
										$( "#"+voucher_id+"_activate_result" ).append( '<?php self::_e( 'Activated' ) ?>' ).fadeIn();
									}
								);
						} else {
							// nothing to do.
						}
				});
				jQuery(".gb_deactivate").on('click', function(event) {
					event.preventDefault();
						if( confirm( '<?php gb_e( "Are you sure? This will immediately remove the voucher from customer access." ) ?>' ) ) {
							var $deactivate_button = $( this ),
							deactivate_voucher_id = $deactivate_button.attr( 'ref' );
							$( "#"+deactivate_voucher_id+"_deactivate" ).fadeOut('slow');
							$.post( ajaxurl, { action: 'gbs_deactivate_voucher', voucher_id: deactivate_voucher_id, deactivate_voucher_nonce: '<?php echo wp_create_nonce( Group_Buying_Destroy::NONCE ) ?>' },
								function( data ) {
										$( "#"+deactivate_voucher_id+"_deactivate_result" ).append( '<?php self::_e( 'Deactivated' ) ?>' ).fadeIn();
									}
								);
						} else {
							// nothing to do.
						}
				});
				jQuery(".gb_destroy").on('click', function(event) {
					event.preventDefault();
						if( confirm( '<?php gb_e( "This will permanently destroy the voucher from the database and remove records of it’s existence from the related purchase and payment(s) which cannot be reversed. This will not reverse any payments or provide a credit to the customer, that must be done manually. Are you sure?" ) ?>' ) ) {
							var $destroy_link = $( this ),
							destroy_voucher_id = $destroy_link.attr( 'ref' );
							$.post( ajaxurl, { action: 'gbs_destroyer', type: 'voucher', id: destroy_voucher_id, destroyer_nonce: '<?php echo wp_create_nonce( Group_Buying_Destroy::NONCE ) ?>' },
								function( data ) {
										$destroy_link.parent().parent().parent().parent().fadeOut();
									}
								);
						} else {
							// nothing to do.
						}
				});
				jQuery(".gb_unclaim").on('click', function(event) {
					event.preventDefault();
						if(confirm("Are you sure?")){
							var $unclaim_button = $( this ),
							unclaim_voucher_id = $unclaim_button.attr( 'ref' );
							$( "#"+unclaim_voucher_id+"_unclaim" ).fadeOut('slow');
							$.post( ajaxurl, { action: 'gb_mark_voucher', voucher_id: unclaim_voucher_id, unmark_voucher: 1 },
								function( data ) {
										$( "#"+unclaim_voucher_id+"_unclaim_result" ).append( '<?php gb_e( "Voucher Redemption Removed." ) ?>' ).fadeIn();
									}
								);
						} else {
							// nothing to do.
						}
				});
			});
		</script>
		<style type="text/css">
			#voucher_deal_id-search-input, #voucher_purchase_id-search-input, #voucher_account_id-search-input, #voucher_id-search-input { width:5em; margin-left: 10px;}
		</style>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php do_action( 'gb_settings_tabs' ); ?>
			</h2>

			 <?php $wp_list_table->views() ?>
			<form id="payments-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $wp_list_table->search_box( self::__( 'Voucher ID' ), 'voucher_id' ); ?>
				<p class="search-box deal_search">
					<label class="screen-reader-text" for="voucher_deal_id-search-input"><?php self::_e( 'Deal ID:' ) ?></label>
					<input type="text" id="voucher_deal_id-search-input" name="deal_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Deal ID' ) ?>">
				</p>
				<p class="search-box purchase_search">
					<label class="screen-reader-text" for="voucher_purchase_id-search-input"><?php self::_e( 'Order ID:' ) ?></label>
					<input type="text" id="voucher_purchase_id-search-input" name="purchase_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Order ID' ) ?>">
				</p>
				<p class="search-box account_search">
					<label class="screen-reader-text" for="voucher_account_id-search-input"><?php self::_e( 'Account ID:' ) ?></label>
					<input type="text" id="voucher_account_id-search-input" name="account_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Account ID' ) ?>">
				</p>
				<?php $wp_list_table->display() ?>
			</form>
		</div>
		<?php
	}
}