<?php


if ( !class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Group_Buying_Gifts_Table extends WP_List_Table {
	protected static $post_type = Group_Buying_Gift::POST_TYPE;

	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
				'singular' => 'gift',     // singular name of the listed records
				'plural' => 'gifts', // plural name of the listed records
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
			$label = gb__( 'Payment&nbsp;' ).str_replace( 'Published', 'Complete', translate_nooped_plural( $status->label_count, $num_posts->$status_name ) );
			$status_links[$status_name] = "<a href='admin.php?page=group-buying/gift_records&post_status=$status_name'$class>" . sprintf( $label, number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}

	function extra_tablenav( $which ) {
?>
		<div class="alignleft actions">
<?php
		if ( 'top' == $which && !is_singular() ) {

			$this->months_dropdown( self::$post_type );

			submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
		}
?>
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
			return apply_filters( 'gb_mngt_gifts_column_'.$column_name, $item ); // do action for those columns that are filtered in
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
		$gift = Group_Buying_Gift::get_instance( $item->ID );
		$purchase_id = $gift->get_purchase_id();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		if ( $purchase ) {
			$user_id = $purchase->get_original_user();
		} else {
			$user_id = 0;
		}
		$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );

		//Build row actions
		$actions = array(
			'order'    => sprintf( '<a href="admin.php?page=group-buying/purchase_records&s=%s">Order</a>', $purchase_id ),
			'purchaser'    => sprintf( '<a href="post.php?post=%s&action=edit">'.gb__( 'Purchaser' ).'</a>', $account_id ),
		);

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(order&nbsp;id:%2$s)</span>%3$s',
			$item->post_title,
			$purchase_id,
			$this->row_actions( $actions )
		);
	}

	function column_total( $item ) {
		$gift = Group_Buying_Gift::get_instance( $item->ID );
		$purchase_id = $gift->get_purchase_id();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );

		if ( $purchase ) {
			gb_formatted_money( $purchase->get_total() );
		} else {
			gb_formatted_money(0);
		}
	}

	function column_deals( $item ) {
		$gift = Group_Buying_Gift::get_instance( $item->ID );
		$purchase_id = $gift->get_purchase_id();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		$products = $purchase?$purchase->get_products():array();

		$i = 0;
		foreach ( $products as $product => $item ) {
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
			$details = apply_filters( 'gb_purchase_deal_column_details', $details, $item, $products );
			// display details
			foreach ( $details as $label => $value ) {
				echo '<br />&nbsp;&nbsp;'.$label.': '.$value;
			}
			// Build Payment methods
			$payment_methods = array();
			foreach ( $item['payment_method'] as $method => $payment ) {
				$payment_methods[] .= $method.' &mdash; '.gb_get_formatted_money( $payment );
			}
			echo '</p>';
			if ( count( $products ) > $i ) {
				echo '<span class="meta_box_block_divider"></span>';
			}
		}

	}

	function column_gift( $item ) {
		$gift_id = $item->ID;
		$gift = Group_Buying_Gift::get_instance( $gift_id );
		$purchase_id = $gift->get_purchase_id();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		$account_id = $purchase?$purchase->get_user():-1;
		if ( $account_id == -1 ) {
			echo '<p>';
			echo '<strong>'.gb__( 'Recipient:' ).'</strong> <span class="editable_string gb_highlight" ref="'.$gift_id.'">'.$gift->get_recipient().'</span><input type="text" id="'.$gift_id.'_recipient_input" class="option_recipient cloak" value="'.$gift->get_recipient().'" />';
			echo '<br/><span style="color:silver">'.gb__( 'code' ).': '.$gift->get_coupon_code().'</span>';
			echo '</p>';
			echo '<p><span id="'.$gift_id.'_activate_result"></span><a href="/wp-admin/edit.php?post_type=gb_purchase&resend_gift='.$gift_id.'&_wpnonce='.wp_create_nonce( 'resend_gift' ).'" class="gb_resend_gift button" id="'.$gift_id.'_activate" ref="'.$gift_id.'">Resend</a></p>';
			return;
		} else {
			$user_id = $purchase->get_user();
			$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
			$account = Group_Buying_Account::get_instance( $account_id );
			$account_name = ( is_a( $account, 'Group_Buying_Account' ) ) ? $account->get_name() : '' ;
			printf( '<a href="%1$s">%2$s<span style="color:silver">(account&nbsp;id:%3$s)</span></a>', get_edit_post_link( $account_id ), $account_name, $account_id );
		}

	}

	function column_status( $item ) {
		$gift = Group_Buying_Gift::get_instance( $item->ID );
		$purchase_id = $gift->get_purchase_id();
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		if ( $purchase ) {
			$account_id = $purchase->get_user();
		} else {
			$account_id = -1;
		}

		if ( $account_id == -1 ) {
			$status = '<strong>'.gb__( 'Pending Claim' ).'</strong><br/>';
		} else {
			$status = '<strong>'.gb__( 'Complete' ).'</strong><br/>';
		}
		$status .= '<span style="color:silver">';
		$status .= mysql2date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $item->post_date );
		$status .= '<br/>';
		$status .= gb__( 'Payment: ' ).ucfirst( str_replace( 'publish', 'complete', $item->post_status ) );
		$status .= '</span>';

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
			'status'  => gb__('Status'),
			'title'  => gb__('Order'),
			'total'  => gb__('Totals'),
			'deals'  => gb__('Deals'),
			'gift'  => gb__('Manage')
		);
		return apply_filters( 'gb_mngt_gifts_columns', $columns );
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
		return apply_filters( 'gb_mngt_gifts_sortable_columns', $sortable_columns );
	}


	/**
	 * Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 * */
	function get_bulk_actions() {
		$actions = array();
		return apply_filters( 'gb_mngt_gifts_bulk_actions', $actions );
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

		$filter = ( isset( $_REQUEST['post_status'] ) ) ? $_REQUEST['post_status'] : 'all';
		$args=array(
			'post_type' => Group_Buying_Gift::POST_TYPE,
			'post_status' => $filter,
			'posts_per_page' => $per_page,
			'paged' => $this->get_pagenum()
		);
		// Check purchases based on Deal ID
		if ( isset( $_GET['deal_id'] ) && $_GET['deal_id'] != '' ) {

			if ( Group_Buying_Deal::POST_TYPE != get_post_type( $_GET['deal_id'] ) )
				return; // not a valid search

			$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'deal' => $_GET['deal_id'] ) );

			$meta_query = array(
				'meta_query' => array(
					array(
						'key' => '_purchase',
						'value' => $purchase_ids,
						'type' => 'numeric',
						'compare' => 'IN'
					)
				) );
			$args = array_merge( $args, $meta_query );
		}
		// Check payments based on Account ID
		if ( isset( $_GET['account_id'] ) && $_GET['account_id'] != '' ) {

			if ( Group_Buying_Account::POST_TYPE != get_post_type( $_GET['account_id'] ) )
				return; // not a valid search

			$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'account' => $_GET['account_id'] ) );
			$meta_query = array(
				'meta_query' => array(
					array(
						'key' => '_purchase',
						'value' => $purchase_ids,
						'type' => 'numeric',
						'compare' => 'IN'
					)
				) );
			$args = array_merge( $args, $meta_query );
		}
		// Search
		if ( isset( $_GET['s'] ) && $_GET['s'] != '' ) {
			$args = array_merge( $args, array( 's' => $_GET['s'] ) );
		}
		// Filter by date
		if ( isset( $_GET['m'] ) && $_GET['m'] != '' ) {
			$args = array_merge( $args, array( 'm' => $_GET['m'] ) );
		}
		$gifts = new WP_Query( $args );

		/**
		 * REQUIRED. *Sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = apply_filters( 'gb_mngt_gifts_items', $gifts->posts );

		/**
		 * REQUIRED. Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
				'total_items' => $gifts->found_posts,                //WE have to calculate the total number of items
				'per_page'  => $per_page,                    //WE have to determine how many items to show on a page
				'total_pages' => $gifts->max_num_pages   //WE have to calculate the total number of pages
			) );
	}

}