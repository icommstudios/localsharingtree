<?php


// QUE SLIDER   
wp_register_script( 'slider', PPT_THEME_URI.'/template_shopperpress/js/jquery.etalage.min.js');
wp_enqueue_script( 'slider' );

// REGISTER COLOURBOX
wp_register_script( 'colorbox',  get_template_directory_uri() .'/PPT/js/jquery.colorbox-min.js');
wp_enqueue_script( 'colorbox' );

wp_register_style( 'colorbox',  get_template_directory_uri() .'/PPT/css/css.colorbox.css');
wp_enqueue_style( 'colorbox' );

/* =============================================================================
   PAGE REDIRECT // V7 // 16TH MARCH
   ========================================================================== */

$redirect = get_post_meta($post->ID, "redirect", true);
if($redirect == "yes"){
	$redirect_to = premiumpress_link($post->ID,true);		
			 
	if($redirect_to != ""){ 
		header("location: ".$redirect_to);
		exit();
	}
}

 // CONTENT FILTERS FOR AMAZON PRODUCTS
function filter_ppt_content($content){
$content = str_replace("images. ","images.", str_replace("amazon. ","amazon.", str_replace("g-ecx. ","g-ecx.", str_replace(". jpg",".jpg", str_replace(". _","._", str_replace("http : ","http:", $content ) ) ) ) ) );
return $content;
}
add_filter("the_content","filter_ppt_content");	

 		
/* -------------- VERSION 5.X UPLOAD FORM OPTIONS --------------- */
if(isset($_POST['doupload'])){	
		$image = premiumpress_upload($_FILES['attachment']);
		$image = str_replace(",","",$image);
		$result = 1;
		sleep(1);
	 
	?> 
	<script language="javascript" type="text/javascript">window.top.window.stopUpload(<?php echo $result; ?>,'<?php echo $image; ?>');</script>  
	<?php 
	 die();
}

$GLOBALS['nosidebar'] = 1;
$GLOBALS['IS_SINGLEPAGE'] = 1;


// PRODUCT CATEGORY
$GLOBALS['singleCategory'] 	= get_the_category($post->ID); 

$GLOBALS['postID'] 		= $post->ID;

// SETUP GLOBAL VALUES FROM CUSTOM DATA
$GLOBALS['qty'] 		= get_post_meta($post->ID, 'qty', true);
$GLOBALS['image'] 		= get_post_meta($post->ID, 'image', true);
$GLOBALS['images'] 		= get_post_meta($post->ID, 'images', true);
$GLOBALS['map'] 		= get_post_meta($post->ID, "map_location", true);
$GLOBALS['old_price'] 	= GetPrice(get_post_meta($post->ID, "old_price", true));
$GLOBALS['price'] 		= GetPrice(get_post_meta($post->ID, "price", true));
$GLOBALS['related'] 	= get_post_meta($post->ID, 'related', true);

// DISLAY OPTIOSN
$GLOBALS['display_single_related'] 				= get_option("display_single_related");
$GLOBALS['display_single_additionalimages'] 	= get_option("display_single_additionalimages");
$GLOBALS['display_single_epb'] 					= get_option("display_single_epb");

 // FILE DOWNLOAD FIELDS
$GLOBALS['filename'] 				= get_post_meta($post->ID, "file", true);
$GLOBALS['file_type'] 				= get_post_meta($post->ID, "file_type", true);

if($GLOBALS['file_type'] == "cart"){ $GLOBALS['file_type']=""; $GLOBALS['filename'] = ""; }

// AMAZON INTEGRATION
$GLOBALS['amazon_link'] 			= get_post_meta($post->ID, "amazon_link", true);
if(strlen($GLOBALS['amazon_link']) > 2){

	 
	$GLOBALS['amazon_reviews_link'] 	= get_post_meta($post->ID, "amazon_reviews_link", true);
	$GLOBALS['amazon_lastchecked'] 		= get_post_meta($post->ID, "amazon_lastchecked", true);
	if($GLOBALS['amazon_lastchecked'] == ""){ $GLOBALS['amazon_lastchecked'] = $post->post_date; }
	$GLOBALS['amazon_checkout'] 		= get_option("display_amazon_checkout"); 
	
	// Amazon Checkout
	if($GLOBALS['amazon_checkout'] == "no" && strlen($GLOBALS['amazon_link']) > 1){
	$GLOBALS['hidecheckoutbtn']=1;
	}
 
	// AMAZON REVIEWS FIX // IF THE LAST CHECK HAS EXPIRED 24 HOURS, REIMPORT AUTOMATICALLY THE NEW REVIEWS LINK
	if(get_option('enabled_amazon_updater') == "yes" &&  date('Y-m-d h:i:s') > date("Y-m-d H:i:s", strtotime($GLOBALS['amazon_lastchecked']." +1 days" ))  ){
		
		// SET LAST CHECKED
		update_post_meta($post->ID, "amazon_lastchecked", date("Y-m-d H:i:s"));
		
		require_once (TEMPLATEPATH ."/PPT/class/class_amazon.php");		
		$obj = new AmazonProductAPI();
		$result = $obj->getItemByAsin(get_post_meta($post->ID, "amazon_guid", true),get_option('enabled_amazon_updater_country') );	
		
		$GLOBALS['amazon_reviews_link'] = str_replace("!!aaqq","",$result->Items->Item->CustomerReviews->IFrameURL);
				
		// UPDATE POST META
		update_post_meta($post->ID, "amazon_reviews_link", $GLOBALS['amazon_reviews_link']);
		update_post_meta($post->ID, "amazon_lastchecked", date("Y-m-d H:i:s"));
	
	}
}

// DATAFEEDR, AFFILIATE INTEGRATION
$GLOBALS['buy_link'] 					= get_post_meta($post->ID, "buy_link", true);
if($GLOBALS['buy_link'] == ""){
$GLOBALS['buy_link'] = get_post_meta($post->ID, "link", true);
}
if(strlen($GLOBALS['buy_link']) > 0){
$GLOBALS['hidecheckoutbtn']=1;
}

 
$GLOBALS['buy_network_icon'] 			= get_post_meta($post->ID, "datafeedr_network", true);
if($GLOBALS['buy_network_icon'] == ""){ $GLOBALS['buy_network_icon'] = "none"; }
 

get_header(); 
 
if (have_posts()) : while (have_posts()) : the_post(); ?>

<div id="AJAXRESULTS"></div><!-- AJAX RESULTS -do not delete- -->
     
<div id="BackGroundWrapper">

    <div id="galleryblock">
    
        <ul id="gallery">
        
   		<li>
        
        <?php
		
		// IMAGE DISPLAY // V7 
		echo premiumpress_image($post->ID,"",array('alt' => $post->post_title, 'class'=>'etalage_thumb_image', 'style' => 'auto' ));  
		
		echo premiumpress_image($post->ID,"",array('alt' => $post->post_title, 'class'=>'etalage_source_image',  'style' => 'auto' )); 
		
		?> 
     
        </li>        
                        
        <?php  if(strlen($GLOBALS['images']) > 5 ){ $i=0; foreach (explode(",",$GLOBALS['images']) as $img){ if(strlen($img) > 2){  ?>
        <li>
        <img class="etalage_thumb_image" src="<?php echo premiumpress_image_check(trim($img),"image"); ?>" />
        <a href="<?php echo premiumpress_image_check(trim($img),"t"); ?>" class="lightbox"><img class="etalage_source_image" src="<?php echo premiumpress_image_check(trim($img),"t"); ?>" /></a>
        </li>
        <?php $i++; } } }  ?>
      
        </ul> 
          
    </div><!-- end gallery -->
              
    
    <div id="descriptionBlock">
    
        <h1 class="title"><?php the_title(); ?></h1>
        
        <?php echo BuyProductWidget(); ?>
        
    </div> <!-- end buy block --> 
    
    <div class="clearfix"></div>
    
    <div class="padding">
    
        <div class="shareButton">
        
        <ul class="add-to-links">
        
        <li><a class="link-largeimages lightbox" rel="lightbox" href="<?php echo premiumpress_image_check(trim($GLOBALS['image']),"t"); ?>"><?php echo $PPT->_e(array('sp','3')) ?></a></li>
        
          
		<?php if(isset($GLOBALS['shortcomments'])){ ?>
        <li><a class="link-compare"  href="javascript:void(0);" onclick="toggleLayer('commentform');" rel="nofollow"><?php echo $PPT->_e(array('sp','4')) ?></a></li>
        <?php } ?>	
        
        <?php if(get_option("display_wishlist") =="yes"){ ?>
        
        <li><a class="link-wishlist"  href="javascript:void(0);" <?php if($userdata->ID){ ?>onclick="UpdateWishlist(<?php echo $post->ID; ?>,'add','AJAXRESULTS','<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','wishlist');" 
        <?php }else{ ?>onclick="alert('<?php echo $PPT->_e(array('ajax','1')) ?>','WishlistAlert');"<?php } ?> rel="nofollow"><?php echo $PPT->_e(array('fav','1')) ?></a></li>
       
		<li><a class="link-comparelist"  href="javascript:void(0);" <?php if($userdata->ID){ ?>onclick="UpdateWishlist(<?php echo $post->ID; ?>,'add','AJAXRESULTS','<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','compare');" 
        <?php }else{ ?>onclick="alert('<?php echo $PPT->_e(array('ajax','1')) ?>');"<?php } ?> rel="nofollow"><?php echo $PPT->_e(array('fav','2')) ?></a></li>
       
       
        <?php } ?>
        
        <li><a href="javascript:void(0);" class="addthis_button_email link-email" rel="nofollow"><?php echo $PPT->_e(array('sp','5')) ?></a></li> 
        
       
        
        </ul>
        
       </div><!-- end share buttons -->
       
  </div>
  
  <div id="WishlistAlert"></div>
       
    
 	<div class="clearfix"></div> 
 
</div><!-- end background top -->


<?php if(get_option("display_social") =="yes"){ ?>  

    <div class="addthis_toolbox addthis_default_style addthis_25x25_style">
    <a class="addthis_button_preferred_1"></a>
    <a class="addthis_button_preferred_2"></a>     
    <a class="addthis_button_compact"></a>
    <a class="addthis_counter addthis_bubble_style"></a>
    </div>

<?php } ?>
    
    
<ul class="tabs">
 
    <li><a href="#info"><?php echo $PPT->_e(array('title','17')) ?></a></li> 
    
   <?php if(get_option('display_single_comments') == "yes"){ ?> <li><a href="#c"><?php echo $PPT->_e(array('comment','11')) ?></a></li><?php } ?> 

</ul> 


    
<div class="tab_container">


	<div id="info" class="tab_content entry article">


        
    <?php the_content(); ?>
    
        
    <div class="clearfix"></div>
    
    <p> <span  class="icon cat"><?php the_category(', ') ?></span> <?php if(get_option("display_search_tags") =="yes"){ the_tags("<span class='icon arrow'>",' ','</span>'); } ?></p>  
    
    </div><!-- end inf tab -->

    <div id="c" class="tab_content">
         
    <?php if(get_option('display_single_comments') == "yes" && isset($GLOBALS['amazon_reviews_link']) && strlen($GLOBALS['amazon_reviews_link']) > 5){ 
               echo '<iframe frameborder="0" border="0" cellspacing="0" style="border-style: none;width: 100%; height: 1000px; margin-top:20px;" src="'.$GLOBALS['amazon_reviews_link'].'"></iframe>'; 
     } ?>	
           
            
    <?php if(get_option('display_single_comments') == "yes"){ comments_template(); } ?>
    
    </div><!-- end comments tab --> 

        
   
<div class="clearfix"></div> 

</div><!-- end tabs -->   
   
      
 
 
<?php endwhile; else :  endif;   ?>

<?php if(get_option('display_single_related') == "yes"){ ?>

<h4><?php echo $PPT->_e(array('sp','6')) ?></h4> 
 
	
	<ul class="display thumb_view">  
    
	<?php wp_reset_query(); 
	
	if(isset($GLOBALS['nosidebar0']) ){ $GLOBALS['galleryblockstop']=4; }else{ $GLOBALS['galleryblockstop']=3; }
    if(isset($GLOBALS['related']) && strlen($GLOBALS['related']) > 0){  
	
	$GLOBALS['query_string_new'] = array('post__in' => explode(",",$GLOBALS['related'])); 
	
    }else{ 
	$showco = $GLOBALS['galleryblockstop']*2;
	$GLOBALS['galleryblockhideID'] = array($GLOBALS['postID']); // hide post ID's
	$GLOBALS['query_string_new'] = 'showposts='.$showco.'&orderby=rand&cat='.$GLOBALS['singleCategory'][0]->cat_ID; }
     $GLOBALS['counter']=0;  $PPTDesign->GALLERYBLOCK();
    ?> 
             
	</ul><!-- end related products --> 

<?php wp_reset_query(); } ?>

 
<script type="text/javascript">

jQuery(document).ready(function(){

	// ENABLE CART BUTTON WHEN PAGE HAS FINISHED LOADING
	enableCartButton();

	// LIGHTBOX FOR IMAGE POPUP
	jQuery(".lightbox").colorbox({rel:'lightbox', slideshow:true});	
	
	// JQUERY FOR IMAGE GALLERY
	jQuery('#gallery').etalage({
	<?php if(isset($GLOBALS['nosidebar0']) ){ ?>
					thumb_image_width: 260,
					thumb_image_height: 250,
	<?php }elseif(  isset($GLOBALS['nosidebar3'])){ ?>		
					thumb_image_width: 360,
					thumb_image_height: 350,										
	<?php }else{ ?>
					thumb_image_width: 160,
					thumb_image_height: 150,	
	<?php } ?>				
					source_image_width: 900,
					source_image_height: 900,
					zoom_area_width: 500,
					zoom_area_height: 500,
					small_thumbs: <?php $GLOBALS['imgCount'] = count(explode(",",$GLOBALS['images'])); if($GLOBALS['imgCount'] < 4){ echo "4"; }else{ echo $GLOBALS['imgCount']; } ?>,
					smallthumb_inactive_opacity: 0.3,
					smallthumbs_position: 'left',
					show_icon: false,
					autoplay: false,
					keyboard: false,
					zoom_easing: true,
					click_to_zoom: true
					
	}); 
});

</script> 


<?php get_footer(); ?>