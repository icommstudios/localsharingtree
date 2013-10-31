<?php



if ( !class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Group_Buying_Payments_Table extends WP_List_Table {
	protected static $post_type = Group_Buying_Payment::POST_TYPE;

	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
				'singular' => 'payment',     // singular name of the listed records
				'plural' => 'payments', // plural name of the listed records
				'ajax' => false     // does this table support ajax?
			) );

	}

	function get_views() {

		$status_links = array();
		$num_posts = wp_count_posts( self::$post_type, 'readable' );
		$class = '';
		$allposts = '';

		$total_posts = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state )
			$total_posts -= $num_posts->$state;

		$class = empty( $_REQUEST['post_status'] ) ? ' class="current"' : '';
		$status_links['all'] = "<a href='admin.php?page=group-buying/payment_records{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset( $_REQUEST['post_status'] ) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			// replace "Published" with "Complete".
			$label = str_replace( 'Published', 'Complete', translate_nooped_plural( $status->label_count, $num_posts->$status_name ) );
			$status_links[$status_name] = "<a href='admin.php?page=group-buying/payment_records&post_status=$status_name'$class>" . sprintf( $label, number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}

	function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions"> <?php
		if ( 'top' == $which && !is_singular() ) {

			$this->months_dropdown( self::$post_type );

			submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
		} ?>
		<a href="<?php gb_credit_purchases_report_url() ?>"  class="button"><?php gb_e('Credit Payments') ?></a>
		</div>
		<?php
	}


	/**
	 *
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @param array   $item        A singular item (one full row's worth of data)
	 * @param array   $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		default:
			return apply_filters( 'gb_mngt_payments_column_'.$column_name, $item ); // do action for those columns that are filtered in
		}
	}


	/**
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array   $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 */
	function column_title( $item ) {
		$payment = Group_Buying_Payment::get_instance( $item->ID );	
		$purchase_id = $payment->get_purchase();

		//Build row actions
		$account = $payment->get_account();
		if ( is_a( $account, 'Group_Buying_Account' ) ) { // Check if purchase wasn't deleted
			$actions = array(
				'order'    => sprintf( '<a href="admin.php?page=group-buying/purchase_records&s=%s">Order</a>', $purchase_id ),
				'account'  => sprintf( '<a href="post.php?post=%s&action=edit">'.gb__( 'Account' ).'</a>', $account->get_ID() ),
			);
		} else {
			$actions = array(
				'error'    => gb__('Associated records cannot be found.'),
			);
		}

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(order&nbsp;id:%2$s)</span>%3$s',
			$item->post_title,
			$purchase_id,
			$this->row_actions( $actions )
		);
	}

	function column_total( $item ) {
		$payment = Group_Buying_Payment::get_instance( $item->ID );
		$payment_method = $payment->get_payment_method();
		$deals = $payment->get_deals();
		$purchase_id = $payment->get_purchase();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		if ( !$purchase ) {
			return;
		}

		if ( empty( $deals ) ) {
			echo '<em>'.gb__( 'Order Total' ).': '.gb_get_formatted_money( $purchase->get_total() ).'</em>';
			return;
		}

		// Find the total via calculated costs per item within the payment, since shipping/tax and other extras are added to these totals and not the payment->amount.
		if ( $payment_method == Group_Buying_Affiliate_Credit_Payments::PAYMENT_METHOD ) { // For some reason Credits are handled differently. TODO fix that
			$total = $payment->get_amount();
		} else {
			$total = 0;
			foreach ( $deals as $deal_id => $items ) {
				foreach ( $items as $item ) {
					foreach ( $item['payment_method'] as $method => $payment ) {
						if ( $method == $payment_method ) {
							$total += $payment;
						}
					}
				}
			}
		}

		echo gb__( 'Subtotal' ).': '.gb_get_formatted_money( $purchase->get_subtotal() ).'<br/>';
		echo gb__( 'Shipping' ).': '.gb_get_formatted_money( $purchase->get_shipping_total() ).'<br/>';
		echo gb__( 'Tax' ).': '.gb_get_formatted_money( $purchase->get_tax_total() ).'<br/>';
		echo '<strong>'.gb__( 'Payment Total' ).':</strong> '.gb_get_formatted_money( $total ).'<br/>';
		echo '<em>'.gb__( 'Order Total' ).': '.gb_get_formatted_money( $purchase->get_total() ).'</em>';
	}

	function column_account( $item ) {
		$payment = Group_Buying_Payment::get_instance( $item->ID );
		$account = $payment->get_account();
		if ( !is_a( $account, 'Group_Buying_Account' ) ) {
			return;
		}
		//Build row actions
		$actions = array(
			'account'    => sprintf( '<a href="post.php?post=%s&action=edit">'.gb__( 'Account' ).'</a>', $account->get_ID() ),
			'user'    => sprintf( '<a href="user-edit.php?user_id=%s">'.gb__( 'User' ).'</a>', Group_Buying_Account::get_user_id_for_account( $account->get_ID() ) ),
		);

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			$account->get_name(),
			$account->get_ID(),
			$this->row_actions( $actions )
		);
	}

	function column_deals( $item ) {
		$payment = Group_Buying_Payment::get_instance( $item->ID );
		$deals = $payment->get_deals();
		if ( empty( $deals ) ) {
			gb_e( 'No Data.' );
			return;
		}
		$i = 0;
		foreach ( $deals as $deal_id => $items ) {
			foreach ( $items as $item ) {
				$i++;
				// Display deal name and link
				echo '<p><strong><a href="'.get_edit_post_link( $item['deal_id'] ).'">'.get_the_title( $item['deal_id'] ).'</a></strong>&nbsp;<span style="color:silver">(id:'.$item['deal_id'].')</span>';
				// Build details
				$details = array(
					'Quantity' => $item['quantity'],
					'Unit Price' => gb_get_formatted_money( $item['unit_price'] ),
					'Total' => gb_get_formatted_money( $item['price'] )
				);
				// Filter to add attributes, etc.
				$details = apply_filters( 'gb_purchase_deal_column_details', $details, $item, $items );
				// display details
				foreach ( $details as $label => $value ) {
					echo '<br />&nbsp;&nbsp;'.$label.': '.$value;
				}
				// Build Payment methods
				$payment_methods = array();
				foreach ( $item['payment_method'] as $method => $payment ) {
					$payment_methods[] .= $method.' &mdash; '.gb_get_formatted_money( $payment );
				}
				// Display payment methods
				echo '<br/><strong>'.gb__( 'Payment Methods' ).':</strong><br/>';
				echo implode( '<br/>', $payment_methods );
				echo '</p>';
				if ( ( count( $items )+count( $deals )-1 ) > $i ) {
					echo '<span class="meta_box_block_divider"></span>';
				}
			}
		}
		return;

	}

	function column_data( $item ) {
		$payment_id = $item->ID;
		$payment = Group_Buying_Payment::get_instance( $payment_id );
		$method = $payment->get_payment_method();
		//Build row actions
		$actions = array(
			'detail'    => sprintf( '<a href="#TB_inline?width=900&height=600&inlineId=data_id_%s" class="show_payment_detail thickbox" id="%s" title="'.gb__( 'Transaction Data' ).'">'.gb__( 'Transaction Data' ).'</a><div id="data_id_%s" style="display:none;"><pre id="payment_detail_%s" style="width="500px"; white-space:pre-wrap; text-align: left; font: normal normal 11px/1.4 menlo, monaco, monospaced; padding: 5px;">%s</pre></div>', $payment_id, $payment_id, $payment_id, $payment_id, print_r( $payment->get_data(), TRUE ) )
		);

		//Return the title contents
		return sprintf( '%1$s %2$s', $method, $this->row_actions( $actions ) );
	}

	function column_status( $item ) {
		$payment_id = $item->ID;
		
		if ( in_array( $item->post_status, array( Group_Buying_Payment::STATUS_PENDING, Group_Buying_Payment::STATUS_AUTHORIZED, Group_Buying_Payment::STATUS_COMPLETE, Group_Buying_Payment::STATUS_PARTIAL ) ) ) {
			
			$actions = array(
				'trash'    => '<a href="#TB_inline?width=900&height=260&inlineId=void_payment_'.$payment_id.'" class="thickbox" id="void_link_'.$payment_id.'" title="'.gb__('Void Payment').'">'.gb__( 'Void Payment' ).'</a>',
			);

			// Check if payment is even available since payment->items_to_capture() will check
			// and if no deals are successful than the capture will not work.
			$payment_capture_available = FALSE;
			$payment = Group_Buying_Payment::get_instance( $item->ID );
			$deals = $payment->get_deals();
			foreach ( $deals as $deal_id => $items ) {
				// All it takes is one deal to not be available for capture.
				if ( !$payment_capture_available ) {
					$deal = Group_Buying_Deal::get_instance( $deal_id );
					if ( $deal->is_successful() ) {
						$payment_capture_available = TRUE; 
					}
				}
			}
			if ( $payment_capture_available && $item->post_status != Group_Buying_Payment::STATUS_COMPLETE ) {
				$actions['attempt_capture'] = '<a href="javascript:void(0)" class="gb_attempt_capture" ref="'.$payment_id.'">'.gb__( 'Attempt Capture' ).'</a>';
			}
		}

		$void_form = '<div id="void_payment_'.$payment_id.'" style="display:none;"><p><textarea name="transaction_data_'.$payment_id.'" id="transaction_data_'.$payment_id.'" style="width:99%" rows="10" placeholder="'.gb__('These notes will be added to the transaction data.').'"></textarea><a href="javascript:void(0)" class="gb_void_payment button" id="'.$payment_id.'_void" ref="'.$payment_id.'">'.gb__( 'Void Payment' ).'</a></p></div>';

		$status = ucfirst( str_replace( 'publish', 'complete', $item->post_status ) );
		$status .= '<br/><span style="color:silver">';
		$status .= mysql2date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $item->post_date );
		$status .= '</span>';
		$status .= $void_form;
		$status .= $this->row_actions( $actions );
		return $status;
	}


	/**
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 * */
	function get_columns() {
		$columns = array(
			'status'  => gb__( 'Status' ),
			'title'  => gb__( 'Payment' ),
			'total'  => gb__( 'Totals' ),
			'deals'  => gb__( 'Deals' ),
			'account'    => gb__( 'Account' ),
			'data'  => gb__( 'Data' )
		);
		return apply_filters( 'gb_mngt_payments_columns', $columns );
	}

	/**
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 * */
	function get_sortable_columns() {
		$sortable_columns = array(
		);
		return apply_filters( 'gb_mngt_payments_sortable_columns', $sortable_columns );
	}


	/**
	 * Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 * */
	function get_bulk_actions() {
		$actions = array();
		return apply_filters( 'gb_mngt_payments_bulk_actions', $actions );
	}


	/**
	 * Prep data.
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 * */
	function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 25;


		/**
		 * Define our column headers.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Build an array to be used by the class for column
		 * headers.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$filter = ( isset( $_REQUEST['post_status'] ) ) ? $_REQUEST['post_status'] : array( Group_Buying_Payment::STATUS_PENDING, Group_Buying_Payment::STATUS_AUTHORIZED, Group_Buying_Payment::STATUS_COMPLETE, Group_Buying_Payment::STATUS_PARTIAL );

		$args = array(
			'post_type' => Group_Buying_Payment::POST_TYPE,
			'post_status' => $filter,
			'posts_per_page' => $per_page,
			'paged' => $this->get_pagenum()
		);
		// Check payments based on Deal ID
		if ( isset( $_GET['deal_id'] ) && $_GET['deal_id'] != '' ) {

			if ( Group_Buying_Deal::POST_TYPE != get_post_type( $_GET['deal_id'] ) )
				return; // not a valid search

			$purchases = Group_Buying_Purchase::get_purchases( array( 'deal' => $_GET['deal_id'] ) );
			$payment_ids = array();
			foreach ( $purchases as $purchase_id ) {
				$payment_ids = array_merge( $payment_ids, Group_Buying_Payment::get_payments_for_purchase( $purchase_id ) );
			}
			$args = array_merge( $args, array( 'post__in' => $payment_ids ) );
		}
		// Check payments based on Purchase ID
		if ( isset( $_GET['purchase_id'] ) && $_GET['purchase_id'] != '' ) {

			if ( Group_Buying_Purchase::POST_TYPE != get_post_type( $_GET['purchase_id'] ) )
				return; // not a valid search

			$payment_ids = Group_Buying_Payment::get_payments_for_purchase( $_GET['purchase_id'] );
			if ( empty( $payment_ids ) )
				return;

			$args = array_merge( $args, array( 'post__in' => $payment_ids ) );
		}
		// Check payments based on Account ID
		if ( isset( $_GET['account_id'] ) && $_GET['account_id'] != '' ) {

			if ( Group_Buying_Account::POST_TYPE != get_post_type( $_GET['account_id'] ) )
				return; // not a valid search

			$purchases = Group_Buying_Purchase::get_purchases( array( 'account' => $_GET['account_id'] ) );
			$payment_ids = array();
			foreach ( $purchases as $purchase_id ) {
				$payment_ids = array_merge( $payment_ids, Group_Buying_Payment::get_payments_for_purchase( $purchase_id ) );
			}
			$args = array_merge( $args, array( 'post__in' => $payment_ids ) );
		}
		// Search
		if ( isset( $_GET['s'] ) && $_GET['s'] != '' ) {
			$args = array_merge( $args, array( 's' => $_GET['s'] ) );
		}
		// Filter by date
		if ( isset( $_GET['m'] ) && $_GET['m'] != '' ) {
			$args = array_merge( $args, array( 'm' => $_GET['m'] ) );
		}
		
		$payments = new WP_Query( $args );

		/**
		 * REQUIRED. *Sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = apply_filters( 'gb_mngt_payments_items', $payments->posts );

		/**
		 * REQUIRED. Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
				'total_items' => $payments->found_posts,                //WE have to calculate the total number of items
				'per_page'  => $per_page,                    //WE have to determine how many items to show on a page
				'total_pages' => $payments->max_num_pages   //WE have to calculate the total number of pages
			) );
	}

}
