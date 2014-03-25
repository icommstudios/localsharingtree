<?php

/**
 * Record Controller
 *
 * @package GBS
 * @subpackage Base
 */
class Group_Buying_Records extends Group_Buying_Controller {
	const SETTINGS_PAGE = 'gbs_records';
	const RECORD_PURGE_NONCE = 'gb_record_purge_nonce';
	private static $instance;

	public static function get_admin_page( $prefixed = TRUE ) {
		return ( $prefixed ) ? self::TEXT_DOMAIN . '/' . self::SETTINGS_PAGE : self::SETTINGS_PAGE ;
	}

	public static function init() {
		add_action( 'gb_new_record', array( get_class(), 'new_record' ), 10, 6 );

		add_action( 'admin_menu', array( get_class(), 'add_admin_page' ), 10, 0 );

		// Purge
		add_action( 'admin_init', array( get_class(), 'maybe_purge_records' ) );

		self::add_admin_page();
	}

	/**
	 * Add menu under tools.
	 */
	public static function add_admin_page() {
		// Option page
		$args = array(
			'parent' => 'tools.php',
			'slug' => self::SETTINGS_PAGE,
			'title' => self::__( 'Records' ),
			'menu_title' => self::__( 'GBS Records' ),
			'weight' => 10,
			'reset' => FALSE, 
			'section' => '',
			'callback' => array( get_class(), 'display_table' )
			);
		do_action( 'gb_settings_page', $args );
	}

	public static function new_record( $data = array(), $type = 'mixed', $title = '', $author_id = 1, $associate_id = -1, $deprecated_data_variable = array() ) {
		if ( !empty( $deprecated_data_variable ) ) {
			$data = $deprecated_data_variable;
		}
		$post = array(
			'post_title' => $title,
			'post_author' => $author_id,
			'post_status' => 'pending',
			'post_type' => Group_Buying_Record::POST_TYPE,
			'post_parent' => $associate_id
		);
		$id = wp_insert_post( $post );

		if ( !is_wp_error( $id ) ) {
			$record = Group_Buying_Record::get_instance( $id );
			$record->set_data( $data );
			$record->set_associate_id( $associate_id );
			$record->set_type( $type );
		}
		return $id;
	}

	public function maybe_purge_records() {
		if ( !isset( $_REQUEST[self::RECORD_PURGE_NONCE] ) )
			return;
		
		if ( !wp_verify_nonce( $_REQUEST[self::RECORD_PURGE_NONCE], self::RECORD_PURGE_NONCE ) )
			return;
		
		if ( isset( $_GET['purge_records'] ) )
			self::purge_records( $_GET['purge_records'] );
	}

	public function purge_records( $type = 0 ) {
		$args = array(
			'post_type' => Group_Buying_Record::POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		if ( $type ) {
			$tax_query = array(
					'tax_query' => array(
							array(
								'taxonomy' => Group_Buying_Record::TAXONOMY,
								'field' => 'id',
								'terms' => $type
							)
						)
				);
			$args = array_merge( $args, $tax_query );
		}
		$records = get_posts( $args );
		foreach ( $records as $record_id ) {
			wp_delete_post( $record_id, TRUE );
		}
	}

	/*
	 * Singleton Design Pattern
	 * ------------------------------------------------------------- */
	private function __clone() {
		// cannot be cloned
		trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
	}
	private function __sleep() {
		// cannot be serialized
		trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
	}
	public static function get_instance() {
		if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() { }

	public function sort_callback( $a, $b ) {
		if ( $a == $b ) {
			return 0;
		}
		return ( $a < $b ) ? 1 : -1;
	}

	public static function display_table() {
		add_thickbox();
		//Create an instance of our package class...
		$wp_list_table = new Group_Buying_Records_Table();
		//Fetch, prepare, sort, and filter our data...
		$wp_list_table->prepare_items();
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			jQuery(".show_record_detail").live('click', function(e) {
				e.preventDefault();
				var record_id = $(this).parent().attr("id");
				$('#'+record_id).remove();
				$('#record_detail_'+record_id).toggle();
			});
		});
	</script>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>
			<?php gb_e('GBS Records') ?>
		</h2>

		<form id="payments-filter" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php $wp_list_table->display() ?>
		</form>
	</div>
	<?php
	}

}
