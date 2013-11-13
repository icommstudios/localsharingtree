<?php

/**
 * Payment Controller
 *
 * @package GBS
 * @subpackage Payment
 */
class Group_Buying_Payments extends Group_Buying_Controller {
	protected static $settings_page;

	public static function init() {
		//add_action('admin_menu', array(get_class(), 'payments_menu'),10);
		self::$settings_page = self::register_settings_page( 'payment_records', self::__( 'Payment Records' ), self::__( 'Payments' ), 8, FALSE, 'records', array( get_class(), 'display_table' ) );

		add_filter( 'views_group-buying_page_group-buying/payment_records', array( get_class(), 'modify_views' ) );
	}

	public function modify_views( $views ) {
		$auth_class = ( isset( $_GET['post_status'] ) && $_GET['post_status'] == Group_Buying_Payment::STATUS_AUTHORIZED ) ? 'class="current"' : '';
		$views['authorized_payments'] = '<a href="'.add_query_arg( array( 'post_status' => Group_Buying_Payment::STATUS_AUTHORIZED ) ).'" '.$auth_class.'>'.self::__('Authorized/Temp').'</a>';

		$void_class = ( isset( $_GET['post_status'] ) && $_GET['post_status'] == Group_Buying_Payment::STATUS_VOID ) ? 'class="current"' : '';
		$views['voided_payments'] = '<a href="'.add_query_arg( array( 'post_status' => Group_Buying_Payment::STATUS_VOID ) ).'" '.$void_class.'>'.self::__('Voided').'</a>';

		$refund_class = ( isset( $_GET['post_status'] ) && $_GET['post_status'] == Group_Buying_Payment::STATUS_REFUND ) ? 'class="current"' : '';
		$views['refunded_payments'] = '<a href="'.add_query_arg( array( 'post_status' => Group_Buying_Payment::STATUS_REFUND ) ).'" '.$refund_class.'>'.self::__('Refunded').'</a>';
		return $views;
	}

	public static function display_table() {
		add_thickbox();
		//Create an instance of our package class...
		$wp_list_table = new Group_Buying_Payments_Table();
		//Fetch, prepare, sort, and filter our data...
		$wp_list_table->prepare_items();

		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				jQuery(".gb_void_payment").on('click', function(event) {
					event.preventDefault();
					var $void_button = $( this ),
					void_payment_id = $void_button.attr( 'ref' ),
					notes_form = $( '#transaction_data_' + void_payment_id ).val();
					$void_button.html("<?php gb_e('Working...') ?>");
					$.post( ajaxurl, { action: 'gbs_void_payment', payment_id: void_payment_id, notes: notes_form, void_payment_nonce: '<?php echo wp_create_nonce( Group_Buying_Destroy::NONCE ) ?>' },
						function( data ) {
							self.parent.tb_remove();
							$('#void_link_'+void_payment_id).closest('tr').fadeOut('slow');
						}
					);
				});

				jQuery(".gb_attempt_capture").on('click', function(event) {
					event.preventDefault();
					if( confirm( '<?php gb_e( "Are you sure? This will force a capture attempt on this payment (a partial payment will be captured if the payment is mixed with untipped items). Note: the payment processor for this payment must support the gb_manually_capture_purchase method." ) ?>' ) ) {
						var $capture_link = $( this ),
						capture_payment_id = $capture_link.attr( 'ref' );
						$capture_link.html('<?php gb_e("Working...") ?>');
						$.post( ajaxurl, { action: 'gb_manually_capture_payment', payment_id: capture_payment_id, capture_payment_nonce: '<?php echo wp_create_nonce( Group_Buying_Payment_Processors::AJAX_NONCE ) ?>' },
							function( data ) {
								window.location = window.location.pathname + "?page=group-buying%2Fpayment_records&s=" + escape( capture_payment_id );
							}
						);
					}
				});
			});
		</script>
		<style type="text/css">
			#payment_deal_id-search-input, #payment_id-search-input, #payment_purchase_id-search-input, #payment_account_id-search-input { width:5em; margin-left: 10px;}
		</style>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php self::display_admin_tabs(); ?>
			</h2>

			 <?php $wp_list_table->views() ?>
			<form id="payments-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $wp_list_table->search_box( self::__( 'Payment ID' ), 'payment_id' ); ?>
				<p class="search-box deal_search">
					<label class="screen-reader-text" for="payment_deal_id-search-input"><?php self::_e( 'Deal ID:' ) ?></label>
					<input type="text" id="payment_deal_id-search-input" name="deal_id" value="">

					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Deal ID' ) ?>">
				</p>
				<p class="search-box purchase_search">

					<label class="screen-reader-text" for="payment_purchase_id-search-input"><?php self::_e( 'Purchase ID:' ) ?></label>
					<input type="text" id="payment_purchase_id-search-input" name="purchase_id" value="">

					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Purchase ID' ) ?>">
				</p>
				<p class="search-box account_search">

					<label class="screen-reader-text" for="payment_account_id-search-input"><?php self::_e( 'Account ID:' ) ?></label>
					<input type="text" id="payment_account_id-search-input" name="account_id" value="">

					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Account ID' ) ?>">
				</p>
				<?php $wp_list_table->display() ?>
			</form>
		</div>
		<?php
	}

}