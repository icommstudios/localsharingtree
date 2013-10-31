<?php 

	// Set the sidebars to hide
	$GLOBALS['nosidebar'] = 1; $GLOBALS['nosidebar-left']=1;
	
	// Load the page header
	get_header(); 

	// Load in home page layout
	$childtheme = get_option("theme-style");
	if($childtheme == "_SimpleShopper.css" || $childtheme == "_Download-Shopper.css"){ 
	
	
	if(get_option("PPT_slider") =="s2"){  $GLOBALS['s2'] =1;echo $PPTDesign->SLIDER(2);   }  ?>
	 
	 <?php if(strlen(do_shortcode(get_option("welcome_text"))) > 1){ echo '<div class="clearfix"></div><br />'.nl2br(stripslashes(do_shortcode(get_option("welcome_text")))).'<div class="clearfix"></div>'; }?>
	
	<?php if(get_option("display_home_products") =="yes"){ ?>
	 
	<ul class="display thumb_view"> <?php $GLOBALS['limitSearch'] = get_option('display_home_products_num');  $GLOBALS['galleryblockstop']=4;  echo $ThemeDesign->GALLERYBLOCK(); ?></ul> <div class="clearfix"></div>  
	
	<?php } 
 

	}else{ ?>

 
    <div class="homepad"> 
    
        <div class="ct1a">	
            
            <?php 
            
            /*---------------------------- HOME PAGE SLDIER ------------------------------------- */ 
            
            if(get_option("PPT_slider") =="s2"){  
            
            $GLOBALS['s2'] =1;echo $PPTDesign->SLIDER(2); 
            
            
            }else{  
            
            
            /*---------------------------- FEATURED IMAGE ------------------------------------- */ 
        
            
             ?>
            
                <a href="<?php echo get_option("home_link_1"); ?>" title="<?php echo get_option("home_title_1"); ?>">
                <img src="<?php if(strlen(get_option("home_image_1")) > 1){ echo premiumpress_image_check(get_option("home_image_1"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge1.gif<?php } ?>" 
                alt="<?php echo get_option("home_title_1");  ?>" /></a>
            
            <?php } ?>
            
        </div>
            
            
        <div class="ct1b">	
            
            <a href="<?php echo get_option("home_link_2"); ?>" title="<?php echo get_option("home_title_2"); ?>"><img src="<?php if(strlen(get_option("home_image_2")) > 1){ echo premiumpress_image_check(get_option("home_image_2"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge2.gif<?php } ?>" class="imageBorder" alt="<?php echo get_option("home_title_2"); ?>" /></a>
        
            <a href="<?php echo get_option("home_link_3"); ?>" title="<?php echo get_option("home_title_3"); ?>"><img src="<?php if(strlen(get_option("home_image_3")) > 1){ echo premiumpress_image_check(get_option("home_image_3"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge3.gif<?php } ?>" class="imageBorder" style="margin-top:19px;" alt="<?php echo get_option("home_title_3"); ?>" /></a>
            
            <a href="<?php echo get_option("home_link_4"); ?>" title="<?php echo get_option("home_title_4"); ?>"><img src="<?php if(strlen(get_option("home_image_4")) > 1){ echo premiumpress_image_check(get_option("home_image_4"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge4.gif<?php } ?>" class="imageBorder" style="margin-top:19px;" alt="<?php echo get_option("home_title_4"); ?>" /></a>
            
        </div>
        
        <div class="clearfix"></div>
        
         
        <a href="<?php echo get_option("home_link_5"); ?>" title="<?php echo get_option("home_title_5"); ?>">
        <img src="<?php if(strlen(get_option("home_image_5")) > 1){ echo premiumpress_image_check(get_option("home_image_5"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge5.gif<?php } ?>" alt="<?php echo get_option("home_title_5"); ?>" /></a>
    
     <?php if(strlen(do_shortcode(get_option("welcome_text"))) > 1){ echo '<div class="clearfix"></div><br />'.nl2br(stripslashes(do_shortcode(get_option("welcome_text")))).'<div class="clearfix"></div>'; }?>
    
    </div>
    
    
    <?php
    
    
    
    
    ?>
    <?php if(get_option("display_home_products") =="yes"){ ?>
    
    <div class="RowWrapper">
    <?php
    
    if(strlen(get_option("home_image_6")) > 1){ $badge6icon =  premiumpress_image_check(get_option("home_image_6"),"full"); }else{ $badge6icon =  PPT_CUSTOM_STYLE_URL.'badge6.gif';} 
    
    $content = "";
    $postslist = query_posts($ThemeDesign->HOMEPRODUCTS(get_option("display_home_products_num")));
    foreach ($postslist as $loopID => $post){ 
    
    $link = get_permalink($post->ID);
            
        $content .= '<div class="Row460">
                <div class="Rowa">
                    <a href="'.$link.'">
                        <img src="'. $badge6icon.'" alt="hot item" />
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
    <?php 
    
    } // end row 

}

	// Load in widgets
	if(function_exists('dynamic_sidebar')){  dynamic_sidebar('Home Page Widget Box');   } 

	// Load in footer
	get_footer();

?>