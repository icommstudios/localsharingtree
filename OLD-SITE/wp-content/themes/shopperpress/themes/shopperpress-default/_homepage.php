<?php $GLOBALS['nosidebar'] = 1; $GLOBALS['nosidebar-left']=1; get_header(); ?> 
 
 <?php if(strlen(do_shortcode(get_option("welcome_text"))) > 1){ echo '<div class="clearfix"></div><br />'.nl2br(stripslashes(do_shortcode(get_option("welcome_text")))).'<div class="clearfix"></div>'; }?>
 
<?php if(get_option("display_home_products") =="yes"){ 
$STRING = "";
$postslist = query_posts($ThemeDesign->HOMEPRODUCTS(get_option("display_home_products_num")));
if(!empty($postslist)){
	
	
	$STRING .= '<div id="SPHOME"><div id="style1_wrapper" '.$sty.'><div id="style1" class="style1"><div class="previous_button"></div><div class="container"><ul>'; 
		 
		foreach ($postslist as $post ){ 
		  
			$STRING .= '<li>
			<div class="imgwrapper">
			<a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">
				<img src="'.premiumpress_image($post,"url","&amp;w=120&amp;h=90").'" alt="'.$post->post_title.'" />
			</a>
			</div>
			<div class="text">'.$post->post_title.'</div>
			
			<div class="excerpt">'.substr(strip_tags($post->post_excerpt),0,180).'...</div>
			
			';
			
			 $price = get_post_meta($post->ID, "price", true);
			
				if($price != ""){
				
					$STRING .= '			
					<div class="actions">
						<a href="'.get_permalink($post->ID).'"><span class="add-box"><span>add to cart</span></span></a>				
						<div class="price-box"> 
							<span class="price">'.premiumpress_price(get_post_meta($post->ID, "price", true),$GLOBALS['premiumpress']['currency_symbol'],$GLOBALS['premiumpress']['currency_position'],1).'</span>
						</div>        
					</div> ';
				
				}
			 
			 
			 $STRING .= ' 
			 
			 
			 </li>';
		
		 }  

	$STRING .= '</ul></div><div class="next_button"></div></div></div></div><div class="clearfix"></div>';
	
	$STRING .= '<script src="'.PPT_PATH.'js/jquery.jcarousel.pack.js" type="text/javascript"></script>';
	$STRING .= '<script src="'.PPT_PATH.'js/jquery.jcarousellite_1.0.1.js" type="text/javascript"></script>';
	$STRING .= '<script type="text/javascript">jQuery(function() {    jQuery(".style1").jCarouselLite({        btnNext: ".next_button",        btnPrev: ".previous_button",		visible:4,		scroll: 2, auto:20000 }); });</script>';
}
echo $STRING;
}
?>


<div class="f_half left">

<a href="<?php echo get_option("home_link_1"); ?>" title="<?php echo get_option("home_title_1"); ?>">
            <img src="<?php if(strlen(get_option("home_image_1")) > 1){ echo premiumpress_image_check(get_option("home_image_1"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge1.gif<?php } ?>" alt="<?php echo get_option("home_title_1");  ?>" /></a>

</div>

<div class="f_half left a2">

<a href="<?php echo get_option("home_link_2"); ?>" title="<?php echo get_option("home_title_2"); ?>"><img src="<?php if(strlen(get_option("home_image_2")) > 1){ echo premiumpress_image_check(get_option("home_image_2"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge2.gif<?php } ?>" alt="<?php echo get_option("home_title_2"); ?>" /></a>

</div>


<?php get_footer(); ?>