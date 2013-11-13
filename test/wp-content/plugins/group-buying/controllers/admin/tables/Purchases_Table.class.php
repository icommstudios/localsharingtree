<?php

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Group_Buying_Purchases_Table extends WP_List_Table {
	protected static $post_type = Group_Buying_Purchase::POST_TYPE;

	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
				'singular' => 'order',     // singular name of the listed records
				'plural' => 'orders', // plural name of the listed records
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
		$status_links['all'] = "<a href='admin.php?page=group-buying/purchase_records{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset( $_REQUEST['post_status'] ) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			// replace "Published" with "Complete".
			$label = str_replace( 'Published', 'Complete', translate_nooped_plural( $status->label_count, $num_posts->$status_name ) );
			$status_links[$status_name] = "<a href='admin.php?page=group-buying/purchase_records&post_status=$status_name'$class>" . sprintf( $label, number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}

	function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions"> <?php
		if ( 'top' == $which && !is_singular() ) {

			$this->months_dropdown( self::$post_type );

			do_action( 'gb_mngt_purchases_extra_tablenav' );

			submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
		} ?>
		</div> <?php
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
			return apply_filters( 'gb_mngt_purchases_column_'.$column_name, $item ); // do action for those columns that are filtered in
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
		$purchase = Group_Buying_Purchase::get_instance( $item->ID );
		$user_id = $purchase->get_original_user();
		$account_id = Group_Buying_Account::get_account_id_for_user( $user_id );

		//Build row actions
		$actions = array(
			'payment'    => sprintf( '<a href="admin.php?page=group-buying/payment_records&purchase_id=%s">Payments</a>', $item->ID ),
			'purchaser'    => sprintf( '<a href="post.php?post=%s&action=edit">'.gb__( 'Purchaser' ).'</a>', $account_id ),
		);

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(order&nbsp;id:%2$s)</span>%3$s',
			$item->post_title,
			$item->ID,
			$this->row_actions( $actions )
		);
	}

	function column_total( $item ) {
		$purchase = Group_Buying_Purchase::get_instance( $item->ID );
		gb_formatted_money( $purchase->get_total() );
	}

	function column_deals( $item ) {
		$purchase = Group_Buying_Purchase::get_instance( $item->ID );
		$products = $purchase->get_products();

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

	function column_payments( $item ) {
		$payments = Group_Buying_Payment::get_payments_for_purchase( $item->ID );
		foreach ( $payments as $payment_id ) {
			$payment = Group_Buying_Payment::get_instance( $payment_id );
			$method = $payment->get_payment_method();
			//Return the title contents
			return sprintf( '<a href="admin.php?page=group-buying/payment_records&s=%2$s">%1$s</a> <span style="color:silver">(payment&nbsp;id:%2$s)</span>',
				$method,
				$payment_id
			);
		}
	}

	function column_ip_address( $item ) {
		$purchase = Group_Buying_Purchase::get_instance( $item->ID );
		print $purchase->get_user_ip();
	}

	function column_status( $item ) {
		$purchase_id = $item->ID;
		$purchase = Group_Buying_Purchase::get_instance( $item->ID );

		$actions = array(
			'trash'    => '<span id="'.$purchase_id.'_destroy_result"></span><a href="javascript:void(0)" class="gb_destroy" id="'.$purchase_id.'_destroy" ref="'.$purchase_id.'">'.gb__( 'Delete Purchase' ).'</a>',
		);

		$status = ucfirst( str_replace( 'publish', 'complete', $item->post_status ) );
		$status .= '<br/><span style="color:silver">';
		$status .= mysql2date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $item->post_date );
		$status .= '</span>';
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
			'title'  => gb__( 'Order' ),
			'total'  => gb__( 'Total' ),
			'deals'  => gb__( 'Deals' ),
			'payments'  => gb__( 'Payments' ),
			'ip_address'  => gb__( 'IP Address' )
		);
		return apply_filters( 'gb_mngt_purchases_columns', $columns );
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
		return apply_filters( 'gb_mngt_purchases_sortable_columns', $sortable_columns );
	}


	/**
	 * Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 * */
	function get_bulk_actions() {
		$actions = array();
		return apply_filters( 'gb_mngt_purchases_bulk_actions', $actions );
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
			'post_type' => Group_Buying_Purchase::POST_TYPE,
			'post_status' => $filter,
			'posts_per_page' => $per_page,
			'paged' => $this->get_pagenum()
		);
		// Check purchases based on Deal ID
		if ( isset( $_GET['deal_id'] ) && $_GET['deal_id'] != '' ) {

			if ( Group_Buying_Deal::POST_TYPE != get_post_type( $_GET['deal_id'] ) )
				return; // not a valid search

			$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'deal' => $_GET['deal_id'] ) );

			$posts_in = array(
				'post__in' => $purchase_ids
			);
			$args = array_merge( $args, $posts_in );
		}
		// Check payments based on Account ID
		if ( isset( $_GET['account_id'] ) && $_GET['account_id'] != '' ) {

			if ( Group_Buying_Account::POST_TYPE != get_post_type( $_GET['account_id'] ) )
				return; // not a valid search

			$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'account' => $_GET['account_id'] ) );
			$meta_query = array(
				'post__in' => $purchase_ids,
			);
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
		$purchases = new WP_Query( $args );

		/**
		 * REQUIRED. *Sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = apply_filters( 'gb_mngt_purchases_items', $purchases->posts );

		/**
		 * REQUIRED. Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
				'total_items' => $purchases->found_posts,                //WE have to calculate the total number of items
				'per_page'  => $per_page,                    //WE have to determine how many items to show on a page
				'total_pages' => $purchases->max_num_pages   //WE have to calculate the total number of pages
			) );
	}

}
