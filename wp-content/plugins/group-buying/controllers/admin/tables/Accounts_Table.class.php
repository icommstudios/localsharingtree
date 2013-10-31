<?php

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Group_Buying_Accounts_Table extends WP_List_Table {
	protected static $post_type = Group_Buying_Account::POST_TYPE;

	function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
				'singular' => 'account',     // singular name of the listed records
				'plural' => 'account', // plural name of the listed records
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
		$status_links['all'] = "<a href='admin.php?page=group-buying/account_records{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset( $_REQUEST['post_status'] ) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			// replace "Published" with "Complete".
			$label = str_replace( array( 'Published', 'Trash' ), array( 'Active', 'Suspended' ), translate_nooped_plural( $status->label_count, $num_posts->$status_name ) );
			$status_links[$status_name] = "<a href='admin.php?page=group-buying/account_records&post_status=$status_name'$class>" . sprintf( $label, number_format_i18n( $num_posts->$status_name ) ) . '</a>';
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
		<a href="<?php gb_accounts_report_url() ?>"  class="button"><?php gb_e('Accounts Report') ?></a>
		<a href="<?php gb_credits_report_url() ?>"  class="button"><?php gb_e('Credits Report') ?></a>
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
			return apply_filters( 'gb_mngt_account_column_'.$column_name, $item ); // do action for those columns that are filtered in
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
		$account_id = $item->ID;
		$account = Group_Buying_Account::get_instance_by_id( $account_id );

		//Build row actions
		$actions = array(
			'payments'    => sprintf( '<a href="admin.php?page=group-buying/payment_records&account_id=%s">'.gb__('Payments').'</a>', $account_id ),
			'purchases'    => sprintf( '<a href="admin.php?page=group-buying/purchase_records&account_id=%s">'.gb__('Orders').'</a>', $account_id ),
			'vouchers'    => sprintf( '<a href="admin.php?page=group-buying/voucher_records&account_id=%s">'.gb__('Vouchers').'</a>', $account_id ),
			'gifts'    => sprintf( '<a href="admin.php?page=group-buying/gift_records&account_id=%s">'.gb__('Gifts').'</a>', $account_id )
		);

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(account&nbsp;id:%2$s)</span>%3$s',
			$item->post_title,
			$item->ID,
			$this->row_actions( $actions )
		);
	}

	function column_username( $item ) {
		$account_id = $item->ID;
		$account = Group_Buying_Account::get_instance_by_id( $account_id );
		$user_id = $account->get_user_id_for_account( $account_id );
		$user = get_userdata( $user_id );
		$name = ( $account->is_suspended() ) ? '<span style="color:#BC0B0B">Suspended:</span> ' . $account->get_name() : $account->get_name() ;

		//Build row actions
		$suspend_text = ( $account->is_suspended() ) ? gb__('Revert Suspension'): gb__('Suspend');
		$actions = array(
			'edit'    => sprintf( '<a href="post.php?post=%s&action=edit">Manage</a>', $account_id ),
			'user'    => sprintf( '<a href="user-edit.php?user_id=%s">User</a>', $user_id ),
			'trash'    => '<span id="'.$account_id.'_suspend_result"></span><a href="javascript:void(0)" class="gb_suspend" id="'.$account_id.'_suspend" ref="'.$account_id.'">'.$suspend_text.'</a>'
		);

		//Return the title contents
		return sprintf( '%1$s %2$s <span style="color:silver">(user&nbsp;id:%3$s)</span>%4$s',
			get_avatar( $user->user_email, '35' ),
			$name,
			$user_id,
			$this->row_actions( $actions )
		);

	}

	function column_address( $item ) {
		$account_id = $item->ID;
		$account = Group_Buying_Account::get_instance_by_id( $account_id );
		echo gb_format_address( $account->get_address(), 'string', '<br />' );
	}

	function column_credits( $item ) {
		$account_id = $item->ID;
		$account = Group_Buying_Account::get_instance_by_id( $account_id );
		$credits = $account->get_credit_balance( Group_Buying_Affiliates::CREDIT_TYPE );
		if ( !$credits ) $credits = '0';
		echo $credits;
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
			'username' => gb__('Name'),
			'title'  => gb__('Account'),
			'address'  => gb__('Address'),
			'credits'  => gb__('Credits')
		);
		return apply_filters( 'gb_mngt_accounts_columns', $columns );
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
		return apply_filters( 'gb_mngt_accounts_sortable_columns', $sortable_columns );
	}


	/**
	 * Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 * */
	function get_bulk_actions() {
		$actions = array();
		return apply_filters( 'gb_mngt_accounts_bulk_actions', $actions );
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
			'post_type' => Group_Buying_Account::POST_TYPE,
			'post_status' => $filter,
			'posts_per_page' => $per_page,
			'paged' => $this->get_pagenum()
		);
		// Search
		if ( isset( $_GET['s'] ) && $_GET['s'] != '' ) {
			$args = array_merge( $args, array( 's' => $_GET['s'] ) );
		}
		// Filter by date
		if ( isset( $_GET['m'] ) && $_GET['m'] != '' ) {
			$args = array_merge( $args, array( 'm' => $_GET['m'] ) );
		}
		$accounts = new WP_Query( $args );

		/**
		 * REQUIRED. *Sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = apply_filters( 'gb_mngt_accounts_items', $accounts->posts );

		/**
		 * REQUIRED. Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
				'total_items' => $accounts->found_posts,                //WE have to calculate the total number of items
				'per_page'  => $per_page,                    //WE have to determine how many items to show on a page
				'total_pages' => $accounts->max_num_pages   //WE have to calculate the total number of pages
			) );
	}

}