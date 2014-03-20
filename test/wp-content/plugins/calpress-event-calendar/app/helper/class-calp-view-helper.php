<?php
//
//  class-calp-view-helper.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_View_Helper class
 *
 * @package Helpers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_View_Helper {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * display function
	 *
	 * Display the view specified by file $file and passed arguments $args.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display( $file = false, $args = array() ) {
		if( ! $file || empty( $file ) ) {
			throw new Calp_File_Not_Provided( "You need to specify a view file." );
		}

		$file = CALP_VIEW_PATH . "/" . $file;

		if( ! file_exists( $file ) ) {
			throw new Calp_File_Not_Found( "The specified view file doesn't exist." );
		} else {
			extract( $args );
			require( $file );
		}
	}

	/**
	 * display_css function
	 *
	 * Renders the given stylesheet inline. If stylesheet has already been
	 * displayed once before with the same set of $args, does not display
	 * it again.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_css( $file = false, $args = array() ) {
		static $displayed = array();
		static $num = 0;

		if( ! $file || empty( $file ) ) {
			throw new Calp_File_Not_Provided( 'You need to specify a css file.' );
		}

		$file = CALP_CSS_PATH . "/" . $file;

		if( isset( $displayed[$file] ) && $displayed[$file] === $args )	// Skip if already displayed
			return;

		if( ! file_exists( $file ) ) {
			throw new Calp_File_Not_Found( "The specified css file doesn't exist." );
		} else {
			$displayed[$file] = $args;	// Flag that we've displayed this file with these args

			extract( $args );
			echo '<style type="text/css">';
			require( $file );
			echo '</style>';
		}
	}

	/**
	 * display_js function
	 *
	 * Renders the given script inline. If script has already been displayed
	 * once before with the same set of $args, does not display it again.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function display_js( $file = false, $args = array() ) {
		static $displayed = array();

		if( ! $file || empty( $file ) ) {
			throw new Calp_File_Not_Provided( "You need to specify a js file." );
		}

		$file = CALP_JS_PATH . "/" . $file;

		if( $displayed[$file] === $args)	// Skip if already displayed
			return;

		if( ! file_exists( $file ) ) {
			throw new Calp_File_Not_Found( "The specified js file doesn't exist." );
		} else {
			$displayed[$file] = $args;	// Flag that we've displayed this file with these args

			extract( $args );
			echo '<script type="text/javascript" charset="utf-8">';
			echo '/* <![CDATA[ */';
			require( $file );
			echo '/* ]]> */';
			echo '</script>';
		}
	}

	/**
	 * get_view function
	 *
	 * Return the output of a view as a string rather than output to response.
	 *
	 * @param string $file
	 * @param array $args
	 *
	 * @return void
	 **/
	function get_view( $file = false, $args = array() ) {
		ob_start();
		$this->display( $file, $args );
		return ob_get_clean();
	}

	/**
	 * json_response function
	 *
	 * Utility for properly outputting JSON data as an AJAX response.
	 *
	 * @param array $data
	 *
	 * @return void
	 **/
	function json_response( $data ) {
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Content-type: application/json' );

		// Output JSON-encoded result and quit
		echo json_encode( $data );
		exit;
	}

	/**
	 * get_frontend_googlemap function
	 *
	 * Gets an URL for a Google map to be displayed on the front end if the address is valid
	 *
	 * @param string $address
	 *
	 * @return string, false if fails
	 **/

    function get_frontend_googlemap ( $address ) {
        $address = urlencode($address);
        $key = "ABQIAAAAaARX5dVkfKIs2CDutUe80BQK941FY3jFBzxgzqsv_t2EudznNhT6W__kKRvCXBK994wTccGETDztGw";
        $url = "http://maps.google.com/maps/geo?q=".$address."&output=json&key=".$key;

        if ( extension_loaded('curl') ) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec( $ch );
        } else {
            $data = implode ( '', file ( $url ));
        }
        
        $geo_json = json_decode($data, true);

        if ( $geo_json['Status']['code'] !== 200 )
            return false;

        $latitude = $geo_json['Placemark'][0]['Point']['coordinates'][0];
        $longitude = $geo_json['Placemark'][0]['Point']['coordinates'][1];

        //$mapUrl  = "http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;aq=0&amp;output=embed&amp;ie=UTF8&amp;hq=&amp;t=m&amp;z=13&amp;iwloc=A";
        $mapUrl = "&amp;q=".$address."&amp;hnear=".$address."&amp;ll=".$longitude.",".$latitude;

        return $mapUrl;
    }

}
// END class
