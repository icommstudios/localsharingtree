<?php


if(!function_exists("selfURL1")){ function selfURL1() {
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = "http".$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
			: (":".$_SERVER["SERVER_PORT"]);
		return $protocol."://".$_SERVER['SERVER_NAME'].$port;
	}
}

if(!function_exists("FilterPath")){

	function FilterPath(){
		$path=dirname(realpath($_SERVER['SCRIPT_FILENAME']));
		$path_parts = pathinfo($path);
		return $path;
	}
}
	
if(function_exists("get_option")){ 

	// DELETE DEFAULT LINKS
	if(isset($_POST['RESETME']) && $_POST['RESETME'] =="yes"){

		mysql_query("DELETE FROM $wpdb->links");
		mysql_query("DELETE FROM $wpdb->posts");
		mysql_query("DELETE FROM $wpdb->postmeta");
		mysql_query("DELETE FROM $wpdb->terms");
		mysql_query("DELETE FROM $wpdb->term_taxonomy");
		mysql_query("DELETE FROM $wpdb->term_relationships");
		mysql_query("DELETE FROM wp_shopperpress_orders");
		mysql_query("DELETE FROM $wpdb->comments");
		mysql_query("DELETE FROM $wpdb->commentmeta");
	}
  
update_option("ppt_load_countries",""); 

	update_option("logo_url", 			"");
	update_option("copyright", 			"Enter your copyright text here.");
 
	
	update_option("display_linkcloak", 	"no");
	update_option("tc_linkcloak", 		"");

	update_option("email", "me@mywebsite.com");

	update_option("display_showpages","sub");

	// DEFAULT CURRENCY
	update_option("currency_caption", "US Dollar");
	update_option("currency_code", "USD");
	update_option("currency_symbol", "$");
	update_option("currency_value", "1.00000");

	update_option("currency_caption_1", " UK Pound");
	update_option("currency_code_1", "GBP");
	update_option("currency_symbol_1", "&#163;");
	update_option("currency_value_1", "1.88000");

	update_option("currency_caption_2", "Euro");
	update_option("currency_code_2", "EUR");
	update_option("currency_symbol_2", "&#8364;");
	update_option("currency_value_2", "1.28000");

	// SHOPPERPRESS DEFAULT SETTINGS 
	update_option("shopperpress_system", "cart");
	update_option("language", "language_english");
	update_option("theme", "shopperpress-default");

	update_option("advertising_top_checkbox", "1");
	update_option("advertising_top_adsense", '<a href="http://www.'.''.'premiumpress.com/?source=shopperpress"><img src="http://www.premiumpress.com/banner/468x60_1.gif" alt="premium wordpress themes" /></a>');
	
	update_option("advertising_left_checkbox", "0");
	update_option("advertising_left_adsense", '');

update_option("advertising_footer_checkbox", "0");
update_option("advertising_footer_adsense", '');

update_option("listbox_custom_title","Order Results By");	
update_option("footer_text","<h3>Welcome to our website!</h3><p><strong>Your introduction goes here!</strong><br />You can customize and edit this text via the admin area to create your own introduction text for your website.</p><p>You can customize and edit this text via the admin area to create your own introduction text for your website.</p> <p><b>We accept: </b> <br /><br /><img src='http://shopperpress.com/inc/cards.gif' alt='cards' /></p> ");	

	update_option("display_search", "yes1");
	update_option("display_categories", "yes");
	update_option("display_account", "yes");
	update_option("display_cart", "yes");
	update_option("display_featured", "yes");
	update_option("display_footer", "yes");
	update_option("display_view", "yes");
	update_option("display_gallery_subcats", "no");
	update_option("display_liststyle", "thumb_view");

	update_option("display_pricetag", "yes");

	update_option("coupon_enable", "no");
	
	// featured items
	update_option("display_imagesection", "yes");	
	update_option("display_fea_num", "3");

	// custom list
	update_option("custom_field1", "Size");
	update_option("custom_field2", "Product Color");
	update_option("custom_field3", "Extra Values");
	update_option("custom_field6", "More Values");
	
	// amazon
	update_option("amazon_ID", "mf05-20");
	
	// extra	
	update_option("display_categories_leftmenu", "show");
	
	// SEARCH RESULTS
	update_option("listbox_custom", "1");
	update_option("listbox_custom_title", "Order Results By");
	
	update_option("posts_per_page", "12");

// DISPLAY SETTINGS	
update_option("display_home_products_cat","");
update_option("display_home_products","yes");
update_option("display_themecolumns",3);
update_option("checkout_skip_registration","no");

 
/* ====================== PREMIUM PRESS CATEGORIES ====================== */

 
$args = array('cat_name' => "Store Products" ); 
$cat_id = wp_insert_term("Store Products", 'category', $args);
$STOREID = $cat_id['term_id'];
	wp_create_category1('Projects', $cat_id['term_id']);
	wp_create_category1('Speakers', $cat_id['term_id']);
	wp_create_category1('DVD Players', $cat_id['term_id']);

$args = array('cat_name' => "Affiliate Products" ); 
$cat_id = wp_insert_term("Affiliate Products", 'category', $args);
$AFFID = $cat_id['term_id'];
	wp_create_category1('Affiliate Sub Category 1', $cat_id['term_id']);
	wp_create_category1('Affiliate Sub Category 2', $cat_id['term_id']);
	
$args = array('cat_name' => "Download Products" ); 
$cat_id = wp_insert_term("Download Products", 'category', $args);
$DWNID = $cat_id['term_id'];
	wp_create_category1('Download Sub Category 1', $cat_id['term_id']);
	wp_create_category1('Download Sub Category 2', $cat_id['term_id']);
		

/* ====================== PREMIUM PRESS CATEGORIES ====================== */
$pages_array = "";
// CREATE PAGES
$page1 = array();
$page1['post_title'] = 'Account';
$page1['post_content'] = '';
$page1['post_status'] = 'publish';
$page1['post_type'] = 'page';
$page1['post_author'] = 1;
$page1_id = wp_insert_post( $page1 );
$pages_array .= $page1_id.",";
update_post_meta($page1_id , '_wp_page_template', 'tpl-myaccount.php');
// CREATE PAGES
$page1 = array();
$page1['post_title'] = 'Articles';
$page1['post_content'] = '';
$page1['post_status'] = 'publish';
$page1['post_type'] = 'page';
$page1['post_author'] = 1;
$page1_id = wp_insert_post( $page1 );
//$ARTID = $page1_id;
update_post_meta($page1_id , '_wp_page_template', 'tpl-articles.php');
// CREATE PAGES
$page1 = array();
$page1['post_title'] = 'Contact';
$page1['post_content'] = '';
$page1['post_status'] = 'publish';
$page1['post_type'] = 'page';
$page1['post_author'] = 1;
$page1_id = wp_insert_post( $page1 );
update_post_meta($page1_id , '_wp_page_template', 'tpl-contact.php');
// CREATE PAGES
$page1 = array();
$page1['post_title'] = 'Checkout';
$page1['post_content'] = '';
$page1['post_status'] = 'publish';
$page1['post_type'] = 'page';
$page1['post_author'] = 1;
$page1_id = wp_insert_post( $page1 );
$pages_array .= $page1_id.",";
update_post_meta($page1_id , '_wp_page_template', 'tpl-checkout.php');
// CREATE PAGES
$page1 = array();
$page1['post_title'] = 'Callback';
$page1['post_content'] = '';
$page1['post_status'] = 'publish';
$page1['post_type'] = 'page';
$page1['post_author'] = 1;
$page1_id = wp_insert_post( $page1 );
$pages_array .= $page1_id.",";
update_post_meta($page1_id , '_wp_page_template', 'tpl-callback.php');
 
 

//wp_delete_term( $CATID+1, 'category' );

// HIDDEN PAGES
update_option("excluded_pages",$pages_array);
//update_option("article_cats", "-".$CATID);
// PAGE VALES
update_option("checkout_url", 	selfURL1().str_replace("wp-admin/","",str_replace("admin.php?page=setup","",str_replace("themes.php?activated=true","",$_SERVER['REQUEST_URI'])))."checkout/");
update_option("dashboard_url",	selfURL1().str_replace("wp-admin/","",str_replace("admin.php?page=setup","",str_replace("themes.php?activated=true","",$_SERVER['REQUEST_URI'])))."account/"); 
update_option("contact_url", 		selfURL1().str_replace("wp-admin/","",str_replace("admin.php?page=setup","",str_replace("themes.php?activated=true","",$_SERVER['REQUEST_URI'])))."contact/");
update_option("manage_url", 		selfURL1().str_replace("wp-admin/","",str_replace("admin.php?page=setup","",str_replace("themes.php?activated=true","",$_SERVER['REQUEST_URI'])))."manage/");

// POST PRUN SETTINGS
update_option("post_prun","no");
update_option("prun_period","30");

// IMAGE STORAGE PATHS
// IMAGE STORAGE PATHS
update_option("imagestorage_link",get_template_directory_uri()."/thumbs/");
update_option("imagestorage_path",str_replace("\\","/",TEMPLATEPATH)."/thumbs/");


update_option("upload_path","wp-content/themes/shopperpress/thumbs");
update_option("upload_url_path",selfURL1().str_replace("wp-admin/","",str_replace("admin.php?page=setup","",str_replace("themes.php?activated=true","",$_SERVER['REQUEST_URI'])))."wp-content/themes/shopperpress/thumbs");

update_option("display_currency_format","2");

/* ================ EXAMPLE ARTICLE ===================== */

// ADD NEW PRODUCTS
$my_post = array();
$my_post['post_title'] 		= "Example Website Article 1";
$my_post['post_content'] 	= "<h1>This is an example h1 title</h1> <h2>This is an example h2 title</h2> <h3>This is an example h3 title</h3> <br> <p>This is an example paragraph of text added via the admin area.</p> <p>This is an example paragraph of text added via the admin area.</p> <p>This is an example paragraph of text added via the admin area.</p> <ul><li>example list item 1</li><li>example list item 2</li><li>example list item 3</li><li>example list item 4</li><li>example list item 5</li></ul> <p>This is an example paragraph with a link in it, click here to the <a href='http://www.premiumpress.com' title='premium wordpress themes'>premium wordpress themes website.</a></p>";
$my_post['post_excerpt'] 	= "This is an example article that you can create for your website just like any normal Wordpress blog post. You can use the 'image' custom field to attach a prewview picture also!";
$my_post['post_status'] 	= "publish";
$my_post['post_type'] 		= "article_type";
//$my_post['post_category'] 	= array($ARTID);
$my_post['tags_input'] 		= "article,blog";
$POSTID 					= wp_insert_post( $my_post );
add_post_meta($POSTID, "type", "article");	
add_post_meta($POSTID, "image", "article.jpg");	

$my_post = array();
$my_post['post_title'] 		= "Example Website Article 2";
$my_post['post_content'] 	= "<h1>This is an example h1 title</h1> <h2>This is an example h2 title</h2> <h3>This is an example h3 title</h3> <br> <p>This is an example paragraph of text added via the admin area.</p> <p>This is an example paragraph of text added via the admin area.</p> <p>This is an example paragraph of text added via the admin area.</p> <ul><li>example list item 1</li><li>example list item 2</li><li>example list item 3</li><li>example list item 4</li><li>example list item 5</li></ul> <p>This is an example paragraph with a link in it, click here to the <a href='http://www.premiumpress.com' title='premium wordpress themes'>premium wordpress themes website.</a></p>";
$my_post['post_excerpt'] 	= "This is an example article that you can create for your website just like any normal Wordpress blog post. You can use the 'image' custom field to attach a prewview picture also!";
$my_post['post_status'] 	= "publish";
$my_post['post_type'] 		= "article_type";
//$my_post['post_category'] 	= array($ARTID);
$my_post['tags_input'] 		= "example tag,blog tag";
$POSTID 					= wp_insert_post( $my_post );
add_post_meta($POSTID, "type", "article");	
add_post_meta($POSTID, "image", "article.jpg");	


/* ================ EXAMPLE PRODUCTS ===================== */


$my_post = array();
$my_post['post_title'] 		= "Test Product 1";
$my_post['post_content'] 	= "In honor of the graceful, lemonade-making optimists of the world, may we present this joyful, bright bouquet of yellow Gerbera daisies, alstroemeria, daisy poms and more, designed in a striking glass gathering vase.";
$my_post['post_excerpt'] 	= "<ul><li>100% Satisfaction and Freshness Guaranteed</li><li>If this is a gift, please remember to check the This is a gift checkbox.</li><li>In order to provide a more accurate delivery, please ensure that an exact delivery date is selected.</li></ul>";
$my_post['post_status'] 	= "publish";
$my_post['post_category'] 	= array($STOREID);
$my_post['tags_input'] 		= "tag1,tag2";
$POSTID 					= wp_insert_post( $my_post );
 
add_post_meta($POSTID, "SKU", "001");	
add_post_meta($POSTID, "hits", "0");	
add_post_meta($POSTID, "price", "30");	
add_post_meta($POSTID, "old_price", "49.99");
add_post_meta($POSTID, "image", "http://ecx.images-amazon.com/images/I/41PbLTMtFgL.jpg");
add_post_meta($POSTID, "images", "http://ecx.images-amazon.com/images/I/41PbLTMtFgL.jpg,http://ecx.images-amazon.com/images/I/41258sEWRLL.jpg");
add_post_meta($POSTID, "customlist1", "small,medium,large,extra large");	
add_post_meta($POSTID, "customlist2", "red,blue,green,yellow");	
add_post_meta($POSTID, "customlist3", "10=Extra $10,20=Extra $20,30=Extra $30");	
add_post_meta($POSTID, "customlist6", "40=Extra $40,50=Extra $50,60=Extra $60");	
add_post_meta($POSTID, "qty", "50");
add_post_meta($POSTID, "featured", "yes"); 

 $my_post = array();
$my_post['post_title'] 		= "Test Product 2";
$my_post['post_content'] 	= "Two's Company Bee Happy Dancing Solar Flower set/2";
$my_post['post_excerpt'] 	= "<ul><li>Guaranteed Happiness</li><li>Set of a pink and blue dancing flower no batteries necessary</li><li>Solar Powered, Pollen-less and requires no water</li><li>Small and portable</li></ul>";
$my_post['post_status'] 	= "publish";
$my_post['post_category'] 	= array($STOREID);
$my_post['tags_input'] 		= "tag1,tag2";
$POSTID 					= wp_insert_post( $my_post );
 
add_post_meta($POSTID, "SKU", "001");	
add_post_meta($POSTID, "hits", "0");	
add_post_meta($POSTID, "price", "10");	
add_post_meta($POSTID, "old_price", "23.99");
add_post_meta($POSTID, "image", "http://ecx.images-amazon.com/images/I/41fOqtdUkZL.jpg");
add_post_meta($POSTID, "images", "http://ecx.images-amazon.com/images/I/41fOqtdUkZL.jpg,http://ecx.images-amazon.com/images/I/41TGax4TbML.jpg");
add_post_meta($POSTID, "customlist1", "small,medium,large,extra large");	
add_post_meta($POSTID, "customlist2", "red,blue,green,yellow");	
add_post_meta($POSTID, "customlist3", "10=Extra $10,20=Extra $20,30=Extra $30");	
add_post_meta($POSTID, "customlist6", "40=Extra $40,50=Extra $50,60=Extra $60");	
add_post_meta($POSTID, "qty", "50");
add_post_meta($POSTID, "featured", "no"); 


$my_post = array();
$my_post['post_title'] 		= "Example Free Downloadable Product";
$my_post['post_content'] 	= "Let the freedom of The Sims 3 and the opportunities of The Sims 3 Ambitions inspire you! The Sims 3 Deluxe includes both The Sims 3 and the popular expansion pack The Sims 3 Ambitions in one! Start off creating Sims with unique personalities, customizing everything from their appearances to their personalities to their homes, and then share them online. Make their dreams come true - or don¡¯t! Then widen their horizons with killer new career tracks. Will your Sim save the day as a brave firefighter, become a billionaire high-tech inventor, change the town as a leading architect, or wreak havoc as a neighborhood nuisance? Whatever direction they take, their future is in your hands!";
$my_post['post_excerpt'] 	= "This is a example product short description (excerpt) you can customize these seprate to the main description to give your product a better introduction.";
$my_post['post_status'] 	= "publish";
$my_post['post_category'] 	= array($DWNID);
$my_post['tags_input'] 		= "tag1,tag2";
$POSTID 					= wp_insert_post( $my_post );
 
add_post_meta($POSTID, "SKU", "001");	
add_post_meta($POSTID, "hits", "0");	
add_post_meta($POSTID, "price", "8.00");	
add_post_meta($POSTID, "old_price", "25.99");
add_post_meta($POSTID, "image", "http://ecx.images-amazon.com/images/I/610craS6hSL._AA300_.jpg");
add_post_meta($POSTID, "images", "http://ecx.images-amazon.com/images/I/41r1bI%2BjfHL._AA300_.jpg,http://ecx.images-amazon.com/images/I/5116QPxZE%2BL._AA300_.jpg");
add_post_meta($POSTID, "file", "example-download.zip"); 
add_post_meta($POSTID, "file_type", "free"); 
add_post_meta($POSTID, "featured", "yes"); 

$my_post = array();
$my_post['post_title'] 		= "Example Paid Downloadable Product";
$my_post['post_content'] 	= "You are the sole ruler of a remote banana republic. Your people are free willed individuals who have their own wants and needs in your world. Keep them happy by providing them with homes, places of worship, healthcare, and entertainment! Fight against poverty, corruption and rebels, make your own people happy or enforce your rule through military strength. When election day comes around, will your people reelect you to stay in office or will you have to flex your muscles to keep your seat of power?";
$my_post['post_excerpt'] 	= "This is a example product short description (excerpt) you can customize these seprate to the main description to give your product a better introduction.";
$my_post['post_status'] 	= "publish";
$my_post['post_category'] 	= array($DWNID);
$my_post['tags_input'] 		= "tag1,tag2";
$POSTID 					= wp_insert_post( $my_post );
 
add_post_meta($POSTID, "SKU", "001");	
add_post_meta($POSTID, "hits", "0");	
add_post_meta($POSTID, "price", "8.00");	
add_post_meta($POSTID, "old_price", "25.99");
add_post_meta($POSTID, "image", "http://ecx.images-amazon.com/images/I/51ATxXNQxYL._AA300_.jpg");
add_post_meta($POSTID, "images", "http://ecx.images-amazon.com/images/I/6192pX7S0EL._AA300_.jpg,http://ecx.images-amazon.com/images/I/6128aThSR2L._AA300_.jpg");
add_post_meta($POSTID, "file", "example-download.zip"); 
add_post_meta($POSTID, "file_type", "paid"); 
add_post_meta($POSTID, "featured", "no"); 
 	
add_post_meta($POSTID, "qty", "50");


// ENABLE PAYPAL TEST
$cb = selfURL1().str_replace("wp-admin/","",str_replace("admin.php?page=setup","",str_replace("themes.php?activated=true","",$_SERVER['REQUEST_URI'])))."callback/";

update_option("gateway_paypal","yes");
update_option("paypal_email","example@paypal.com");
update_option("paypal_return",$cb);
update_option("paypal_cancel",$cb);
update_option("paypal_notify",$cb);
update_option("paypal_currency","USD");

}






?>