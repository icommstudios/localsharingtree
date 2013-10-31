<?php

class Group_Buying_Deals_Preview extends Group_Buying_Controller {

	const NONCE_OPTION = 'gb_deal_preview_option';
	private static $id;
	private static $hookname;

	public static function init() {
		if ( !is_admin() ) {
			add_action( 'init', array( get_class(), 'show_preview' ) );
		} else {
			add_action( 'add_meta_boxes', array( get_class(), 'add_meta_boxes' ) );
			add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		}
	}

	public static function add_meta_boxes() {
		add_meta_box( 'gb_deal_previews', self::__( 'Previews' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'side', 'low' );
	}

	public static function show_meta_box( $post, $metabox ) {
		$deal = Group_Buying_Deal::get_instance( $post->ID );
		switch ( $metabox['id'] ) {
		case 'gb_deal_previews':
			self::show_meta_box_gb_deal_previews( $deal, $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	private static function show_meta_box_gb_deal_previews( Group_Buying_Deal $deal, $post, $metabox ) {
		self::load_view( 'meta_boxes/deal-preview', array(
				'post' => $post,
				'deal_preview' => self::has_key( $deal ),
				'voucher_preview_url' => self::get_voucher_preview_link( $deal ),
				'deal_preview_url' => self::get_preview_link( $deal ),
			), FALSE );
	}

	public static function save_meta_boxes( $post_id, $post ) {
		// Don't save meta boxes when the importer is used.
		if ( isset( $_GET['import'] ) && $_GET['import'] == 'wordpress' ) {
			return;
		}
		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		// Since the save_box_gb_deal_[meta] functions don't check if there's a _POST, a nonce was added to safe gaurd save_post actions from ... scheduled posts, etc.
		if ( !isset( $_POST['gb_deal_submission'] ) && ( empty( $_POST ) || !check_admin_referer( 'gb_save_metaboxes', 'gb_save_metaboxes_field' ) ) ) {
			return;
		}
		// save meta boxes
		$deal = Group_Buying_Deal::get_instance( $post_id );
		self::save_meta_box_gb_deal_preview( $deal, $post_id, $post );
	}

	public static function save_meta_box_gb_deal_preview( Group_Buying_Deal $deal, $post_id, $post ) {
		if ( isset( $_POST['deal_preview'] ) && 'TRUE' == $_POST['deal_preview'] ) {
			if ( !self::has_key( $deal ) ) {
				$deal->set_preview_key( self::create_key() );
			}
		} else {
			$deal->set_preview_key( null );
		}
		return;
	}

	public static function get_preview_link( Group_Buying_Deal $deal ) {
		$key = $deal->get_preview_key();
		return add_query_arg( array( 'p' => $deal->get_id(), 'post_type' => get_post_type( $deal->get_id() ), 'key' => $key, 'preview' => 'true' ), trailingslashit( get_option( 'home' ) ) );
	}

	public static function get_voucher_preview_link( Group_Buying_Deal $deal ) {
		$key = $deal->get_preview_key();
		return add_query_arg( array( 'deal_id' => $deal->get_id(), 'key' => $key, 'voucher_preview' => 'true' ), trailingslashit( get_option( 'home' ) ) );
	}

	public static function has_key( Group_Buying_Deal $deal ) {
		$private_key = $deal->get_preview_key();
		if ( $private_key != '' ) {
			return TRUE;
		}
		return;
	}

	public static function verify_key( $key = NULL, $deal_id ) {
		$deal = Group_Buying_Deal::get_instance( $deal_id );
		$private_key = $deal->get_preview_key();
		if ( $key == $private_key ) {
			return TRUE;
		}
		return;
	}

	/**
	 * Create Key
	 */
	public static function create_key() {
		return wp_generate_password( 18, FALSE );
	}

	/**
	 * Show the previews
	 */
	public static function show_preview() {
		if ( !is_admin() && isset( $_GET['preview'] ) && $_GET['preview'] && isset( $_GET['key'] ) ) {
			$deal_id = (int)$_GET['p'];
			if ( !self::verify_key( $_GET['key'], $deal_id ) ) {
				wp_die( self::__( 'Sorry but you do not have permission to preview this deal.' ) );
			}
			add_filter( 'posts_results', array( get_class(), 'fake_publish' ) );
		} elseif ( !is_admin() && isset( $_GET['voucher_preview'] ) && $_GET['voucher_preview'] && isset( $_GET['key'] ) ) {
			$deal_id = (int)$_GET['deal_id'];
			if ( !self::verify_key( $_GET['key'], $deal_id ) ) {
				wp_die( self::__( 'Sorry but you do not have permission to preview this voucher.' ) );
			}
			add_filter( 'template_redirect', array( get_class(), 'voucher_preview' ) );
		}
	}

	/**
	 * Fake the post being published so we don't have to do anything *too* hacky to get it to load the preview
	 */
	public static function fake_publish( $posts ) {
		$posts[0]->post_status = 'publish';
		return $posts;
	}

	// FUTURE move to voucher class
	public static function voucher_preview( $template ) {
		self::login_required();
		$deal_id = (int)$_GET['deal_id'];
		$deal = Group_Buying_Deal::get_instance( $deal_id );
		$template = self::locate_template( array(
				'account/voucher.php',
				'vouchers/single-voucher.php',
				'vouchers/voucher.php',
				'voucher.php',
			), $template );

		$content = '$id = '.$deal_id.'; ?>';
		$content .= file_get_contents( $template );
		// Title
		$content = str_replace( '<?php the_title(); ?>', '<?php echo get_the_title($id); ?>', $content );
		$content = str_replace( 'get_the_title()', 'get_the_title($id)', $content );
		// Logo
		$logo = $deal->get_voucher_logo();
		if ( !empty( $logo ) ) {
			$content = str_replace( 'gb_has_voucher_logo()', '__return_true()', $content );
			$content = str_replace( 'gb_voucher_logo_image();', '?><img src="'.$logo.'" /><?php', $content );
		} else {
			$content = str_replace( 'gb_has_voucher_logo()', '__return_false()', $content );
		}
		// Serial
		$serial = $deal->get_next_serial();
		if ( $serial == '' ) {
			$random = wp_generate_password( 12, FALSE, FALSE );
			$serial = implode( '-', str_split( $random, 4 ) );
		}
		$content = str_replace( '<?php gb_voucher_code(); ?>', $serial, $content );
		// QR Code
		$content = str_replace( '<?php echo urlencode( gb_get_voucher_claim_url( gb_get_voucher_security_code(), FALSE ) ) ?>', home_url(), $content );
		// Exp.
		$format = get_option( "date_format" );
		$expiration = ( $deal->get_voucher_expiration_date() ) ? $deal->get_voucher_expiration_date() : time()+60*60*24*14;
		$content = str_replace( '<?php gb_voucher_expiration_date(); ?>', date( $format, $expiration ), $content );
		// fine print.
		$content = str_replace( '<?php gb_voucher_fine_print() ?>', $deal->get_fine_print(), $content );
		// security code.
		$content = str_replace( '<?php gb_voucher_security_code(); ?>', '<?php echo '.$deal_id.' . "-" . strtoupper(wp_generate_password(5, FALSE, FALSE)); ?>', $content );
		// Locations
		$locals = $deal->get_voucher_locations();
		$locations = '';
		if ( !empty( $locals ) ) {
			$locations .= '<ul class="voucher_locations"><li>';
			$locations .= implode( '</li><li>', $locals );
			$locations .= '</li></ul>';
		}
		$content = str_replace( '<?php gb_voucher_locations() ?>', $locations, $content );
		// How to use.
		$content = str_replace( '<?php gb_voucher_usage_instructions() ?>', $deal->get_voucher_how_to_use(), $content );
		// Map
		$content = str_replace( '<?php gb_voucher_map() ?>', $deal->get_voucher_map(), $content );

		$content = apply_filters( 'gb_voucher_preview_content', $content, $deal_id );
		eval( $content );
		die();
	}

}