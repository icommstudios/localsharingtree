<?php

class GB_SF_Charities extends Group_Buying_Controller {
	const POST_TYPE = 'gb_charities';
	const REWRITE_SLUG = 'charities';
	const REPORT_SLUG = 'charity';
	const META_KEY = 'gb_purchase_charity';
	const META_KEY_DONATION_AMT = 'gb_purchase_charity_amount';
	const META_KEY_DONATION_PCT = 'gb_purchase_charity_percentage';
	const DONATION_ITEM_ID = 'gb_donation_item_id';
	const ATTRIBUTE_ASSOCIATION_META_KEY = 'gb_attributed_charity';

	public static function init() {
		parent::init();

		// admin
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 0 );
			add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
		}
		add_action( 'admin_init', array( __CLASS__, 'maybe_create_donation_deal') );
		add_action( 'admin_init', array( __CLASS__, 'maybe_create_donation_attributes'), 50, 2 );
		add_action( 'save_post', array( __CLASS__, 'maybe_create_donation_attributes_on_save'), 50, 2 );

		add_filter( 'template_include', array( get_class(), 'override_template' ) );
		
		// Admin columns
		add_filter ( 'manage_edit-'.self::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter ( 'manage_'.self::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		
		// Add public view for Charity authorized users for current logged in user - Allow mutiple charities
		add_action ('account_section_before_dash', array( get_class(), 'show_user_charity_section'), 10, 0);

	}
	
	public static function register_columns( $columns ) {
		unset( $columns['date'] );
		//unset( $columns['author'] );
		$columns['authorized'] = __( 'Authorized' );
		$columns['report'] = __( 'Reports' );
		$columns['date'] = __( 'Published' );
	
		return $columns;
	}

	public static function column_display( $column_name, $id ) {
		$charity = GB_SF_Charity::get_instance( $id );

		if ( !$charity )
			return; // return for that temp post

		switch ( $column_name ) {
		case 'authorized':
			$display = '';
			$authorized_users = $charity->get_authorized_users();
			foreach ( $authorized_users as $user_id ) {
				$user = get_userdata( $user_id );
				$display = $user->user_firstname . ' ' . $user->user_lastname;
				if ( ' ' == $display ) {
					$display = $user->user_login;
				}
				if ( !empty( $user->user_email ) ) {
					$display .= " (".$user->user_email.")";
				}
			}
			echo $display;
			break;
		
		case 'report':
			echo '<a href="'.gb_get_charity_purchases_report_url( $charity->get_id() ).'" class="button" target="_blank">'.gb__('Purchase Report').'</a>';
			break;
		default:
			break;
		}
	}
	
	public function set_purchase_charity( Group_Buying_Purchase $purchase, $charity_id, $donation_amount = 0, $donation_percentage = 0 ) {
		$purchase->save_post_meta( array(
				self::META_KEY => $charity_id,
				self::META_KEY_DONATION_AMT => $donation_amount,
				self::META_KEY_DONATION_PCT => $donation_percentage
			) );
	}

	public function get_purchase_charity_id( Group_Buying_Purchase $purchase ) {
		$charity = self::get_purchase_charity( $purchase );
		if ( is_a( $charity, 'GB_SF_Charity' ) ) {
			return $charity->get_id();
		}
		return 0;
	}

	public function get_purchase_charity( Group_Buying_Purchase $purchase ) {
		$charity_id = $purchase->get_post_meta( self::META_KEY );
		if ( $charity_id ) {
			$charity = GB_SF_Charity::get_instance( $charity_id );
			if ( is_a( $charity, 'GB_SF_Charity' ) ) {
				return $charity;
			}
		}
		return 0;
	}
	
	public function get_purchase_charity_donation_amount( Group_Buying_Purchase $purchase ) {
		$donation_amount = $purchase->get_post_meta( self::META_KEY_DONATION_AMT );
		if ( $donation_amount ) {
			return $donation_amount;
		}
		return false;
	}
	
	public function get_purchase_charity_donation_percentage( Group_Buying_Purchase $purchase ) {
		$percentage = $purchase->get_post_meta( self::META_KEY_DONATION_PCT );
		if ( $percentage ) {
			return $percentage;
		}
		return false;
	}


	public static function add_meta_box() {
		add_meta_box( 'gb_charity_reports', gb__( 'Reports' ), array( __CLASS__, 'show_meta_box' ), self::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_charity_authorized_users', self::__( 'Authorized Users' ), array( get_class(), 'show_meta_box' ), self::POST_TYPE, 'advanced', 'high' );
		//add_meta_box( 'gb_charity_payment_settings', gb__( 'Payment Information' ), array( __CLASS__, 'show_meta_box' ), self::POST_TYPE, 'advanced', 'high' );
		
	}

	public static function show_meta_box( $post, $metabox ) {
		$charity = GB_SF_Charity::get_instance( $post->ID );
		switch ( $metabox['id'] ) {
		case 'gb_charity_reports':
			self::show_meta_box_reports( $charity, $post, $metabox );
			break;
		case 'gb_charity_payment_settings':
			self::show_meta_box_gb_charity_payments( $charity, $post, $metabox );
			break;
		case 'gb_charity_authorized_users':
			self::show_meta_box_gb_charity_authorized_users( $charity, $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	public function show_meta_box_reports( GB_SF_Charity $charity, $post, $metabox ) {
		echo '<a href="'.gb_get_charity_purchases_report_url( $charity->get_id() ).'" class="button" target="_blank">'.gb__('Purchase Report').'</a>';
	}

	/**
	 * Display the deal details meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $charity
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_charity_payments( GB_SF_Charity $charity, $post, $metabox ) {
		//$username = $charity->get_username();
		//$password = $charity->get_password();
		$payment_notes = $charity->get_payment_notes();
		//$percentage = ( $charity->get_percentage() ) ? $charity->get_percentage() : 15 ;
		?>
			<p>
				<label for="gb_payment_notes"><?php gb_e( 'Payment Notes' ); ?></label><br />
				<textarea name="gb_payment_notes" id="gb_payment_notes" class="large-text"><?php echo $payment_notes; ?></textarea>
			</p>
			<?php /*/ ?>
			<p>
				<label for="gb_charity_username"><?php gb_e( 'BluePay Username' ); ?></label><br />
				<input type="text" name="gb_charity_username" id="gb_charity_username" value="<?php echo esc_attr( $username ); ?>" class="large-text" />
			</p>
			<p>
				<label for="gb_charity_password"><?php gb_e( 'BluePay Password' ); ?></label><br />
				<input type="text" name="gb_charity_password" id="gb_charity_password" value="<?php echo esc_attr( $password ); ?>" class="large-text" />
			</p>
			<p>
				<label for="gb_charity_percentage"><?php gb_e( 'Payment Percentage' ); ?></label><br />
				<input type="number" min="1" max="99" name="gb_charity_percentage" id="gb_charity_percentage" value="<?php echo esc_attr( $percentage ); ?>" />%
			</p>
			<?php /**/ ?>
		<?php
	}
	
	private static function show_meta_box_gb_charity_authorized_users(  GB_SF_Charity $charity, $post, $metabox ) {
		$authorized_users = $charity->get_authorized_users();
		$args = apply_filters( 'gb_get_users_args', null );
		$users = get_users( $args );
		?>
        <p>
	<strong><?php gb_e( 'Authorized Users' ); ?></strong><br />
	<?php
		foreach ( $authorized_users as $user_id ) {
			$user = get_userdata( $user_id );
			$display = "$user->user_firstname $user->user_lastname";
			if ( ' ' == $display ) {
				$display = $user->user_login;
			}
			if ( !empty( $user->user_email ) ) {
				$display .= " ($user->user_email)";
			}
			echo "$display<br />";
		} ?>
</p>
<p>
	<strong><?php gb_e( 'Authorize a User' ); ?></strong><br />

	<?php 
	// If over 100 users use an input field
	if ( count( $users ) > 100 ): ?>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				var $authorized_userid = $('#authorized_user');
				var $span = $('#authorized_user_ajax');
				
				var show_account = function() {
					$span.addClass('loading_gif').empty();
					var user_id = $authorized_userid.val();
					if ( !user_id ) {
						$span.removeClass('loading_gif');
						return;
					}
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						data: {
							action: 'gbs_ajax_get_account',
							id: user_id
						},
						success: function(data) {
							$span.removeClass('loading_gif');
							$span.empty().append(data.name + ' <span style="color:silver">(user id:' + data.user_id + ') (account id:' + data.account_id + ')</span>');
						}
					});
				};
				$authorized_userid.live('keyup',show_account);
			});
		</script>
		<style type="text/css">
			.loading_gif {
				background: url( '<?php echo GB_URL; ?>/resources/img/loader.gif') no-repeat 0 center;
				width: auto;
				height: 16px;
				padding-right: 16px;
				padding-bottom: 2px;
			}
		</style>
		<input name="authorized_user" id="authorized_user" type="text" size="8" placeholder="<?php gb_e('User ID')?>"/>
		<span id="authorized_user_ajax">&nbsp;</span>
	<?php else: ?>
		<select name="authorized_user" id="authorized_user" class="select2" style="width:300px;">
			<option value=""><?php gb_e( 'Select a User To Authorize' ); ?></option>
			<?php
				$authorized_user = $authorized_users[0];
				foreach ( $users as $user ) {
					if ( !in_array( $user->ID, $authorized_users) ) {
						
						$display = get_user_meta( $user->ID, 'first_name', TRUE ) . ' ' . get_user_meta( $user->ID, 'last_name', TRUE );
						if ( ' ' == $display ) {
							$display = $user->user_login;
						}
						if ( !empty( $user->user_email ) ) {
							$display .= " ($user->user_email)";
						}
						echo "<option value=\"$user->ID\">$display</option>";
					}
				} ?>
		</select>
	<?php endif ?>
</p>

<p>
	<strong><?php gb_e( 'Unauthorize a User' ); ?></strong><br />
	<select name="unauthorized_user" id="unauthorized_user" class="select2" style="width:300px;">
		<option value=""><?php gb_e( 'Select a User To Unuthorize' ); ?></option>
		<?php
			foreach ( $authorized_users as $user_id ) {
				$user = get_userdata( $user_id );
				$display = get_user_meta( $user_id, 'first_name', TRUE ) . ' ' . get_user_meta( $user_id, 'last_name', TRUE );
				if ( ' ' == $display ) {
					$display = $user->user_login;
				}
				if ( !empty( $user->user_email ) ) {
					$display .= " ($user->user_email)";
				}

				echo "<option value=\"$user->ID\">$display</option>";
			} ?>
	</select>
</p>
        <?php
	}

	public static function save_meta_box( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != GB_SF_Charity::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		if ( empty( $_POST ) ) {
			return;
		}
		$charity = GB_SF_Charity::get_instance( $post->ID );
		//$username = ( isset( $_POST['gb_charity_username'] ) ) ? $_POST['gb_charity_username'] : '' ;
		//$password = ( isset( $_POST['gb_charity_password'] ) ) ? $_POST['gb_charity_password'] : '' ;
		$notes = ( isset( $_POST['gb_payment_notes'] ) ) ? $_POST['gb_payment_notes'] : '' ;
		//$percentage = ( isset( $_POST['gb_charity_percentage'] ) && is_numeric( $_POST['gb_charity_percentage'] ) ) ? (int) $_POST['gb_charity_percentage'] : '' ;
		//$charity->set_username( $username );
		//$charity->set_password( $password );
		$charity->set_payment_notes( $notes );
		//$charity->set_percentage( $percentage );
		
		self::save_meta_box_gb_charity_authorized_users( $charity, $post_id, $post );
	}
	
	private static function save_meta_box_gb_charity_authorized_users( GB_SF_Charity $charity, $post_id, $post ) {
		if ( isset( $_POST['authorized_user'] ) && ( $_POST['authorized_user'] != '' ) ) {
			$authorized_user = $_POST['authorized_user'];
			$charity->authorize_user( $authorized_user );
		}
		if ( isset( $_POST['unauthorized_user'] ) && ( $_POST['unauthorized_user'] != '' ) ) {
			$unauthorized_user = $_POST['unauthorized_user'];
			$charity->unauthorize_user( $unauthorized_user );
		}
	}

	public static function override_template( $template ) {
		if ( GB_SF_Charity::is_charity_query() ) {
			if ( is_single() ) {
				$template = self::locate_template( array(
						'charity/single.php',
						'charity/charity.php',
						'charity.php'
					), $template );
			} elseif ( is_archive() ) {
				$template = self::locate_template( array(
						'charity/charities.php',
						'business/index.php',
						'business/archive.php',
						'charities.php'
					), $template );
			}
		}
		return $template;
	}
	
	public static function show_user_charity_section() {
		//Get mutiple charity_ids (if multiple charities assigned to user)
		$charity_ids = GB_SF_Charity::get_all_charity_ids_for_user();

		if ( $charity_ids) {
			foreach ( $charity_ids as $charity_id ) {
				echo self::_load_view_to_string( 'account/charity-info', array( 'charity_id' => $charity_id ) );
			}
		}
		
	}

	public static function get_purchase_by_charity( $charity = null, $date_range = null ) {
		if ( null == $charity ) return; // nothing more to to

		$args = array(
			'fields' => 'ids',
			'post_type' => gb_get_purchase_post_type(),
			'post_status' => 'any',
			'posts_per_page' => -1, // return this many
			'meta_query' => array(
				array(
					'key' => GB_SF_Charities::META_KEY,
					'value' => $charity,
					'compare' => '='
				)
			)
		);
		add_filter( 'posts_where', array( get_class(), 'filter_where' ) );
		$purchases = new WP_Query( $args );
		remove_filter( 'posts_where', array( get_class(), 'filter_where' ) );
		return $purchases->posts;
	}

	public function filter_where( $where = '' ) {
		// range based
		if ( isset( $_GET['range'] ) ) {
			$range = ( empty( $_GET['range'] ) ) ? 7 : intval( $_GET['range'] ) ;
			$where .= " AND post_date > '" . date( 'Y-m-d', strtotime( '-'.$range.'days' ) ) . "'";
			return $where;
		}
		// date based
		if ( isset( $_GET['from'] ) ) {
			// from
			$from = $_GET['from'];
			// to
			if ( !isset( $_GET['to'] ) || $_GET['to'] == '' ) {
				$now = time() + ( get_option( 'gmt_offset' ) * 3600 );
				$to = gmdate( 'Y-m-d', $now );
			} else {
				$to = $_GET['to'];
			}

			$where .= " AND post_date >= '".$from."' AND post_date < '".$to."'";
		}
		return $where;
	}
	
	public static function get_purchase_by_charity_filter( $charity = null, $filter_args = null ) {
		if ( null == $charity ) return; // nothing more to to

		$args = array(
			'fields' => 'ids',
			'post_type' => gb_get_purchase_post_type(),
			'post_status' => 'any',
			'posts_per_page' => -1, // return this many
			'meta_query' => array(
				array(
					'key' => GB_SF_Charities::META_KEY,
					'value' => $charity,
					'compare' => '='
				)
			)
		);
		
		if ( $filter_args )  {
			$args = array_merge( $args, $filter_args );
		}
		
		$purchases = new WP_Query( $args );
		
		return $purchases->posts;
	}
	
	private static function _load_view_to_string( $path, $args ) {
		ob_start();
		if ( !empty( $args ) ) extract( $args );
		// Check if there's a template specific file
		$file = ( file_exists( GB_SF_CHARITY_PATH . '/views/' . GBS_THEME_SLUG . '/' . $path . '.php' ) ) ? GB_SF_CHARITY_PATH . '/views/' . GBS_THEME_SLUG . '/' . $path . '.php' : GB_SF_CHARITY_PATH . '/views/prime_theme/' . $path . '.php' ;
		@include $file;
		return ob_get_clean();
	}

	public function maybe_create_donation_deal() {
		$donation_deal = get_option( self::DONATION_ITEM_ID, FALSE );
		if ( !$donation_deal ) {
			$item_id = wp_insert_post( array(
					'post_status' => 'private',
					'post_type' => Group_Buying_Deal::POST_TYPE,
					'post_title' => gb__('Donation'),
					'post_content' => 'This is a deal that will have all donations attributed to it. Keep it private or publish it after modifying the content.'
				) );
			add_option( self::DONATION_ITEM_ID, $item_id );
			$deal = Group_Buying_Deal::get_instance( $item_id );
			$deal->set_expiration_date( Group_Buying_Deal::NO_EXPIRATION_DATE );
		}
	}

	public function maybe_create_donation_attributes_on_save( $post_id, $post ) {
		// only continue if it's a charity post
		if ( $post->post_type != GB_SF_Charity::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		// ensure it's not a child deal
		if ( $post->post_parent ) {
			return;
		}

		// Get deal_id
		$deal_id = get_option( self::DONATION_ITEM_ID, FALSE );
		if ( get_post_type( $deal_id ) !== Group_Buying_Deal::POST_TYPE ) {
			return;
		}

		// check if charity already has an attribute associated
		$attribute_id = get_post_meta( $post_id, self::ATTRIBUTE_ASSOCIATION_META_KEY, TRUE );

		// Create new attribute
		if ( !$attribute_id ) {
			$args = array(
				'title' => $post->title,
				'sku' => $post_id
				);
			$att_id = Group_Buying_Attribute::new_attribute( $deal_id, $args );
			update_post_meta( $post_id, self::ATTRIBUTE_ASSOCIATION_META_KEY, $att_id );
		}
		else {
			$args = array(
				'title' => $post->title,
				'sku' => $post_id
				);
			$attribute = Group_Buying_Attribute::get_instance( $attribute_id );
			$attribute->update( $data );
		}

	}

	public function maybe_create_donation_attributes() {
		$created = get_option( 'donation_attributes_created' );
		if ( $created < time()-604800 ) { // check once a week
			$deal_id = get_option( self::DONATION_ITEM_ID, FALSE );
			if ( get_post_type( $deal_id ) !== Group_Buying_Deal::POST_TYPE ) {
				return;
			}

			$charities = Group_Buying_Post_Type::find_by_meta( GB_SF_Charity::POST_TYPE );
			foreach ( $charities as $charity_id ) {
				$attribute_id = get_post_meta( $charity_id, self::ATTRIBUTE_ASSOCIATION_META_KEY, TRUE );
				if ( !$attribute_id ) {
					$args = array(
						'title' => get_the_title( $charity_id ),
						'sku' => $charity_id
						);
					$att_id = Group_Buying_Attribute::new_attribute( $deal_id, $args );
					update_post_meta( $charity_id, self::ATTRIBUTE_ASSOCIATION_META_KEY, $att_id );
				}
			}
			update_option( 'donation_attributes_created', time() );
		}
	}

	public function get_donation_id() {
		$deal_id = get_option( self::DONATION_ITEM_ID, FALSE );
		if ( get_post_type( $deal_id ) !== Group_Buying_Deal::POST_TYPE ) {
			return FALSE;
		}
		return $deal_id;
	}

	public function get_donation_attribute_by_charity_id( $charity_id ) {
		$attribute_id = get_post_meta( $charity_id, self::ATTRIBUTE_ASSOCIATION_META_KEY, TRUE );
		return $attribute_id;
	}

	public function get_charity_id_by_attribute_id( $attribute_id ) {
		$charity_id = get_post_meta( $attribute_id, '_sku', TRUE );
		return $charity_id;
	}
}

