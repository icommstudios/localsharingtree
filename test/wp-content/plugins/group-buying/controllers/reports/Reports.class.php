<?php

/**
 * Reports Controller
 *
 * @package GBS
 * @subpackage Report
 */
class Group_Buying_Reports extends Group_Buying_Controller {
	const REPORTS_PATH_OPTION = 'gb_report_path';
	const REPORTS_PATH_CSV_OPTION = 'gb_report_csv_path';
	const REPORTS_PATH = 'gb_report_path';
	const REPORT_QUERY_VAR = 'gb_report';
	const CSV_QUERY_VAR = 'gb_report_csv';
	private static $reports_path = 'gbs_reports';
	private static $reports_csv_path = 'gbs_reports/csv';
	private static $report;
	private static $instance;

	final public static function init() {
		self::$reports_path = get_option( self::REPORTS_PATH_OPTION, self::$reports_path );
		self::$reports_csv_path = get_option( self::REPORTS_PATH_CSV_OPTION, self::$reports_csv_path );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_csv_callbacks' ), 100, 1 );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 50, 0 );
	}

	/**
	 * Register the path callback for the edit page
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_csv_callbacks( GB_Router $router ) {
		$args = array(
			'path' => self::$reports_path,
			'title' => 'GB Report',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_gbs_report' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$reports_path ).'.php', // non-default edit path
				self::get_template_path().'/report.php', // theme override
				GB_PATH.'/views/public/report.php', // default
			),
		);
		$router->add_route( self::REPORT_QUERY_VAR, $args );

		$args = array(
			'path' => self::$reports_csv_path,
			'title' => 'GB Report CSV',
			'page_callback' => array( get_class(), 'on_csv_report' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$reports_csv_path ).'.php', // non-default edit path
				self::get_template_path().'/report-csv.php', // theme override
				self::get_template_path().'/report.php', // theme override
				GB_PATH.'/views/public/report.php', // default
			),
		);
		$router->add_route( self::CSV_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_reports_paths';
		add_settings_section( $section, null, array( get_class(), 'display_paths_section' ), $page );
		register_setting( $page, self::REPORTS_PATH_OPTION );
		register_setting( $page, self::REPORTS_PATH_CSV_OPTION );
		add_settings_field( self::REPORTS_PATH_OPTION, self::__( 'Report Path' ), array( get_class(), 'display_path' ), $page, $section );
		add_settings_field( self::REPORTS_PATH_CSV_OPTION, self::__( 'Report Path for CSV Downloads' ), array( get_class(), 'display_csv_path' ), $page, $section );
	}

	public static function display_paths_section() {
		echo self::__( '<h4>Customize the Report paths</h4>' );
	}

	public static function display_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="'.self::REPORTS_PATH_OPTION.'" id="'.self::REPORTS_PATH_OPTION.'" value="' . esc_attr( self::$reports_path ) . '"  size="40" /><br />';
	}

	public static function display_csv_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="'.self::REPORTS_PATH_CSV_OPTION.'" id="'.self::REPORTS_PATH_CSV_OPTION.'" value="' . esc_attr( self::$reports_csv_path ) . '"  size="40" /><br />';
	}

	public static function on_gbs_report() {
		$report = self::get_instance();
		$report->view_report();
	}

	public static function on_csv_report() {
		$report = self::get_instance();
		$report->download_csv();
	}

	/*
	 * Singleton Design Pattern
	 * ------------------------------------------------------------- */
	final protected function __clone() {
		// cannot be cloned
		trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
	}

	final protected function __sleep() {
		// cannot be serialized
		trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
	}
	public static function get_instance() {
		if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		self::$report = isset( $_GET['report'] ) ? $_GET['report'] : '';
	}

	public function view_report( $report_type = '' ) {
		if ( $report_type == '' ) {
			$report_type = self::$report;
		}
		remove_filter( 'the_content', 'wpautop' );
		$report = Group_Buying_Report::get_instance( $report_type );
		$columns = $report->columns;
		$records = $report->records;
		self::load_view( 'reports/view', array( 'columns' => $columns, 'records' => $records ) );
	}

	public function download_csv( $report_type = '' ) {
		if ( $report_type == '' ) {
			$report_type = self::$report;
		}
		$report = Group_Buying_Report::get_instance( $_GET['report'] );
		$columns = $report->columns;
		$records = $report->records;
		self::load_view( 'reports/csv', array( 'filename' => 'gbs_report.csv', 'columns' => $columns, 'records' => $records ), FALSE );
		exit();
	}

	public function get_title( $title ) {
		$report_name = str_replace( '_', ' ', self::$report );
		return apply_filters( 'gb_reports_get_title', self::__( ucwords( $report_name . " report" ) ), self::$report );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the report page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return add_query_arg( array( 'report' => self::$report ), trailingslashit( home_url() ).trailingslashit( self::$reports_path ) );
		} else {
			$router = GB_Router::get_instance();
			return add_query_arg( array( 'report' => self::$report ), $router->get_url( self::REPORTS_PATH_OPTION ) );
		}
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the report csv page
	 */
	public static function get_csv_url() {
		if ( self::using_permalinks() ) {
			return add_query_arg( array( 'report' => self::$report ), trailingslashit( home_url() ).trailingslashit( self::$reports_csv_path ) );
		} else {
			$router = GB_Router::get_instance();
			return add_query_arg( array( 'report' => self::$report ), $router->get_url( self::REPORTS_PATH_CSV_OPTION ) );
		}
	}

}
