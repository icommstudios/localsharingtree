<?php $GLOBALS['nosidebar'] = 1; $GLOBALS['nosidebar-left']=1;  get_header();

if(strlen(get_option("home_image_4")) > 1){ $BADGET4 =  premiumpress_image_check(get_option("home_image_4"),"full"); }else{ $BADGET4 =PPT_CUSTOM_STYLE_URL."badge4.gif"; } 

 ?> 


<?php


 
?>

<div class="full clearfix box"> 

			<div class="f3 left"> 
            
				  <a href="<?php echo get_option("home_link_1"); ?>" title="<?php echo get_option("home_title_1"); ?>">
        <img src="<?php if(strlen(get_option("home_image_1")) > 1){ echo premiumpress_image_check(get_option("home_image_1"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge1.gif<?php } ?>" alt="<?php echo get_option("home_title_1");  ?>" /></a>
			</div> 
		
			<div class="f3 left"> 
            
            <div style="padding-left:6px;">
				 <a href="<?php echo get_option("home_link_2"); ?>" title="<?php echo get_option("home_title_2"); ?>"><img src="<?php if(strlen(get_option("home_image_2")) > 1){ echo premiumpress_image_check(get_option("home_image_2"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge2.gif<?php } ?>" alt="<?php echo get_option("home_title_2"); ?>" /></a>
    		</div>
			</div> 
			
			<div class="f3 left"> 
            
            <div style="padding-left:11px;">
				 <a href="<?php echo get_option("home_link_3"); ?>" title="<?php echo get_option("home_title_3"); ?>"><img src="<?php if(strlen(get_option("home_image_3")) > 1){ echo premiumpress_image_check(get_option("home_image_3"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge3.gif<?php } ?>" alt="<?php echo get_option("home_title_3"); ?>" /></a>
       	</div>
			</div> 
			
</div> 



<?php if(strlen(do_shortcode(get_option("welcome_text"))) > 1){ echo '<div class="clearfix"></div><br />'.nl2br(stripslashes(do_shortcode(get_option("welcome_text")))).'<div class="clearfix"></div>'; }?>


<?php if(get_option("display_home_products") =="yes"){ ?>

<div class="RowWrapper">
<?php

$content = "";
$postslist = query_posts($ThemeDesign->HOMEPRODUCTS(get_option("display_home_products_num")));
foreach ($postslist as $loopID => $post){ 

$link = get_permalink($post->ID);
		
	$content .= '<div class="Row460">
			<div class="Rowa">
				<a href="'.$link.'">
					<img src="'.$BADGET4.'" alt="item" />
				</a>
			</div>
			
			<div class="Rowb">
				<b><a href="'.$link.'">
					'.substr($post->post_title,0,55).'
				</a></b><br />			
			'. substr(strip_tags($post->post_excerpt),0,130).'...
			</div>
			
			<div class="Rowc">
				<div class="Rowc1">
				<a href="'.$link.'">
					'.premiumpress_image($post->ID,"",array('alt' => $post->post_title, 'width' => '90', 'height' => '95', 'style' => 'auto' )).'
				</a>
				</div>
			</div>
		<div style="clear:both;"></div>
		</div>';

				
		 $i++;
} 
print $content;

?>
</div>
<?php } ?>

 



<?php if(function_exists('dynamic_sidebar')){  dynamic_sidebar('Home Page Widget Box');   } ?>
 

<?php get_footer(); ?>