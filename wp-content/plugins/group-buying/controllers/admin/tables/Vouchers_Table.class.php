<?php 

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Group_Buying_Vouchers_Table extends WP_List_Table {
	protected static $post_type = Group_Buying_Voucher::POST_TYPE;

	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
				'singular' => gb__( 'voucher' ),     // singular name of the listed records
				'plural' => gb__( 'vouchers' ), // plural name of the listed records
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
		$status_links['all'] = "<a href='admin.php?page=group-buying/voucher_records{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset( $_REQUEST['post_status'] ) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			// replace "Published" with "Complete".
			$label = str_replace( array( 'Published', 'Trash' ), array( 'Active', 'Deactived' ), translate_nooped_plural( $status->label_count, $num_posts->$status_name ) );
			$status_links[$status_name] = "<a href='admin.php?page=group-buying/voucher_records&post_status=$status_name'$class>" . sprintf( $label, number_format_i18n( $num_posts->$status_name ) ) . '</a>';
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
			return apply_filters( 'gb_mngt_vouchers_column_'.$column_name, $item ); // do action for those columns that are filtered in
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
		$voucher = Group_Buying_Voucher::get_instance( $item->ID );
		$purchase = $voucher->get_purchase();
		if ( !is_a( $purchase, 'Group_Buying_Purchase' ) ) {
			return '<p style="color:#BC0B0B">' . gb__( 'ERROR: Order not found.' ) . '</span>';
		}
		$user_id = $purchase->get_user();
		$account = Group_Buying_Account::get_instance( $user_id );
		$deal_id = $voucher->get_post_meta( '_voucher_deal_id' );


		//Build row actions
		$actions = array(
			'deal'    => sprintf( '<a href="%s">'.gb__( 'Deal' ).'</a>', get_edit_post_link( $deal_id ) ),
			'purchase'    => sprintf( '<a href="admin.php?page=group-buying/purchase_records&s=%s">'.gb__( 'Order' ).'</a>', $purchase->get_id() )
		);
		if ( $user_id == -1 ) { // gifts
			$purchaser = array(
				'purchaser'    => sprintf( '<a href="admin.php?page=group-buying/gift_records&s=%s">'.gb__( 'Gift' ).'</a>', $purchase->get_id() ),
			);
		} else {
			$purchaser = array(
				'purchaser'    => sprintf( '<a href="%s">'.gb__( 'Purchaser' ).'</a>', get_edit_post_link( $account->get_id() ) ),
			);
		}

		$actions = array_merge( $actions, $purchaser );

		//Return the title contents
		return sprintf( gb__(  '%1$s <span style="color:silver">(voucher&nbsp;id:%2$s)</span>%3$s' ),
			get_the_title( $item->ID ),
			$item->ID,
			$this->row_actions( $actions )
		);
	}

	function column_code( $item ) {
		$voucher = Group_Buying_Voucher::get_instance( $item->ID );
		echo $voucher->get_serial_number();
	}

	function column_manage( $item ) {
		$voucher_id = $item->ID;
		if ( get_post_status( $voucher_id ) != 'publish' ) {
			$activate_path = 'edit.php?post_type=gb_deal&activate_voucher='.$voucher_id.'&_wpnonce='.wp_create_nonce( 'activate_voucher' );
			echo '<p><span id="'.$voucher_id.'_activate_result"></span><a href="'.admin_url( $activate_path ).'" class="gb_activate button" id="'.$voucher_id.'_activate" ref="'.$voucher_id.'">'.gb__( 'Activate' ).'</a></p>';
		} else {
			echo '<p><span id="'.$voucher_id.'_deactivate_result"></span><a href="javascript:void(0)" class="gb_deactivate button disabled" id="'.$voucher_id.'_deactivate" ref="'.$voucher_id.'">'.gb__( 'Deactivate' ).'</a></p>';
		}
	}

	function column_status( $item ) {
		$voucher_id = $item->ID;
		$voucher = Group_Buying_Voucher::get_instance( $voucher_id );

		$actions = array(
			'view'    => '<a href="'.get_permalink( $voucher_id ).'">View</a>',
			'trash'    => '<span id="'.$voucher_id.'_destroy_result"></span><a href="javascript:void(0)" class="gb_destroy" id="'.$voucher_id.'_destroy" ref="'.$voucher_id.'">'.gb__( 'Delete Records' ).'</a>',
		);

		$status = ucfirst( str_replace( 'publish', 'active', $item->post_status ) );
		$status .= '<br/><span style="color:silver">';
		$status .= mysql2date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $item->post_date );
		$status .= '</span>';
		$status .= $this->row_actions( $actions );
		return $status;
	}

	function column_claimed( $item ) {
		$voucher = Group_Buying_Voucher::get_instance( $item->ID );
		$claim_date = $voucher->get_claimed_date();
		$status = '';
		if ( $claim_date ) {
			$status = '<p>' . mysql2date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $claim_date ) . '</p>';
			$status .= '<p><span id="'.$item->ID.'_unclaim_result"></span><a href="javascript:void(0)" class="gb_unclaim button disabled" id="'.$item->ID.'_unclaim" ref="'.$item->ID.'">'.gb__( 'Remove Redemption' ).'</a></p>';
		}
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
			'code'  => gb__( 'Code' ),
			'manage'  => gb__( 'Manage Status' ),
			'claimed'  => gb__( 'Redeemed' )
		);
		return apply_filters( 'gb_mngt_vouchers_columns', $columns );
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
		return apply_filters( 'gb_mngt_vouchers_sortable_columns', $sortable_columns );
	}


	/**
	 * Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 * */
	function get_bulk_actions() {
		$actions = array();
		return apply_filters( 'gb_mngt_vouchers_bulk_actions', $actions );
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

		$filter = ( isset( $_REQUEST['post_status'] ) ) ? $_REQUEST['post_status'] : array( 'publish', 'pending', 'draft', 'future' );
		$args=array(
			'post_type' => Group_Buying_Voucher::POST_TYPE,
			'post_status' => $filter,
			'posts_per_page' => $per_page,
			'paged' => $this->get_pagenum()
		);
		if ( isset( $_GET['purchase_id'] ) && $_GET['purchase_id'] != '' ) {

			if ( Group_Buying_Purchase::POST_TYPE != get_post_type( $_GET['purchase_id'] ) )
				return; // not a valid search

			$vouchers = Group_Buying_Voucher::get_vouchers_for_purchase( $_GET['purchase_id'] );
			if ( empty( $vouchers ) )
				return;

			$args = array_merge( $args, array( 'post__in' => $vouchers ) );
		}
		// Check purchases based on Deal ID
		if ( isset( $_GET['deal_id'] ) && $_GET['deal_id'] != '' ) {

			if ( Group_Buying_Deal::POST_TYPE != get_post_type( $_GET['deal_id'] ) )
				return; // not a valid search

			$vouchers = Group_Buying_Voucher::get_vouchers_for_deal( $_GET['deal_id'] );
			if ( empty( $vouchers ) )
				return;

			$args = array_merge( $args, array( 'post__in' => $vouchers ) );
		}
		// Check payments based on Account ID
		if ( isset( $_GET['account_id'] ) && $_GET['account_id'] != '' ) {

			if ( Group_Buying_Account::POST_TYPE != get_post_type( $_GET['account_id'] ) )
				return; // not a valid search

			$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'account' => $_GET['account_id'] ) );

			$meta_query = array(
				'meta_query' => array(
					array(
						'key' => '_purchase_id',
						'value' => $purchase_ids,
						'type' => 'numeric',
						'compare' => 'IN'
					)
				) );
			$args = array_merge( $args, $meta_query );
		}
		// Search
		if ( isset( $_GET['s'] ) && $_GET['s'] != '' ) {
			$args = array_merge( $args, array( 'p' => $_GET['s'] ) );
		}
		// Filter by date
		if ( isset( $_GET['m'] ) && $_GET['m'] != '' ) {
			$args = array_merge( $args, array( 'm' => $_GET['m'] ) );
		}
		$vouchers = new WP_Query( $args );

		/**
		 * REQUIRED. *Sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = apply_filters( 'gb_mngt_vouchers_items', $vouchers->posts );

		/**
		 * REQUIRED. Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
				'total_items' => $vouchers->found_posts,                //WE have to calculate the total number of items
				'per_page'  => $per_page,                    //WE have to determine how many items to show on a page
				'total_pages' => $vouchers->max_num_pages   //WE have to calculate the total number of pages
			) );
	}

}
