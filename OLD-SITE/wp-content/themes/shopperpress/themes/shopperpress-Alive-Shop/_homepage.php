<?php $GLOBALS['nosidebar'] = 1; $GLOBALS['nosidebar-left']=1; get_header(); ?>

<div class="full clearfix padding10"> 

	<div class="f4 left" style="width:755px;"> 
    
       <?php 
        
        /*---------------------------- HOME PAGE SLDIER ------------------------------------- */ 
        
        if(get_option("PPT_slider") =="s2"){  $GLOBALS['s2'] =1;echo $PPTDesign->SLIDER(2);  }else{  
        
        
        /*---------------------------- FEATURED IMAGE ------------------------------------- */ 
    
        
         ?>
	              
        <a href="<?php echo get_option("home_link_1"); ?>" title="<?php echo get_option("home_title_1"); ?>">
        <img src="<?php if(strlen(get_option("home_image_1")) > 1){ echo premiumpress_image_check(get_option("home_image_1"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge1.gif<?php } ?>" alt="<?php echo get_option("home_title_1");  ?>" /></a> 
        
        <?php } ?>
        
        
        <?php if(strlen(do_shortcode(get_option("welcome_text"))) > 1){ echo '<div class="clearfix"></div><br />'.nl2br(stripslashes(do_shortcode(get_option("welcome_text")))).'<div class="clearfix"></div>'; }?>
	
    <?php if(get_option("display_home_products") =="yes"){ ?>
  <div id="newblock">
	<ul class="display thumb_view marginTop"> <?php $GLOBALS['query_string_new'] = $ThemeDesign->HOMEPRODUCTS(get_option("display_home_products_num"));  
	 
	$GLOBALS['galleryblockstop']=4; echo $ThemeDesign->GALLERYBLOCK(); ?></ul> <div class="clearfix"></div>  </div>
    <?php } ?>
                 
	</div> 
		

	<div class="f1 left" style="width:180px;"> 
           
                
                
<a href="<?php echo get_option("home_link_2"); ?>" title="<?php echo get_option("home_title_2"); ?>"><img src="<?php if(strlen(get_option("home_image_2")) > 1){ echo premiumpress_image_check(get_option("home_image_2"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge2.gif<?php } ?>" alt="<?php echo get_option("home_title_2");  ?>"></a>
                
                
 <a href="<?php echo get_option("home_link_3"); ?>" title="<?php echo get_option("home_title_3"); ?>"><img src="<?php if(strlen(get_option("home_image_3")) > 1){ echo premiumpress_image_check(get_option("home_image_3"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge3.gif<?php } ?>" alt="<?php echo get_option("home_title_3");  ?>" class="marginTop"></a>
          
          
         <div class="marginTop">

       
  
        
        <?php if(function_exists('dynamic_sidebar')){  dynamic_sidebar('Home Page Widget Box');   } ?> 
         
         </div> 
          
               
		</div> 
			
</div>        
        
<?php get_footer(); ?>