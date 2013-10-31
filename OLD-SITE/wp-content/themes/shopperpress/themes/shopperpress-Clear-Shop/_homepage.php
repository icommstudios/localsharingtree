<?php $GLOBALS['nosidebar'] = 1; $GLOBALS['nosidebar-left']=1; get_header(); ?> 
 

<div class="hmail">


	<?php /*----------------MAIN IMAGE 1------------ */ ?>

    <div class="hmail1">
    
        <a href="<?php echo get_option("home_link_1"); ?>" title="<?php echo get_option("home_title_1"); ?>">
        <img src="<?php if(strlen(get_option("home_image_1")) > 1){ echo premiumpress_image_check(get_option("home_image_1"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge1.gif<?php } ?>" alt="<?php echo get_option("home_title_1");  ?>" /></a>
    
    
       <div class="clearfix"></div><br /> 
        
        <?php if(function_exists('dynamic_sidebar')){  dynamic_sidebar('Home Page Widget Box');   } ?>          
    
    </div>
    
    
	<?php /*----------------MAIN IMAGE 2 ------------ */ ?>
    
    <div class="hmail2">
    
        <?php 
        
        /*---------------------------- HOME PAGE SLDIER ------------------------------------- */ 
        
        if(get_option("PPT_slider") =="s2"){  
        
        $GLOBALS['s2'] =1;echo $PPTDesign->SLIDER(2); 
        
        
        }else{  
        
        
        /*---------------------------- FEATURED IMAGE ------------------------------------- */ 
    
        
         ?>
        
            <a href="<?php echo get_option("home_link_2"); ?>" title="<?php echo get_option("home_title_2"); ?>">
            <img src="<?php if(strlen(get_option("home_image_2")) > 1){ echo premiumpress_image_check(get_option("home_image_2"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge2.gif<?php } ?>" 
            alt="<?php echo get_option("home_title_2");  ?>" /></a>
        
        <?php } ?>  
  
   <?php if(strlen(do_shortcode(get_option("welcome_text"))) > 1){ echo '<div class="clearfix"></div><br />'.nl2br(stripslashes(do_shortcode(get_option("welcome_text")))).'<div class="clearfix"></div>'; }?>

   <div class="clearfix"></div>  
   
	<?php /*----------------MAIN IMAGE 3 ----------- */ ?>    
   
   <div class="hmail3">
   
    <a href="<?php echo get_option("home_link_3"); ?>" title="<?php echo get_option("home_title_3"); ?>">
    <img src="<?php if(strlen(get_option("home_image_3")) > 1){ echo premiumpress_image_check(get_option("home_image_3"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge3.gif<?php } ?>" alt="<?php echo get_option("home_title_3");  ?>" /></a>
   
   </div>
   
 	<?php /*---------------- 2 FEATURED PRODUCTS ------------ */ ?>  
   
      <div class="hmail4">
        
 <?php if(get_option("display_home_products") =="yes"){ ?>
 <ul class="display thumb_view"> <?php 
 
 // CHECK FOR CUSTOM DISPLAY PRODUCTS
 $cat = get_option('display_home_products_cat'); $ids = get_option('display_home_products_IDs');
 if($cat == "choose" && $ids != "" && strlen($ids) > 2 ){  
	$GLOBALS['query_string_new'] = array( 'post__in' => explode(",",$ids) );
 }
 
 $GLOBALS['limitSearch'] = 2; $GLOBALS['galleryblockstop']=2;  echo $ThemeDesign->GALLERYBLOCK(); ?></ul> <div class="clearfix"></div> 
 <?php wp_reset_query(); }  ?>
        
        
	<a href="<?php echo get_option("home_link_4"); ?>" title="<?php echo get_option("home_title_4"); ?>">
    <img src="<?php if(strlen(get_option("home_image_4")) > 1){ echo premiumpress_image_check(get_option("home_image_4"),"full"); }else{ echo PPT_CUSTOM_STYLE_URL; ?>badge4.gif<?php } ?>" alt="<?php echo get_option("home_title_4");  ?>" /></a>
   
   </div>
   
   
</div>

 
</div> 

<?php get_footer(); ?>