<?php
/*
Plugin Name: Team Showcase
Plugin URI: http://www.cmoreira.net/team-showcase
Description: This plugin allows you to manage the members of your team/staff and display them in multiple ways.
Author: Carlos Moreira
Version: 1.2.6.1
Author URI: http://cmoreira.net
*/

//Last modified: February 9 2014
//Settings page fix
//Order get_terms by slug for filter
//Fixed alt attribute
//Added new font icon for WP 3.8+ versions
//filter to display all entries in admin
//pagination option for table layout 
//search options
//added filter to pager layout



//include advanced settings
require_once dirname( __FILE__ ) . '/advanced-options.php';
//util functions
require_once dirname( __FILE__ ) . '/utils.php';
//shortcode generator functions
require_once dirname( __FILE__ ) . '/shortcode-generator.php';
//single page settings and functions
require_once dirname( __FILE__ ) . '/single-page-build.php';
//default settings page
require_once dirname( __FILE__ ) . '/settings-page.php';
//ordering code
require_once dirname( __FILE__ ) . '/ordering-code.php';
// search widget code
require_once dirname(__FILE__) . '/search-widget.php';

//count for multiple pager layouts in same page
$tshowcase_pager_count = 0;

//Adding the necessary actions to initiate the plugin
add_action('init', 'register_cpt_tshowcase' );
add_action('admin_init', 'register_tshowcase_settings' );
add_action('admin_menu' , 'tshowcase_shortcode_page_add');
add_action('admin_menu' , 'tshowcase_admin_page');


//runs only when plugin is activated to flush permalinks
register_activation_hook(__FILE__, 'tshowcase_flush_rules');
function tshowcase_flush_rules(){
	//register post type
	register_cpt_tshowcase();
	//and flush the rules.
	flush_rewrite_rules();
}

//Add support for post-thumbnails in case theme does not
add_action('init' , 'tshowcase_add_thumbnails_for_cpt');

function tshowcase_add_thumbnails_for_cpt() {

    global $_wp_theme_features;

   if($_wp_theme_features['post-thumbnails']==1) {
		return;		
	  }	
	  
	  if(is_array($_wp_theme_features['post-thumbnails'][0]) && count($_wp_theme_features['post-thumbnails'][0]) >= 1) {
		array_push($_wp_theme_features['post-thumbnails'][0],'tshowcase');
		return;
		}
	if( empty($_wp_theme_features['post-thumbnails']) ) {
        $_wp_theme_features['post-thumbnails'] = array( array('tshowcase') );
		return;
	}
}


//Add New Thumbnail Size
$tshowcase_crop = false;
$tshowcase_options = get_option('tshowcase-settings');
if($tshowcase_options['tshowcase_thumb_crop']=="true") {
$tshowcase_crop = true;
}
add_image_size( 'tshowcase-thumb', $tshowcase_options['tshowcase_thumb_width'], $tshowcase_options['tshowcase_thumb_height'], $tshowcase_crop);


//Add new Image column 
function tshowcase_columns_head($defaults) {
	global $post;
    if ($post->post_type == 'tshowcase') {
	$defaults['featured_image'] = 'Image';
	//if we want the order to display
	//$defaults['order'] = '<a href="&orderby=menu_order"><span>Order</span><span class="sorting-indicator"></span></a>';
	}
	return $defaults;
}




// SHOW THE FEATURED IMAGE in admin
function tshowcase_columns_content($column_name, $post_ID) {
	
	global $post;
    if ($post->post_type == 'tshowcase') {
		if ($column_name == 'featured_image') {		
			echo get_the_post_thumbnail($post_ID, array(50,50));		
		}
		
		//if we want the order to display
		/* if ($column_name == 'order') {		
			echo $post->menu_order;		
		}*/
		 
		
	}
}

add_filter('manage_posts_columns', 'tshowcase_columns_head');
add_action('manage_posts_custom_column', 'tshowcase_columns_content', 10, 2);


//register the custom post type for the logos showcase
function register_cpt_tshowcase() {

	$options = get_option('tshowcase-settings');
	if(!is_array($options)) {
			tshowcase_defaults();
			$options = get_option('tshowcase-settings');
		}
		
	$name = $options['tshowcase_name_singular'];
	$nameplural = $options['tshowcase_name_plural'];
	$slug = $options['tshowcase_name_slug'];
	$singlepage = $options['tshowcase_single_page'];
	$exclude_from_search = (isset($options['tshowcase_exclude_from_search']) ? true : false);

    $labels = array( 
        'name' => _x( $nameplural, 'tshowcase' ),
        'singular_name' => _x( $name, 'tshowcase' ),
        'add_new' => _x( 'Add New '.$name, 'tshowcase' ),
        'add_new_item' => _x( 'Add New '.$name, 'tshowcase' ),
        'edit_item' => _x( 'Edit '.$name, 'tshowcase' ),
        'new_item' => _x( 'New '.$name, 'tshowcase' ),
        'view_item' => _x( 'View '.$name, 'tshowcase' ),
        'search_items' => _x( 'Search '.$nameplural, 'tshowcase' ),
        'not_found' => _x( 'No '.$nameplural.' found', 'tshowcase' ),
        'not_found_in_trash' => _x( 'No '.$nameplural.' found in Trash', 'tshowcase' ),
        'parent_item_colon' => _x( 'Parent '.$name.':', 'tshowcase' ),
        'menu_name' => _x( $nameplural, 'tshowcase' ),
    );
	
	$singletrue = true;
	if($singlepage=="false") { $singletrue = false; }
	

	
    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,        
        'supports' => array( 'title', 'thumbnail', 'custom-fields', 'editor','page-attributes'),
        'public' => $singletrue,
        'show_ui' => true,
        'show_in_menu' => true,       
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => $exclude_from_search,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post',
		 'menu_icon' => plugins_url( 'img/icon16.png', __FILE__ ),
		 //'menu_position' => 17,
		 'rewrite' => array( 'slug' => $slug )

    );

    register_post_type( 'tshowcase', $args );
}


//register custom category

// WP Menu Categories
add_action( 'init', 'tshowcase_build_taxonomies', 0 );

function tshowcase_build_taxonomies() {
	
	$options = get_option('tshowcase-settings');	
	$categories = $options['tshowcase_name_category'];
	
    register_taxonomy( 'tshowcase-categories', 'tshowcase', array( 'hierarchical' => true, 'label' => $categories, 'query_var' => true, 'rewrite' => true ) );
}



//change Title Info

function tshowcase_change_default_title( $title ){
     $screen = get_current_screen();
	 $options = get_option('tshowcase-settings');	
	$name = $options['tshowcase_name_singular'];
	$nameplural = $options['tshowcase_name_plural'];
 
     if  ( 'tshowcase' == $screen->post_type ) {
          $title = 'Insert '.$name.' Name Here';
     }
 
     return $title;
}

if($ts_change_default_title_en) {
add_filter( 'enter_title_here', 'tshowcase_change_default_title' );
}


function tshowcase_admin_order($wp_query) {
  if (is_post_type_archive( 'tshowcase' ) && is_admin() ) {    
		if (!isset($_GET['orderby'])) {
		  $wp_query->set('orderby', 'menu_order');
		  $wp_query->set('order', 'ASC');
	
  		}
  	}
}

//This will default the ordering admin to the 'menu_order' - will disable other ordering options
add_filter('pre_get_posts', 'tshowcase_admin_order');


// to dispay all entries in admin

function tshowcase_posts_per_page_admin($wp_query) {
  if (is_post_type_archive( 'tshowcase' ) && is_admin() ) {    
		

		  $wp_query->set( 'posts_per_page', '-1' );
	
  		
  	}
}

//This will the filter above to display all entries in the admin page
add_filter('pre_get_posts', 'tshowcase_posts_per_page_admin');


/**
 * Display the metaboxes
 */
 
function tshowcase_info_metabox() {
	global $post;	
	global $ts_labels;
	
	 
	$tsposition = get_post_meta( $post->ID, '_tsposition', true );
	$tsemail = get_post_meta( $post->ID, '_tsemail', true );
	$tstel = get_post_meta( $post->ID, '_tstel', true );
	$tsuser = get_post_meta( $post->ID, '_tsuser', true );
	$tsfreehtml = get_post_meta( $post->ID, '_tsfreehtml', true );
	$tspersonal = get_post_meta( $post->ID, '_tspersonal', true );
	$tslocation = get_post_meta( $post->ID, '_tslocation', true );


	
	
	?>
    
    
<table cellpadding="2">

<tr>
  <td align="right" valign="top"><label for="_tsfreehtml"><?php echo $ts_labels['html']['label'] ?>:</label></td>
  <td><textarea name="_tsfreehtml" cols="35" rows="2" id="_tsfreehtml"><?php if( $tsfreehtml ) { echo $tsfreehtml; } ?>
</textarea></td>
  <td><p class="howto"><?php echo $ts_labels['html']['description'] ?></p></td>
</tr>
<tr><td align="right">	
  <label for="_tsposition"><?php echo $ts_labels['position']['label'] ?>:<br></label>
  </td>
  <td><input id="_tsposition" size="37" name="_tsposition" type="text" value="<?php if( $tsposition ) { echo $tsposition; } ?>" /></td>
  <td><p class="howto"><?php echo $ts_labels['position']['description'] ?></p></td>
</tr>
        
        <tr><td align="right">	
<label for="_tsemail"><?php echo $ts_labels['email']['label'] ?>:<br></label>
        </td>
          <td><input id="_tsemail" size="37" name="_tsemail" type="email" value="<?php if( $tsemail ) { echo $tsemail; } ?>" /></td>
          <td><p class="howto"><?php echo $ts_labels['email']['description'] ?></p></td>
  </tr>



  <tr>
    <td align="right"><?php echo $ts_labels['location']['label'] ?>:</td>
          <td><input id="_tslocation" size="37" name="_tslocation" type="text" value="<?php if( $tslocation ) { echo $tslocation; } ?>" /></td>
          <td><p class="howto"><?php echo $ts_labels['location']['description'] ?></p></td>
  </tr>
  <tr>
          <td align="right"><?php echo $ts_labels['telephone']['label'] ?>:</td>
    <td><input id="_tstel" size="37" name="_tstel" type="text" value="<?php if( $tstel ) { echo $tstel; } ?>" /></td>
    <td><p class="howto"><?php echo $ts_labels['telephone']['description'] ?></p></td>
  </tr>
  <tr>
    <td align="right" nowrap><?php echo $ts_labels['user']['label'] ?>:</td>
    <td><select name="_tsuser" id="_tsuser">
      <option value="0">No User Associated</option>
      <?php
    $blogusers = get_users();
    foreach ($blogusers as $user) { ?>
      <option value="<?php echo $user->ID; ?>" <?php selected( $tsuser, $user->ID ) ?>><?php echo $user->display_name; ?></option>
      <?php } ?>
    </select></td>
    <td><p class="howto"><?php echo $ts_labels['user']['description'] ?>
     
    </p></td>
  </tr>
  <tr>
    <td align="right"><p>
      <label for="_tspersonal"><?php echo $ts_labels['website']['label'] ?>:<br>
      </label>
    </p></td>
    <td><input id="_tspersonal" size="37" name="_tspersonal" type="url" value="<?php if( $tspersonal ) { echo $tspersonal; } ?>" /></td>
    <td><p class="howto"><?php echo $ts_labels['website']['description'] ?></p></td>
  </tr>


</table>
<p>
  <?php
}

/**
 * Process the custom metabox fields
 */
function tshowcase_save_info( $post_id ) {
	global $post;
	
	// Skip auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
	
	if(isset($post)) {
		if ($post->post_type == 'tshowcase') {
			if( $_POST ) {
				
				update_post_meta( $post->ID, '_tsemail', $_POST['_tsemail'] );
				update_post_meta( $post->ID, '_tstel', $_POST['_tstel'] );
				update_post_meta( $post->ID, '_tsposition', $_POST['_tsposition'] );
				update_post_meta( $post->ID, '_tsuser', $_POST['_tsuser'] );
				update_post_meta( $post->ID, '_tsfreehtml', $_POST['_tsfreehtml'] );
				update_post_meta( $post->ID, '_tspersonal', $_POST['_tspersonal'] );
				update_post_meta( $post->ID, '_tslocation', $_POST['_tslocation'] );
				
			}
		}
	}
}

// Add action hooks. Without these we are lost
add_action( 'admin_init', 'tshowcase_add_info_metabox' );
add_action( 'save_post', 'tshowcase_save_info' );

/**
 * Add meta box for Aditional Information
 */
function tshowcase_add_info_metabox() {
	
	global $ts_labels;
	$title = $ts_labels['titles']['info'];
	
	add_meta_box( 'tshowcase-info-metabox', $title, 'tshowcase_info_metabox', 'tshowcase', 'normal', 'high' );
	
	
}

 
 
 
//Social Links Meta Box HTML 
function tshowcase_social_metabox() {
	global $post;	
	global $ts_labels;
	$helptext = $ts_labels['help']['social'];
	
	
	$tslinkedin = get_post_meta( $post->ID, '_tslinkedin', true );
	$tsfacebook = get_post_meta( $post->ID, '_tsfacebook', true );
	$tstwitter = get_post_meta( $post->ID, '_tstwitter', true );
	$tsgplus = get_post_meta( $post->ID, '_tsgplus', true );	
	$tsyoutube = get_post_meta( $post->ID, '_tsyoutube', true );
	$tsvimeo = get_post_meta( $post->ID, '_tsvimeo', true );
	$tsinstagram = get_post_meta( $post->ID, '_tsinstagram', true );
	$tsemailico = htmlentities ( get_post_meta( $post->ID, '_tsemailico', true ) );
	
	//not being used in current version
	$tsdribbble = get_post_meta( $post->ID, '_tsdribbble', true );
	$tspinterest = get_post_meta( $post->ID, '_tspinterest', true );
	
	?>
<p class="howto"><?php echo $helptext; ?></p>
<table width="100%" cellpadding="0">
        
        <tr><td align="right">	
  <p><label for="tslinkedin">LinkedIn:<br></label></p>
          </td>
          <td><input id="_tslinkedin" size="37" name="_tslinkedin" type="url" value="<?php if( $tslinkedin ) { echo $tslinkedin; } ?>" /></td>
          <td align="right">&nbsp;</td>
          <td align="right">Vimeo:</td>
          <td><input id="_tsvimeo" size="37" name="_tsvimeo" type="url" value="<?php if( $tsvimeo ) { echo $tsvimeo; } ?>" /></td>
        </tr>



<tr><td align="right">	
<p><label for="_tsfacebook">Facebook:<br></label></p>
        </td>
  <td><input id="_tsfacebook" size="37" name="_tsfacebook" type="url" value="<?php if( $tsfacebook ) { echo $tsfacebook; } ?>" /></td>
  <td align="right">&nbsp;</td>
  <td align="right">Youtube:</td>
  <td><input id="_tsyoutube" size="37" name="_tsyoutube" type="url" value="<?php if( $tsyoutube ) { echo $tsyoutube; } ?>" /></td>
        </tr>
        
<tr><td align="right">	
<p><label for="_tstwitter">Twitter:<br></label></p>
        </td>
  <td><input id="_tstwitter" size="37" name="_tstwitter" type="url" value="<?php if( $tstwitter ) { echo $tstwitter; } ?>" /></td>
  <td align="right">&nbsp;</td>
  
  
          <td align="right"><label for="_tspinterest">Pinterest:</label></td>
          <td>
          <input id="_tspinterest" size="37" name="_tspinterest" value="<?php if( $tspinterest ) { echo $tspinterest; } ?>" />
          <input id="_tsdribbble" size="37" name="_tsdribbble" type="hidden" value="<?php if( $tsdribbble ) { echo $tsdribbble; } ?>" />
      </td>

  </tr>
        
        <tr><td align="right">	
<p><label for="_tsgplus">Google Plus:<br></label></p>
        </td>
          <td><input id="_tsgplus" size="37" name="_tsgplus" type="url" value="<?php if( $tsgplus ) { echo $tsgplus; } ?>" /></td>
          <td align="right">&nbsp;</td>
          <td align="right">Instagram</td>
          <td><input id="_tsinstagram" size="37" name="_tsinstagram" value="<?php if( $tsinstagram ) { echo $tsinstagram; } ?>" />
        
      </td>
        </tr>

         <tr><td align="right">	

        </td>
          <td>&nbsp;</td>
          <td align="right">&nbsp;</td>
         
<td align="right">Email:</td>
  <td><input id="_tsemailico" size="37" name="_tsemailico" type="text" value="<?php if( $tsemailico ) { echo $tsemailico; } ?>" /><span class="howto">If mailto:active is enabled on the settings, email address will have the mailto link integrated</span></td>
        

        </tr>


</table>
<?php
}

/**
 * Process the custom metabox fields
 */
function tshowcase_save_social( $post_id ) {
	global $post;
	if(isset($post)) {
		if ($post->post_type == 'tshowcase') {
			if( $_POST ) {
				update_post_meta( $post->ID, '_tslinkedin', $_POST['_tslinkedin'] );
				update_post_meta( $post->ID, '_tsfacebook', $_POST['_tsfacebook'] );
				update_post_meta( $post->ID, '_tstwitter', $_POST['_tstwitter'] );
				update_post_meta( $post->ID, '_tsgplus', $_POST['_tsgplus'] );
				update_post_meta( $post->ID, '_tspinterest', $_POST['_tspinterest'] );
				update_post_meta( $post->ID, '_tsyoutube', $_POST['_tsyoutube'] );
				update_post_meta( $post->ID, '_tsvimeo', $_POST['_tsvimeo'] );
				update_post_meta( $post->ID, '_tsdribbble', $_POST['_tsdribbble'] );
				update_post_meta( $post->ID, '_tsemailico', $_POST['_tsemailico'] );
				update_post_meta( $post->ID, '_tsinstagram', $_POST['_tsinstagram'] );
				
			}
		}
	}
}

// Add action hooks. Without these we are lost
add_action( 'admin_init', 'tshowcase_add_social_metabox' );
add_action( 'save_post', 'tshowcase_save_social' );

/**
 * Add meta box for social links
 */
function tshowcase_add_social_metabox() {
	
	global $ts_labels;
	$title = $ts_labels['titles']['social'];
	
	add_meta_box( 'tshowcase-social-metabox',$title, 'tshowcase_social_metabox', 'tshowcase', 'normal', 'high' );
}



//add options page
function tshowcase_admin_page() {
	
	$menu_slug = 'edit.php?post_type=tshowcase';
	$submenu_page_title = 'Settings';
    $submenu_title = 'Settings';
	$capability = 'manage_options';
    $submenu_slug = 'tshowcase_settings';
    $submenu_function = 'tshowcase_settings_page';
    $defaultp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
   }
   
 
  


//Shortcode

//Add shortcode functionality
add_shortcode('show-team', 'shortcode_tshowcase');
add_shortcode('show-team-search', 'shortcode_tshowcase_search');
add_filter('widget_text', 'do_shortcode');
add_filter( 'the_excerpt', 'do_shortcode');


function shortcode_tshowcase( $atts ) {	

	if (!is_array($atts)) { $atts = array(); }

	$orderby = (array_key_exists('orderby', $atts) ? $atts['orderby'] : "menu_order");
	$limit = (array_key_exists('limit', $atts) ? $atts['limit'] : 0);
	$idsfilter = (array_key_exists('ids', $atts) ? $atts['ids'] : "0");
	$category = (array_key_exists('category', $atts) ? $atts['category'] : "0");
	$url =  (array_key_exists('url', $atts) ? $atts['url'] : "inactive");
	$layout = (array_key_exists('layout', $atts) ? $atts['layout'] : "grid");
	$style = (array_key_exists('style', $atts) ? $atts['style'] : "img-square,normal-float");
	$display = (array_key_exists('display', $atts) ? $atts['display'] : "photo,position,email"); 
	$img = (array_key_exists('img', $atts) ? $atts['img'] : ""); 
	$searchact = (array_key_exists('search', $atts) ? $atts['search'] : "true");
	$pagination = (array_key_exists('pagination', $atts) ? $atts['pagination'] : "false");
	 
	$html = build_tshowcase($orderby,$limit,$idsfilter,$category,$url,$layout,$style,$display,$pagination,$img,$searchact);
	return $html;	
	
}

function shortcode_tshowcase_search( $atts ) {	

	if (!is_array($atts)) { $atts = array(); }

	$title = (array_key_exists('title', $atts) ? $atts['title'] : "");
	$taxonomies = (array_key_exists('filter', $atts) ? $atts['filter'] : "false");
	$custom_fields = (array_key_exists('fields', $atts) ? $atts['fields'] : "true");
	$url =  (array_key_exists('url', $atts) ? $atts['url'] : "");
	 
	$html = tshowcase_search_form ($title,$taxonomies,$custom_fields,$url);
	return $html;	
	
}



/*
 *
 * /////////////////////////////
 * FUNCTION TO DISPLAY THE LIST
 * /////////////////////////////
 *
 */

function build_tshowcase($orderby="menu_order",$limit=-1,$idsfilter="0",$category="0",$url="inactive",$layout="grid",$style="float-normal",$display="photo,name,position,email",$pagination="false",$imgwo="",$searchact="true") {
	
	tshowcase_add_global_css();
	
	$html = "";
	$thumbsize = "tshowcase-thumb";
	global $post;
	global $ts_labels;
	
	$options = get_option('tshowcase-settings');
	
	//order
	
	if($orderby=='none') {
		$orderby = 'menu_order';
		};
	
	$ascdesc = 'DESC';
	if($orderby == 'title' || $orderby == 'menu_order') {
		$ascdesc = 'ASC';
		};
	
	//post per page
	$postsperpage = -1;
	$nopaging=true;
	if($limit >= 1) { 
	$postsperpage = $limit;
	$nopaging = false;
	}

	$paged = null;

	if($pagination=="true") {
		$postsperpage = $limit;
		$nopaging = false;
		$paged = 1;

		if(isset($_GET['tpage'])){ $paged = $_GET['tpage'];}
	}
	
	//display
	$display = explode(',',$display);
	$socialshow = false;
	if(in_array('social',$display)) {
		$socialshow = true;
	}
	
	//image size override
	$imgwidth = "";
	if($imgwo!=""){
		$imgwidth = explode(',',$imgwo);
		}
	
	//icons
	if(in_array('smallicons',$display)) {
	tshowcase_add_smallicons_css();	
	}

	
	//SEARCH RELATED CODE
	$search = "";
	$label = "";
	$catlabel = "";

	if(isset($_GET['tshowcase-categories']) && $_GET['tshowcase-categories']!="" && $searchact == "true"){
			$category = esc_attr($_GET['tshowcase-categories']);
			$label = '<div class="tshowcase-search-label">'.$ts_labels['search']['results-for'].' "'.$search.'"</div>';
			//would add category name here
			$catlabel = '';
		}

	if(isset($_GET['search']) && $_GET['search']!="" && $searchact == "true"){
		$search = esc_attr($_GET['search']);
		$label = '<div class="tshowcase-search-label">'.$ts_labels['search']['results-for'].' "'.$search.'" '.$catlabel.'</div>';
	}
	

	
	//If Custom Fields Search ON
	if($search!="" && $searchact == "true") {

	$args = array( 'post_type' => 'tshowcase',
				   'tshowcase-categories' => $category, 
				   'orderby' => $orderby, 
				   'order' => $ascdesc, 
				   'posts_per_page'=> -1, 
				   'nopaging'=> true,
				   'meta_value' => $search,
				   'meta_compare' => "LIKE",
				   
				   );

		$cf_query = new WP_Query( $args );
		wp_reset_postdata();
	}

	$args = array( 'post_type' => 'tshowcase',
				   'tshowcase-categories' => $category, 
				   'orderby' => $orderby, 
				   'order' => $ascdesc, 
				   'posts_per_page'=> $postsperpage, 
				   'nopaging'=> $nopaging,
				   's' => $search,
				   'paged' => $paged,
				   'suppress_filters' => true
				   );
	
	
	if($idsfilter != '0' && $idsfilter != '') {
		$postarray = explode(',', $idsfilter);

	 	if($postarray[0]!='') {
		$args['post__in'] = $postarray;
		}
	} 

	$loop = new WP_Query( $args );

	//Merge If Search is ON
	if($search!="" && $searchact == "true") {
		$loop->posts = $cf_query->posts+$loop->posts ;
		$loop->post_count = count($loop->posts);
	}
	
		
	//CHECK STYLE AND LAYOUT
	if($layout=='table') {
	
	$html .= tshowcase_build_table_layout($loop,$url,$display,$style);

	if($pagination=="true" && !isset($_GET['search'])) {

		$max_page = $loop->max_num_pages;
		$numbers = "";

		$ii = 1;
		while ($ii <= $max_page) {
			$current = isset($_GET['tpage']) && $_GET['tpage'] == $ii ? 'tshowcase_current_page' : '';
			$numbers .= " <a class='tshowcase_page ".$current."' href='?tpage=".$ii."'>".$ii."</a> ";
			$ii++;
		}


		$html .= "<div class='tshowcase_pager'>";

		if(isset($_GET['tpage']) && $_GET['tpage']!='1' && $_GET['tpage'] < $max_page){ 
			
			$next = intval($_GET['tpage']) + 1;
			$previous = intval($_GET['tpage'])-1;

			$html .= "<a class='tshowcase_previous' href='?tpage=".$previous."'>".$ts_labels['pagination']['previous_page']."</a>";
			$html .= $numbers;
			$html .= "<a class='tshowcase_next' href='?tpage=".$next."'>".$ts_labels['pagination']['next_page']."</a>";

		} if(!isset($_GET['tpage']) || $_GET['tpage']=='1' ) {

			$html .= $numbers." <a href='?tpage=2'>".$ts_labels['pagination']['next_page']."</a>";
		
		}

		if(isset($_GET['tpage']) && $_GET['tpage']!='1' && $_GET['tpage']>=$max_page){ 
			
			
			$previous = intval($_GET['tpage'])-1;

			$html .= "<a class='tshowcase_previous' href='?tpage=".$previous."'>".$ts_labels['pagination']['previous_page']."</a>";
			$html .= $numbers;
			

		} 

		$html .= "</div>";
	}
	
		
	} 
	
	if($layout=='pager' || $layout=='thumbnails' ) {
		
		global $tshowcase_pager_count;
		tshowcase_pager_layout($tshowcase_pager_count);

		
		$imgstyle = tshowcase_get_img_style($style);
		$txtstyle = tshowcase_get_txt_style($style);
		$pagerstyle = tshowcase_get_pager_style($style);
		$pagerboxstyle = tshowcase_get_pager_box_style($style);
		$infostyle = tshowcase_get_info_style($style);	
		$pagerfilteractive = '';

		
		$theme = tshowcase_get_themes($style,'pager');	
		tshowcase_add_theme($theme,'pager');
			
		$thumbshtml ="";
		$previewhtml ="";
		$ic = 0;
			
		$lshowcase_options = get_option('tshowcase-settings');
		$dwidth = $lshowcase_options['tshowcase_thumb_width'];	
		
		
		if(is_array($imgwidth)) {
				$thumbsize = $imgwidth;
				$dwidth = $thumbsize[0];
			}
		

		//BUILD CATEGORY FILTERS
	
			if (in_array('filter',$display) || in_array('enhance-filter',$display) ) {
	
			if (in_array('filter',$display)) {
			tshowcase_filter_code();
			$html .= "<ul id='ts-filter-nav'>";
			}
			
			if (in_array('enhance-filter',$display)) {
			tshowcase_enhance_filter_code();
			$html .= "<ul id='ts-enhance-filter-nav'>";
			}
					
			
			$html .= "<li id='ts-all'>".$ts_labels['filter']['all-entries-label']."</li>";

			$includecat = array();
			if($category!="" && $category!="0") { 

			 $cats = explode(',',$category);
			 

			 foreach ($cats as $cat) {
			 
			 	$term = get_term_by('slug', $cat, 'tshowcase-categories');
			 	array_push($includecat,$term->term_id);

			 }

			 $args = array(
			 	'include' => $includecat
			 	);

			}

			 $args['orderby'] = 'slug';
			 $args['order'] = 'ASC';

			 $terms = get_terms("tshowcase-categories",$args);
			 $count = count($terms);
			 if ( $count > 0 ){		 
					 foreach ( $terms as $term ) {
					$html .= "<li id='ts-".$term->slug."'>".$term->name."</li>";
					 }		 
			 }
			$html .= "</ul>";
			
			$pagerfilteractive .="tshowcase-filter-active";
			
			}
				
			//Build Category filter end	





		while ( $loop->have_posts() ) : $loop->the_post(); 
		
		$title = the_title_attribute( 'echo=0' );	

		
			//If Photo is True
			if ( has_post_thumbnail() && in_array('photo',$display)) :     
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $thumbsize );
			$width = $image[1];			
			$twidth = $options['tshowcase_tpimg_width'];
			$theight = $options['tshowcase_tpimg_height'];
						
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),array($twidth,$theight),true); 

			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
			if($alt!='') {
				$alt = 'alt="'.$alt.'"';
			}			
			
			$previewhtml .='<li><div class="tshowcase-box">';
			
			if($options['tshowcase_single_page']=="true" && $url =="active") {
				$previewhtml .='<div class="tshowcase-box-photo '.$imgstyle.'"><a href="'.get_permalink($post->ID).'"><img src="'.$image[0].'" width="'.$width.'" '.$alt.' /></a></div>';
			} else {
				$previewhtml .='<div class="tshowcase-box-photo '.$imgstyle.'"><img src="'.$image[0].'" width="'.$width.'" '.$alt.' /></div>';
			}
			
			$previewhtml .= "<div class='tshowcase-box-info ".$infostyle." ".$txtstyle."'>";
			
			//if title is active
			if (in_array('name',$display)) : 
				if($options['tshowcase_single_page']=="true" && $url =="active") {
					$previewhtml .='<div class="tshowcase-box-title"><a href="'.get_permalink($post->ID).'">'.$title.'</a></div>';
				} 	else {
					$previewhtml .= "<div class='tshowcase-box-title'>".$title."</div>";
				}
			endif;
			
			//if Social is true
			if ($socialshow) : 		
			$previewhtml .= "<div class='tshowcase-box-social'>".tshowcase_get_social(get_the_ID(),$socialshow)."</div>";
			endif;
			
			//if details exist		
			$previewhtml .= "<div class='tshowcase-box-details'>".tshowcase_get_information(get_the_ID(),true,$display,false)."</div>";
			
			
			$previewhtml .="</div></div></li>";
			
			$id = get_the_ID();
			$cat = "";
		
			$terms = get_the_terms( $id , 'tshowcase-categories' );
			if(is_array($terms)) {
				foreach ( $terms as $term ) {
				$cat .= 'ts-'.$term->slug.' ';
				}
			}	
			
			$thumbshtml .= '<div class="tshowcase-pager-thumbnail '.$cat.' '.$pagerfilteractive.'"><div class="'.$imgstyle.'"><a data-slide-index="'.$ic.'" href=""><img src="'.$thumb[0].'" width="'.$thumb[1].'" '.$alt.'/></a></div></div>';		  
			$ic++;	 
			endif;
		
		 
		endwhile;
		
		$wrapclass = '';
		if($theme!="default") {  $wrapclass .= " tshowcase-pager-".$theme."-wrap";  }
		
		$html .= '<div class="tshowcase-pager-wrap '.$wrapclass.'" style="display:none;">';
		$html .= '<div class="'.$pagerboxstyle.'"><ul class="tshowcase-bxslider-'.$tshowcase_pager_count.'">';
		$html .= $previewhtml;
		$html .= '</ul></div>';
		$html .= '<div id="tshowcase-bx-pager-'.$tshowcase_pager_count.'" class="'.$pagerstyle.'">';
		$html .= $thumbshtml;
		$html .= '</div>';
		$html .= '<div class="ts-clear-both"></div></div>';
		
		$tshowcase_pager_count++;
	}
	
	
	if($layout=='grid') {
		
	//theme	
	
	
	$imgstyle = tshowcase_get_img_style($style);
	$txtstyle = tshowcase_get_txt_style($style);
	$boxstyle = tshowcase_get_box_style($style);
	$innerboxstyle = tshowcase_get_innerbox_style($style);
	$infostyle = tshowcase_get_info_style($style);	
	$wrapstyle = tshowcase_get_wrap_style($style);
	$theme = tshowcase_get_themes($style,'grid');
	
	tshowcase_add_theme($theme,'grid');	
		
		$html .="<div class='tshowcase-wrap ".$wrapstyle."'>";	
		
		
			//BUILD CATEGORY FILTERS
	
			if (in_array('filter',$display) || in_array('enhance-filter',$display) ) {
	
			if (in_array('filter',$display)) {
			tshowcase_filter_code();
			$html .= "<ul id='ts-filter-nav'>";
			}
			
			if (in_array('enhance-filter',$display)) {
			tshowcase_enhance_filter_code();
			$html .= "<ul id='ts-enhance-filter-nav'>";
			}
					
			
			$html .= "<li id='ts-all'>".$ts_labels['filter']['all-entries-label']."</li>";

			$includecat = array();

			if($category!="" && $category!="0") { 

				 $cats = explode(',',$category);
				 

				 foreach ($cats as $cat) {
				 
				 	$term = get_term_by('slug', $cat, 'tshowcase-categories');
				 	array_push($includecat,$term->term_id);

				 }

				 $args = array(
				 	'include' => $includecat
				 	);

			}

			$args['orderby'] = 'slug';
			 $args['order'] = 'ASC';

			 $terms = get_terms("tshowcase-categories",$args);

			 $count = count($terms);
			 if ( $count > 0 ){		 
					 foreach ( $terms as $term ) {
					$html .= "<li id='ts-".$term->slug."'>".$term->name."</li>";
					 }		 
			 }
			$html .= "</ul>";
			
			$boxstyle .=" tshowcase-filter-active";
			
			}
				
			//Build Category filter end	
		
		
		
		while ( $loop->have_posts() ) : $loop->the_post(); 
		
			$title = the_title_attribute( 'echo=0' );
			$id = get_the_ID();
			$cat = "";
		
			$terms = get_the_terms( $post->ID , 'tshowcase-categories' );
			if(is_array($terms)) {
				foreach ( $terms as $term ) {
				$cat .= 'ts-'.$term->slug.' ';
				}
			}	
			
			$html .="<div class='tshowcase-box ".$boxstyle." ".$cat."' >";	
			$html .="<div class='tshowcase-inner-box ".$innerboxstyle."'>";	
			
			$tshowcase_options = get_option('tshowcase-settings');
			$dwidth = $tshowcase_options['tshowcase_thumb_width'];	
			
			//If Photo is True
			if ( has_post_thumbnail() && in_array('photo',$display)) {  
			

			if(is_array($imgwidth)) {
				$thumbsize = $imgwidth;
			}
			   
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $thumbsize ); 
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
			if($alt!='') {
				$alt = 'alt="'.$alt.'"';
			}		
			
			$width = $image[1];	
			
			
				if($options['tshowcase_single_page']=="true" && $url =="active") {
					$html .= "<div class='tshowcase-box-photo ".$imgstyle."'><a href='".get_permalink($post->ID)."'><img src='".$image[0]."' width='".$width."' title='".$title."' ".$alt." /></a></div>";
				} else {
					$html .= "<div class='tshowcase-box-photo ".$imgstyle."'><img src='".$image[0]."' width='".$width."' title='".$title."' ".$alt." /></div>";
				}
								
			} else {
				
				if ( !has_post_thumbnail() && in_array('photo',$display)) {  
				
						if($options['tshowcase_single_page']=="true" && $url =="active") {
							$html .= "<div class='tshowcase-box-photo ".$imgstyle."'><a href='".get_permalink($post->ID)."'><img src='".plugins_url( '/img/default.png', __FILE__ )."' width='".$dwidth."' title='".$title."' /></a></div>";
						} else {
							$html .= "<div class='tshowcase-box-photo ".$imgstyle."'><img src='".plugins_url( '/img/default.png', __FILE__ )."' width='".$dwidth."' title='".$title."' /></div>";
						}
								
					}				
				
				}
			
				
			$html .= "<div class='tshowcase-box-info ".$infostyle." ".$txtstyle." '>";
			
			
			//content array for ordering
			$display_array = array();
			
			$display_array['name']="";		
			//if title is active
			if (in_array('name',$display)) : 	
					
			if($options['tshowcase_single_page']=="true" && $url =="active") {
					$display_array['name'] ='<div class="tshowcase-box-title"><a href="'.get_permalink($post->ID).'">'.$title.'</a></div>';
				} 	
				else {
					$display_array['name'] = "<div class='tshowcase-box-title'>".$title."</div>";
				}
			
			
			endif;
			
			$display_array['social'] = "";
			//if Social is true
			if ($socialshow) : 		
			$display_array['social'] = "<div class='tshowcase-box-social'>".tshowcase_get_social(get_the_ID(),$socialshow)."</div>";
			endif;
			
			$display_array['details'] = "";
			//if details exist		
			$display_array['details'] = "<div class='tshowcase-box-details'>".tshowcase_get_information(get_the_ID(),true,$display,false)."</div>";
			
			
			//ORDER INFORMATION
			global $ts_display_order;
			
			
			foreach($ts_display_order as $disp) {
				$html .= $display_array[$disp];
			}
			//END ORDER
			
			
			$html .="</div>";
			$html .="</div>";
			$html .="</div>";
			
			
		endwhile; 
		$html .="</div>";
	}
	
	
	//HOVER THUMBS LAYOUT
	
	if($layout=='hover') {
		
	$imgstyle = tshowcase_get_img_style($style);
	$txtstyle = tshowcase_get_txt_style($style);
	$boxstyle = tshowcase_get_box_style($style);
	$infostyle = tshowcase_get_info_style($style);	
	$wrapstyle = tshowcase_get_wrap_style($style);	
	
	$theme = tshowcase_get_themes($style,'hover');	
	tshowcase_add_theme($theme,'hover');
	
	$wrapid = "tshowcase-hover-wrap";
	if($theme!="default") { $wrapid = "tshowcase-".$theme."-wrap"; }
	
		
		$html .="<div id='".$wrapid."'>";	
		
		
		
	//BUILD CATEGORY FILTERS
	
	if (in_array('filter',$display) || in_array('enhance-filter',$display) ) {
	
	if (in_array('filter',$display)) {
			tshowcase_filter_code();
			$html .= "<ul id='ts-filter-nav'>";
			}
			
			if (in_array('enhance-filter',$display)) {
			tshowcase_enhance_filter_code();
			$html .= "<ul id='ts-enhance-filter-nav'>";
			}
			
	
	$html .= "<li id='ts-all'>".$ts_labels['filter']['all-entries-label']."</li>";
	 
	 $includecat = array();
			if($category!="") { 

			 $cats = explode(',',$category);
			 

			 foreach ($cats as $cat) {
			 
			 	$term = get_term_by('slug', $cat, 'tshowcase-categories');
			 	array_push($includecat,$term->term_id);

			 }

			 $args = array(
			 	'include' => $includecat
			 	);

			}


			$args['orderby'] = 'slug';
			 $args['order'] = 'ASC';
			 $terms = get_terms("tshowcase-categories",$args);


	 $count = count($terms);
	 if ( $count > 0 ){		 
		 foreach ( $terms as $term ) {
			$html .= "<li id='ts-".$term->slug."'>".$term->name."</li>";
			 }		 
	 }
	$html .= "</ul>";
	
	$boxstyle .=" tshowcase-filter-active";
	
	}
		
	//Build Category filter end	
		
		
		
		
		
		$lshowcase_options = get_option('tshowcase-settings');
		$dwidth = $lshowcase_options['tshowcase_thumb_width'];	
		if(is_array($imgwidth)) {
				$thumbsize = $imgwidth;
				$dwidth = $thumbsize[0];
			}
			
		while ( $loop->have_posts() ) : $loop->the_post(); 
		
		$title = the_title_attribute( 'echo=0' );
		
		$id = get_the_ID();
		$cat = "";
		
		$terms = get_the_terms( $post->ID , 'tshowcase-categories' );
			if(is_array($terms)) {
				foreach ( $terms as $term ) {
				$cat .= 'ts-'.$term->slug.' ';
				}
			}
		
		
		$html .='<div class="tshowcase-hover-box '.$boxstyle.' '.$cat.'"><div style="margin-left:auto; margin-right:auto; width:'.$dwidth.'px;">';
		$html .='<span class="'.$imgstyle.'">';
                        
			if ( has_post_thumbnail()) :
			     
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $thumbsize ); 	
			$width = $image[1];	
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
			if($alt!='') {
				$alt = 'alt="'.$alt.'"';
			}		
			
			$html .= "<img src='".$image[0]."' width='".$width."' ".$alt."/>";
			
						
			endif;
			
			
			if ( !has_post_thumbnail()) {  
					
			$html .= "<img src='".plugins_url( '/img/default.png', __FILE__ )."' width='".$dwidth."'/>";
			
			}
			
						
			$html .='<span class="tshowcase-hover-info">';
            $html .= "<div class='tshowcase-box-info ".$txtstyle."'><div class='tshowcase-box-info-inner'>";
			
			//if title is active
			if (in_array('name',$display)) : 	
					
			if($options['tshowcase_single_page']=="true" && $url =="active") {
					$html .='<div class="tshowcase-box-title"><a href="'.get_permalink($post->ID).'">'.$title.'</a></div>';
				} 	
				else {
					$html .= "<div class='tshowcase-box-title'>".$title."</div>";
				}
			
			
			endif;
			
			//if Social is true
			if ($socialshow) : 		
			$html .= "<div class='tshowcase-box-social'>".tshowcase_get_social(get_the_ID(),$socialshow)."</div>";
			endif;
			
			//if details exist		
			$html .= "<div class='tshowcase-box-details'>".tshowcase_get_information(get_the_ID(),true,$display,false)."</div>";
			
			
			$html .="</div></div>";             
						 
						 
						 
						 
            $html .='            </span>
                    
                </span> </div></div>	';	
			
		endwhile; 
		$html .="</div>";
	}
	
	
	
		
		// Restore original Post Data 
		wp_reset_postdata();
	
	$html = "<div class='tshowcase'>".$label.$html."</div>";
	return $html;
}

//BUILDING TABLE LAYOUT

function tshowcase_build_table_layout($loop,$url,$display,$style) {
	
	
	
	$theme = tshowcase_get_themes($style,'table');	
	tshowcase_add_theme($theme,'table');	
	
	$html = "";
	$options = get_option('tshowcase-settings');
	$imgstyle = tshowcase_get_img_style($style);
	$txtstyle = tshowcase_get_txt_style($style);
	$wrapstyle = tshowcase_get_wrap_style($style);
	
	$html .= "<table class='tshowcase-box-table ".$wrapstyle."'>";
	
	while ( $loop->have_posts() ) : $loop->the_post(); 
	$title = the_title_attribute( 'echo=0' );
	$id = get_the_ID();
	$smallicons = in_array('smallicons',$display);
	
	if($smallicons) {
		$iconposition = '<i class="icon-chevron-sign-right"></i> ';
		$iconemail = '<i class="icon-envelope-alt"></i> ';
		$icontel = '<i class="icon-phone-sign"></i> ';
		$iconhtml = '<i class="icon-align-justify"></i> ';
		$iconpersonal = '<i class="icon-external-link"></i> ';
		$iconlocation = '&nbsp;<i class="icon-map-marker"></i>&nbsp;';
		} else {
		$iconposition = '';
		$iconemail = '';
		$icontel = '';
		$iconhtml = '';
		$iconpersonal = '';
		$iconlocation = '';	
		}
	
	
	$html .= "<tr class='".$txtstyle."'>";
	
	if(in_array('photo',$display)){
		$width = $options['tshowcase_timg_width'];
		$height = $options['tshowcase_timg_height'];
		
		$html .= '<td><div class="'.$imgstyle.'">';
		if ( has_post_thumbnail() ) {
		$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($id),array($width,$height));	
		$thumbnail_id = get_post_thumbnail_id( $id );
		$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
		if($alt!='') {
			$alt = 'alt="'.$alt.'"';
		}		
		
				if($options['tshowcase_single_page']=="true" && $url =="active") {
					$html .='<a href="'.get_permalink($id).'"><img src="'.$thumb[0].'" width="'.$thumb[1].'" '.$alt.' /></a>';
				} 	
				else {
					$html .= '<img src="'.$thumb[0].'" width="'.$thumb[1].'" '.$alt.' />';
				}
		}
		
		if ( !has_post_thumbnail()) {  
				
				if($options['tshowcase_single_page']=="true" && $url =="active") {
									
						$html .='<a href="'.get_permalink($id).'"><img src="'.plugins_url( '/img/default.png', __FILE__ ).'" width="'.$width.'" /></a>';
						} 
				else {
								
						$html .= '<img src="'.plugins_url( '/img/default.png', __FILE__ ).'" width="'.$width.'" />';
					
						}
								
					}
		
		
		$html .= '</div></td>';
	}
	
	
	if(in_array('name',$display)){
		
	if($options['tshowcase_single_page']=="true" && $url =="active") {
					$html .='<td><a href="'.get_permalink($id).'">'.$title.'</a></td>';
				} 	
				else {
					$html .= "<td>".$title."</td>";
				}	
	
	}
	
	
	if(in_array('position',$display)) {
	$tsposition = get_post_meta($id,'_tsposition',true);
	$html .= "<td>";
		if($tsposition!="") { 	$html .= $iconposition.$tsposition; }
	$html .= "</td>";
	
	}
	
	if(in_array('email',$display)){
	$tsemail = htmlentities ( get_post_meta($id,'_tsemail',true) );
		$html .= "<td>";
		if(($tsemail!="")) { 
			$mailto = isset($options['tshowcase_mailto']);
			if($mailto): $tsemail = "<a href='mailto:".$tsemail."'>".$tsemail."</a>"; endif;
			$html .= $iconemail.$tsemail;
		}
		$html .= "</td>";
	}
	
	if(in_array('telephone',$display)){
	$tstel = get_post_meta($id,'_tstel',true);
	$html .= "<td>";
	if(($tstel!="")) { $html .= $icontel.$tstel; }
	$html .= "</td>";
	}
	
	if(in_array('location',$display)){
	$tsloc = get_post_meta($id,'_tslocation',true);
	$html .= "<td>";
	if(($tsloc!="")) { $html .= $iconlocation.$tsloc; }
	$html .= "</td>";
	}
	
	if(in_array('freehtml',$display)){
	$tsfreehtml = get_post_meta($id,'_tsfreehtml',true);
	$html .= "<td>";
	if(($tsfreehtml!="")) { $html .= $iconhtml.$tsfreehtml; }
	$html .= "</td>";
	}
	
	if(in_array('social',$display)){
	$social = tshowcase_get_social($id,true);
	$html .= "<td><div class='tshowcase-box-social'>".$social."</div></td>";
	}
	
	
	if(in_array('website',$display)){
		
	$tsweb = get_post_meta($id,'_tspersonal',true);
	$tswebstrip = tshowcase_strip_http($tsweb);
	$html .= "<td>";
	if(($tsweb!="")) { $html .= $iconpersonal."<a href='".$tsweb."' target='_blank'>".$tswebstrip."</a>";}
	$html .= "</td>";
	}
	
	
	
	$html .= "</tr>";
	
	endwhile;
	
	$html .= "</table>";
	return $html; 
}


//CSS & JS FUNCTIONS FOR EACH LAYOUT/STYLE


/* NORMAL STYLES */

function tshowcase_add_theme($theme,$layout) {
	
			global $ts_theme_names;
			
			$thadd = $ts_theme_names[$layout][$theme];
								
			wp_deregister_style( $thadd['name']);
		    wp_register_style($thadd['name'], plugins_url($thadd['link'], __FILE__ ),array(),false,false);
			wp_enqueue_style($thadd['name'] );			
			
}



function tshowcase_default_layout() {
				
			wp_deregister_style( 'tshowcase-normal-style' );
		    wp_register_style( 'tshowcase-normal-style', plugins_url( 'css/normal.css', __FILE__ ),array(),false,false);					            wp_enqueue_style( 'tshowcase-normal-style' );			
			
}



/*   JS for Slider */
function tshowcase_pager_layout($lshowcase_slider_count) {
				
			wp_deregister_script( 'tshowcase-bxslider' );
		    wp_register_script( 'tshowcase-bxslider', plugins_url( 'js/bxslider/jquery.bxslider.js', __FILE__ ),array('jquery'),false,false);
			wp_enqueue_script( 'tshowcase-bxslider' );			
			
			wp_deregister_script( 'tshowcase-bxslider-pager' );
		    wp_register_script( 'tshowcase-bxslider-pager', plugins_url( 'js/pager.js', __FILE__ ),array('jquery','tshowcase-bxslider'),false,false);
			wp_enqueue_script( 'tshowcase-bxslider-pager' );				
			
			
			$pagerarray = array( 'count' => $lshowcase_slider_count );
			wp_localize_script('tshowcase-bxslider-pager', 'tspagerparam', $pagerarray);

			//add_action( 'wp_print_footer_scripts', 'tshowcase_pager_code' );	
			
}


/* JS For Filter */ 
function tshowcase_filter_code() {
	
	wp_deregister_script( 'tshowcase-filter' );
	wp_register_script( 'tshowcase-filter', plugins_url( '/js/filter.js', __FILE__ ),array('jquery'),false,false);
	wp_enqueue_script( 'tshowcase-filter' );
			
}

function tshowcase_enhance_filter_code() {
	
	wp_deregister_script( 'tshowcase-enhance-filter' );
	wp_register_script( 'tshowcase-enhance-filter', plugins_url( '/js/filter-enhance.js', __FILE__ ),array('jquery'),false,false);
	wp_enqueue_script( 'tshowcase-enhance-filter' );
			
}

//Not in use anymore but not deleted for future reference and customizations

function tshowcase_pager_code() {
	global $tshowcase_pager_count;
	$i = 0;
	?>
    <script type="text/javascript">
	jQuery.noConflict();
	
	<?php while ($i<$tshowcase_pager_count) { 
	
	?>
	
	jQuery(document).ready(function(){
    var tsslider = jQuery('.tshowcase-bxslider-<?php echo $i; ?>').bxSlider({
      pagerCustom: '#tshowcase-bx-pager-<?php echo $i; ?>',
	  controls:false,
	  mode:'fade'
    	});

    // //custom hover code
    // jQuery('#tshowcase-bx-pager-'+<?php echo $i; ?>+' a').hover(function() {
				// var idslide = $(this).attr('data-slide-index');
				// tsslider.goToSlide(idslide);
			 //  	});


	
	
	<?php 
	$i++;
	} ?>
	 </script>
    
    <?php
	
}



/* CSS enqueue functions */ 

function tshowcase_add_global_css() {
       		wp_deregister_style( 'tshowcase-global-style' );
		    wp_register_style( 'tshowcase-global-style', plugins_url( '/css/global.css', __FILE__ ),array(),false,false);
			wp_enqueue_style( 'tshowcase-global-style' );	

    } 
	

function tshowcase_add_smallicons_css() {
       		wp_deregister_style( 'tshowcase-smallicons' );
		    wp_register_style( 'tshowcase-smallicons', plugins_url( '/css/font-awesome/css/font-awesome.min.css', __FILE__ ),array(),false,false);
			wp_enqueue_style( 'tshowcase-smallicons' );	

    } 

	
function tshowcase_get_image($id) {
$html = "";	
$options = get_option('tshowcase-settings');

if(isset($options['tshowcase_single_show_photo']) && has_post_thumbnail($id)) { 
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'tshowcase-thumb' ); 
		$html .=   '<div><img src="'.$image[0].'" width="'.$image[1].'" ></div>';
		//get_the_post_thumbnail($post->ID,'thumbnail');
		}
	return $html;	
	
}

//Currently not available in this version - twitter feed
function tshowcase_get_twitter($id) {
	
	$options = get_option('tshowcase-settings');
	$tstwitter = get_post_meta( $id, '_tstwitter', true );
	$html ="";
	if(isset($options['tshowcase_single_show_twitter']) && ($tstwitter!="")) { 
	
	$title = "Latest Tweets";
	if(isset($options['tshowcase_twitter_title'])) {
		$title = $options['tshowcase_twitter_title'];
	}	
		
	$html .=   "<h3>".$title."</h3>";
	$html .= '';
	}
	return $html;
	
}

function tshowcase_get_information($id,$show,$display=array(),$singular=false) {
	
		$options = get_option('tshowcase-settings');
		$html="";
		
		$position = in_array('position',$display);
		$email = in_array('email',$display);
		$tel = in_array('telephone',$display);
		$freehtml = in_array('freehtml',$display);
		$website = in_array('website',$display);
		$location = in_array('location',$display);
		$smallicons = in_array('smallicons',$display);
		
		if($singular) {
			$position = isset($options['tshowcase_single_show_position']);
			$email = isset($options['tshowcase_single_show_email']);
			$tel = isset($options['tshowcase_single_show_telephone']);
			$freehtml = isset($options['tshowcase_single_show_freehtml']);
			$website = isset($options['tshowcase_single_show_website']);
			$location = isset($options['tshowcase_single_show_location']);
			$smallicons = isset($options['tshowcase_single_show_smallicons']);
			
			if($smallicons) {
				tshowcase_add_smallicons_css();
			}
			
		}
		
		
	
		$tsposition = get_post_meta( $id, '_tsposition', true );

		$tsemail = get_post_meta( $id, '_tsemail', true );
		
		
		
		$tstel = get_post_meta( $id, '_tstel', true );
		$tsfreehtml = get_post_meta( $id, '_tsfreehtml', true );
		$tspersonal = get_post_meta( $id, '_tspersonal', true );
		$tslocation = get_post_meta( $id, '_tslocation', true );
	
		
		global $ts_small_icons;
		
		if($smallicons) {
		$iconposition = $ts_small_icons['position'];
		$iconemail = $ts_small_icons['email'];
		$icontel = $ts_small_icons['telephone'];
		$iconhtml = $ts_small_icons['html'];
		$iconpersonal = $ts_small_icons['website'];
		$iconlocation = $ts_small_icons['location'];
		} else {
		$iconposition = '';
		$iconemail = '';
		$icontel = '';
		$iconhtml = '';
		$iconpersonal = '';
		$iconlocation = '';	
		}
		
		$info_array = array();
	
		if(($position)&& ($tsposition!="")) { 
		$info_array['position'] =    "<div class='tshowcase-single-position'>".$iconposition.$tsposition."</div>"; 
		}		
		if(($email) && ($tsemail!="")) { 


			$mailto = isset($options['tshowcase_mailto']);



			if($mailto){ 

				//$tsemail = "<a href='mailto:$tsemail'>$tsemail</a>"; 
				$tsemail = tshowcase_mailto_filter($tsemail);

			} else {

				$tsemail = htmlentities ( $tsemail );
			}

			//to avoid spam bots, we replace the @ with with html code
			$tsemail = str_replace("@", "&#64;", $tsemail);

			$info_array['email'] =   "<div class='tshowcase-single-email'>".$iconemail.$tsemail."</div>";

		}
		if(($tel) && ($tstel!="")) { 
		$info_array['telephone'] =   "<div class='tshowcase-single-telephone'>".$icontel.$tstel."</div>";
		}
		
		if(($location) && ($tslocation!="")) { 
		$info_array['location'] =   "<div class='tshowcase-single-location'>".$iconlocation.$tslocation."</div>";
		}
		
		if(($freehtml) && ($tsfreehtml!="")){ 
		$info_array['html'] =  "<div class='tshowcase-single-freehtml'>". $iconhtml.$tsfreehtml."</div>";
		
		}
		if(($website) && ($tspersonal!="")) { 
		$tspersonalt = tshowcase_strip_http($tspersonal);
		$info_array['website'] =   "<div class='tshowcase-single-website'>".$iconpersonal."<a href='".$tspersonal."' target='_blank'>".$tspersonalt."</a></div>";  		
		}
		
		//ordering
		global $ts_content_order;
		foreach ($ts_content_order as $info) {
			if(isset($info_array[$info])) {
			$html.=$info_array[$info];
			}
		}
		
		
		return $html;
		
}

function tshowcase_get_social($id,$show) {
	
		$html="";
		global $ts_social_order;
		
		if($show) {
			
		$options = get_option('tshowcase-settings');				
	
		$tslinkedin = get_post_meta( $id, '_tslinkedin', true );
		$tsfacebook = get_post_meta( $id, '_tsfacebook', true );
		$tstwitter = get_post_meta( $id, '_tstwitter', true );
		$tsgplus = get_post_meta( $id, '_tsgplus', true );
		$tspinterest = get_post_meta( $id, '_tspinterest', true );
		$tsyoutube = get_post_meta( $id, '_tsyoutube', true );
		$tsvimeo = get_post_meta( $id, '_tsvimeo', true );
		$tsdribbble = get_post_meta( $id, '_tsdribbble', true );
		$tsinstagram = get_post_meta( $id, '_tsinstagram', true );
		$tsemailico = get_post_meta( $id, '_tsemailico', true );
		
		$folder = $options['tshowcase_single_social_icons'];
		
		$social_array=array();
		
		
		if($tslinkedin!=""){ $social_array['linkedin'] =   "<a href='".$tslinkedin."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/linkedin.png', __FILE__ )."'></a>"; }
		
		if($tsfacebook!=""){ $social_array['facebook'] =   "<a href='".$tsfacebook."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/facebook.png', __FILE__ )."'></a>"; }
		
		if($tstwitter!=""){ $social_array['twitter'] =   "<a href='".$tstwitter."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/twitter.png', __FILE__ )."'></a>"; }
		
		if($tsgplus!=""){ $social_array['gplus'] =   "<a href='".$tsgplus."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/gplus.png', __FILE__ )."'></a>"; }
		
		if($tspinterest!=""){ $social_array['pinterest'] =   "<a href='".$tspinterest."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/pinterest.png', __FILE__ )."'></a>"; }
		
		if($tsyoutube!=""){ $social_array['youtube'] =   "<a href='".$tsyoutube."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/youtube.png', __FILE__ )."'></a>"; }
		
		if($tsvimeo!=""){ $social_array['vimeo'] =   "<a href='".$tsvimeo."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/vimeo.png', __FILE__ )."'></a>"; }
		
		if($tsdribbble!=""){ $social_array['dribbble'] =   "<a href='".$tsdribbble."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/dribbble.png', __FILE__ )."'></a>"; }
		}
		
		if($tsinstagram!=""){ $social_array['instagram'] =   "<a href='".$tsinstagram."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/instagram.png', __FILE__ )."'></a>"; }


		if($tsemailico!=""){ 

			$options = get_option('tshowcase-settings');
			$mailto = isset($options['tshowcase_mailto']);



			if($mailto){ 

				$tsemailico = tshowcase_mailto_filter_ico($tsemailico);

			} 

			//to avoid spam bots, we replace the @ with with html code
			$tsemailico = str_replace("@", "&#64;", $tsemailico);	


		$social_array['email'] =   "<a href='".$tsemailico."' target='_blank'><img src='".plugins_url( '/img/social/'.$folder.'/email.png', __FILE__ )."'></a>"; 

		}
	
	


	foreach ($ts_social_order as $info) {
			if(isset($social_array[$info])) {
				$html.=$social_array[$info];
			}
		}
	
	return $html;
	
}




function tshowcase_latest_posts($id) {
		
	$options = get_option('tshowcase-settings');
	$html ="";
	

$tsuser = get_post_meta( $id, '_tsuser', true );
if(isset($options['tshowcase_single_show_posts'])) {
	
	if($tsuser!="0") {	
	$args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'author' => $tsuser,
		'suppress_filters' => true
	);
		
	// The Query
	$tshowcase_posts_query = new WP_Query($args);	
	
	// The Loop
	if($tshowcase_posts_query->have_posts()) {
	
	$title = "Latest Posts";
	if(isset($options['tshowcase_latest_title'])) {
		$title = $options['tshowcase_latest_title'];
	}	
		
	$html .=   "<h3>".$title."</h3>";
	$html .=   "<ul>";
	while ( $tshowcase_posts_query->have_posts() ) : $tshowcase_posts_query->the_post();
		$html .=   '<li><a href="'.get_permalink().'">' . get_the_title() . '</a></li>';
	endwhile;
	$html .=   "</ul>";
	}
	
	/* Restore original Post Data 
	 * NB: Because we are using new WP_Query we aren't stomping on the 
	 * original $wp_query and it does not need to be reset.
	*/
	wp_reset_postdata();
	}
}

return $html;
	
}



// register settings
function register_tshowcase_settings() {
	register_setting( 'tshowcase-plugin-settings', 'tshowcase-settings');
}

//register default values
register_activation_hook(__FILE__, 'tshowcase_defaults');


function tshowcase_defaults() {

	$tmp = get_option('tshowcase-settings');
	
	//check for settings version
    if((!is_array($tmp)) || !isset($tmp['tshowcase_empty'])) {

		delete_option('tshowcase-settings'); 
		
		$arr = array(	"tshowcase_name_singular" => "Member",
						"tshowcase_name_plural" => "Team",
						"tshowcase_name_slug" => "team",
						"tshowcase_name_category" => "Groups",
						"tshowcase_thumb_width" => "160",
						"tshowcase_thumb_height" => "160",
						"tshowcase_thumb_crop" => "false",
						"tshowcase_single_page" => "true", 
						"tshowcase_single_page_style" => "none", 
						"tshowcase_single_show_posts" => "false",
						"tshowcase_single_social_icons" => "round-20",
						"tshowcase_empty" => "0",
						"tshowcase_twitter_title" => "Latest Tweets", 
						"tshowcase_latest_title" => "Latest Posts",
						"tshowcase_single_show_photo" => "",
						"tshowcase_single_show_social" => "",
						"tshowcase_single_show_position" => "",
						"tshowcase_mailto" => "",
						"tshowcase_exclude_from_search" => "true",
						"tshowcase_timg_width" => "50",
						"tshowcase_timg_height" => "50",
						"tshowcase_tpimg_width" => "50",
						"tshowcase_tpimg_height" => "50",					
							
		);
		
		update_option('tshowcase-settings', $arr);
	}
}


//New Icons
$tshowcase_wp_version =  floatval( get_bloginfo( 'version' ) );

if($tshowcase_wp_version >= 3.8) {
	add_action( 'admin_head', 'tshowcase_font_icon' );
}


function tshowcase_font_icon() {
?>

		<style> 
			#adminmenu #menu-posts-tshowcase div.wp-menu-image img { display: none;}
			#adminmenu #menu-posts-tshowcase div.wp-menu-image:before { content: "\f307"; }
		</style>


<?php
}




?>