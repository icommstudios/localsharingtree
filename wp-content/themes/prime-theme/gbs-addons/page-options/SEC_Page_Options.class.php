<?php 

if ( !class_exists( 'SEC_Controller' ) || !class_exists( 'SEC_Offers' ) )
	return;


/**
* Page options for theme features, etc.
*/
class SEC_Page_Options extends SEC_Controller {
	const OFFER_TYPE = 'sec_page_option_offer_type';
	const FILTER = 'sec_page_option_filter_offers';
	const FILTER_LOCAL = 'sec_page_option_location';
	const LAYOUT = 'sec_page_option_layout';
	
	public static function init() {
		// Meta boxes
		add_action( 'admin_init', array( get_class(), 'register_meta_boxes' ) );
		
		// Add help
		if ( is_admin() && ( SEC_THEME_SLUG == 'modular_theme' ) ) {
			add_action( 'admin_menu', array( get_class(), 'admin_menu' ) );
		}

	}

	/////////////////
	// Meta boxes //
	/////////////////

	public static function register_meta_boxes() {
		// Offer specific
		$args = array(
				'sec_page_options' => array(
						'title' => 'SEC Offers Template Options',
						'context' => 'side',
						'show_callback' => array( __CLASS__, 'show_meta_box_page_options' ),
						'save_callback' => array( __CLASS__, 'save_meta_box_page_options' ),
						'priority' => 'default'
					),
			);
		do_action( 'gb_meta_box', $args, 'page' );
	}

	/**
	 * Display the page options meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $offer
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	public static function show_meta_box_page_options( $empty, $post, $metabox ) {
		$offer_type = self::page_offer_type_filter( $post->ID );
		$offer_filter = self::page_offer_filter( $post->ID );
		$location = self::get_page_offer_filtering_local( $post->ID );
		$layout = self::get_page_offer_view( $post->ID );

		?>
			<p class="description"><?php sec_e( 'Options for the offer page template.' ); ?></p>
			<p><strong><?php sec_e('Offer Type') ?></strong></p>
			<label class="screen-reader-text" for="offer_type"><?php sec_e('Offer Type') ?></label><select name="offer_type" id="offer_type">
				<option value="all" <?php selected( 'default', $offer_type ) ?>><?php sec_e('All Types'); ?></option>
				<?php foreach ( SEC_Offers::get_offer_types() as $slug => $name ): ?>
					<option value="<?php echo $slug ?>" <?php selected( $slug, $offer_type ) ?>><?php echo $name ?></option>
				<?php endforeach ?>
			</select>
			<p><strong><?php sec_e('Offer Filter') ?></strong></p>
			<label class="screen-reader-text" for="offer_filter"><?php sec_e('Offer Filter') ?></label><select name="offer_filter" id="offer_filter">
				<option value="publish" <?php selected( 'publish', $offer_filter ) ?>><?php sec_e('Current Offers'); ?></option>
				<option value="expired" <?php selected( 'expired', $offer_filter ) ?>><?php sec_e('Completed Offers'); ?></option>
				<option value="future" <?php selected( 'future', $offer_filter ) ?>><?php sec_e('Future Offers'); ?></option>
			</select>
			<p>
				<label for="offer_filter_location">
					<input name="offer_filter_location" id="offer_filter_location" type="checkbox" <?php checked( $location, 'true' ) ?> value="true" />
					<?php sec_e('Filter based on preferred location selected by user (when available).') ?>
				</label>
			</p>
			<?php if ( apply_filters( 'sec_theme_has_compact_offer_view', FALSE ) ): ?>
				<p>
					<label for="page_template_layout">
						<input name="page_template_layout" id="page_template_layout" type="checkbox" <?php checked( $layout, 'compact' ) ?> value="true" />
						<?php sec_e('Compact Offer Layout') ?>
					</label>
				</p>
			<?php endif ?>
		<?php
	}

	/**
	 * Save the page options meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $offer
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	public static function save_meta_box_page_options( $empty, $post_id, $post ) {
		$offer_type = ( isset( $_POST['offer_type'] ) ) ? $_POST['offer_type'] : '' ;
		update_post_meta( $post_id, self::OFFER_TYPE, $offer_type );

		$offer_filter = ( isset( $_POST['offer_filter'] ) ) ? $_POST['offer_filter'] : '' ;
		update_post_meta( $post_id, self::FILTER, $offer_filter );

		$offer_filter_location = ( isset( $_POST['offer_filter_location'] ) && $_POST['offer_filter_location'] == 'true' ) ? 'true' : 'false' ;
		update_post_meta( $post_id, self::FILTER_LOCAL, $offer_filter_location );

		$page_template_layout = ( isset( $_POST['page_template_layout'] ) && $_POST['page_template_layout'] == 'true' ) ? 'compact' : 'default' ;
		update_post_meta( $post_id, self::LAYOUT, $page_template_layout );
	}

	public static function page_offer_type_filter( $post_id = 0 ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		return get_post_meta( $post_id, self::OFFER_TYPE, TRUE );
	}

	public static function page_offer_filter( $post_id = 0 ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		return get_post_meta( $post_id, self::FILTER, TRUE );
	}

	public static function get_page_offer_filtering_local( $post_id = 0 ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		return get_post_meta( $post_id, self::FILTER_LOCAL, TRUE );
	}

	public static function get_page_offer_view( $post_id = 0 ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		return get_post_meta( $post_id, self::LAYOUT, TRUE );
	}

	public static function is_page_offer_filtering_local( $post_id = 0 ) {
		$option = self::get_page_offer_filtering_local( $post_id );
		return ( $option == 'true' );
	}

	public static function is_page_compact_offer_view( $post_id = 0 ) {
		$option = self::get_page_offer_view( $post_id );
		return ( $option == 'compact' );
	}

	public static function admin_menu() {
		// Mixed
		add_action( 'load-edit.php', array( get_class(), 'help_section' ) );
		add_action( 'load-post.php', array( get_class(), 'help_section' ) );
		add_action( 'load-post-new.php', array( get_class(), 'help_section' ) );
	}

	public static function help_section() {
		$screen = get_current_screen();
		$post_id = isset( $_GET['post'] ) ? (int)$_GET['post'] : FALSE;
		if ( $post_id ) {
			$post_type = get_post_type( $post_id );
		} else {
			$post_type = ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) ? $_REQUEST['post_type'] : null ;
		}
		/**
		 * ***********************************
		 *                Offers
		 * ***********************************
		 */
		if ( $post_type == 'page' ) {
			$screen->add_help_tab( array(
					'id'      => 'offers-template-help', // This should be unique for the screen.
					'title'   => self::__( 'SEC Offers Template Options' ),
					'content' =>
					'<p><strong>' . self::__( 'Template' ) . '</strong></p>' .
					'<p>' . self::__( 'To create a page that displays a certain type of offers, select the "Offers (options below)" template. You can use this template for one page or several with different options for maximum flexibility.' ) . '</p>' .
					'<p><strong>' . self::__( 'Offer Type' ) . '</strong></p>' .
					'<p>' . self::__( 'Your page can either display every available offer or offers of a specific type.' ) . '</p>' .
					'<p><strong>' . self::__( 'Offer Filter' ) . '</strong></p>' .
					'<p>' . self::__( 'Your page can display current offers of the type above that are current, completed (expired), or future (offer is schedule to publish at a specific page). Users can visit the page of completed offers and will only be shown a list of future offers.' ) . '</p>' .
					'<p><strong>' . self::__( 'Options' ) . '</strong></p>' .
					'<p>' . self::__( 'You can also set the page to dynamically display only offers from the current user&rsquo;s location. You can also display the offers in a more compact layout.' ) . '</p>'
				) );

			$screen->set_help_sidebar(
				'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
				'<p>' . self::__( '<a href="http://smartecart.com/forum/" target="_blank">Support Forums</a>' ) . '</p>'
			);
		}

	}

}
add_action( 'init', array( 'SEC_Page_Options', 'init' )  );