<?php
// Custom search
// By StudioFidelis.com

class SF_Custom_Search extends Group_Buying_Controller {
	
	const DEBUG = true;
	
	static $query_instance;
	
	public static function init() {
		global $wp_version;
		
		if ( is_admin() ) return; 
		
		
		//Filter the search query
		add_filter( 'pre_get_posts', array(get_class(), 'filter_search_query'), 99, 1);
		add_filter( 'posts_where', array( get_class(), 'filter_search_where' ), 99, 2 );
		add_filter( 'posts_join', array( get_class(), 'filter_search_join' ) );
		add_filter( 'posts_request', array( get_class(), 'filter_search_distinct' ) );
		
		//Filter wp search template
		add_filter('template_include', array(get_class(), 'search_template'), 10, 1);
		
	}
	
	//Filter search query vars
	public function filter_search_query($query) {
		// Make sure a blank serach is treated as a search
		if (isset($_GET['s']) && empty($_GET['s']) && $query->is_main_query()){
			$query->is_search = true;
			$query->is_home = false;
		}
		if ( $query->is_search && $query->is_main_query() && !empty($query->query_vars['s']) ) {
			
			//$query->set('search_term', $query->query_vars['s'] ); //set our custom search term
			//$query->set('s', '' ); //unset the wordpress "s"
	
		}
		return $query;
	}
	
	// Build the search where  query
	public function filter_search_where( $where, $wp_query ) {
		global $wpdb;
		
		if ( !$wp_query->is_search() ) return $where;
		
		//Save query instance (used in other later filters)
		self::$query_instance = &$wp_query;
		
		if ( !self::$query_instance->query_vars['s'] ) return $where;
		$search_terms = explode(' ', self::$query_instance->query_vars['s']);
		
		//Start over
		$where = '';
		$search = '';
		
		//Setup where - limit post types
		$where = " AND $wpdb->posts.post_type IN ('gb_deal', 'gb_merchant', 'gb_charities') AND ($wpdb->posts.post_status = 'publish')";
		
		//Search title
		$searchand = '';
		$add_search = '';
		foreach ( $search_terms as $term ) {
			$term = addslashes_gpc( $term );
			$add_search .= " $searchand ($wpdb->posts.post_title LIKE '%$term%')";
			$searchand = ' AND ';
		}
		if ( !empty( $add_search ) ) {
			if ( !empty( $search ) ) { //we already have a search group
				$search .= " OR ( $add_search ) "; 
			} else {
				$search .= " ( $add_search ) ";	
			}
		}
		
		//Search terms
		$searchand = '';
		$add_search = '';
		foreach ( $search_terms as $term ) {
			$term = addslashes_gpc( $term );
			$add_search .= " $searchand (tter.name LIKE '%$term%')";
			$searchand = ' AND ';
		}
		if ( !empty( $add_search ) ) {
			if ( !empty( $search ) ) { //we already have a search group
				$search .= " OR ( $add_search ) "; 
			} else {
				$search .= " ( $add_search ) ";	
			}
		}
		
		//do we have anything to search
		if ( !empty( $search ) ) {
			$where .= " AND ( "; //Start search AND wrapper
			$where .= " ( $search ) ";	
			$where .= " ) ";	//End serach AND wrapper
		}
	
		
		if ( self::DEBUG ) error_log('new search query: '.$where);
		return $where;
	}
	
	//Add taxonomy to join (so its searchable)
	public function filter_search_join( $join ) {
		global $wpdb;

		if ( !empty( self::$query_instance->query_vars['s'] ) ) {

			// if we're searching for categories
			//$on[] = "ttax.taxonomy = 'category'";
			//$on[] = "ttax.taxonomy = 'post_tag'";
			
			// if we're searching custom taxonomies
			$all_taxonomies = get_object_taxonomies( 'gb_deal' );
			foreach ( $all_taxonomies as $taxonomy ) {
				if ( $taxonomy == 'post_tag' || $taxonomy == 'category' )
					continue;
				$on[] = "ttax.taxonomy = '".addslashes( $taxonomy )."'";
			}
			$all_taxonomies = get_object_taxonomies( 'gb_merchant' );
			foreach ( $all_taxonomies as $taxonomy ) {
				if ( $taxonomy == 'post_tag' || $taxonomy == 'category' )
					continue;
				$on[] = "ttax.taxonomy = '".addslashes( $taxonomy )."'";
			}
			
			// build our final string
			$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';

			$join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
		}
		
		return $join;
	}
	
	//Make results distinct to avoid duplicates
	public function filter_search_distinct( $query ) {
		global $wpdb;
		if ( !empty( self::$query_instance->query_vars['s'] ) ) {
			if ( strstr( $query, 'DISTINCT' ) ) {}
			else {
				$query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
			}
		}
		return $query;
	}
	
	//Show search results template
	public function search_template($template)   {    
		global $wp_query;   
		
		if ( $wp_query->is_search() ) {
			$new_template = locate_template('archive-search.php');  
			if ( $new_template ) {
				return $new_template;
			}
		}
	 	return $template;
	}

	
}
SF_Custom_Search::init();