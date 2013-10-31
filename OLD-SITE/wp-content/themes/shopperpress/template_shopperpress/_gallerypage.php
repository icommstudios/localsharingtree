<?php get_header();  ?>  

<div id="AJAXRESULTS"></div><!-- AJAX RESULTS -do not delete- -->

<div class="itembox">

 	<?php
	// CATEGORY ICON 
	if(strlen($GLOBALS['premiumpress']['catIcon']) > 1){ 
	echo "<img src='".premiumpress_image_check($GLOBALS['premiumpress']['catIcon'])."' class='galcaticon'>"; 
	}
	// -- end category icon
	?>
    
	<h1 class="title"><?php if(isset($_GET['s'])){ echo $PPT->_e(array('button','11')).": ".strip_tags($_GET['s']); }
	elseif( isset($_GET['search-class'])) {  echo $PPT->_e(array('button','11')).": ".strip_tags($_GET['cs-all-0']); }else{ echo $GLOBALS['premiumpress']['catName']; } ?>
    </h1>
    
    <div class="itemboxinner" style="padding-top:0px;">
      
    <p class="pageresults"><?php echo str_replace("%a",$GLOBALS['query_total_num'],$PPT->_e(array('gallerypage','1'))); ?></p>
    
    <!-- end results -->  
      
      
    <!-- start top buttons -->
    <?php if(get_option("display_gallery_saveoptions") != "no"){ ?> 
     
    <div class="marginTop">
      
          <a class="iconvss right" href="javascript:PPTGetSaveSearch('<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','AJAXRESULTS');" rel="nofollow">
          <?php echo $PPT->_e(array('gallerypage','3')); ?></a> 
    
        <a href="javascript:PPTSaveSearch('<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','<?php echo str_replace("http://","",curPageURL()); ?>','AJAXRESULTS');" class="iconss right" rel="nofollow">
          <?php echo $PPT->_e(array('gallerypage','2')); ?></a> 
          
        <?php if(get_option("display_wishlist") =="yes" && !isset($GLOBALS['flag-home']) ){ ?>
        
        <a href="<?php echo $GLOBALS['bloginfo_url']; ?>/?s=&pptfavs=yes" class="floatr iconfavs"><?php echo $PPT->_e(array('myaccount','35')); ?></a>
        
        <a href="<?php echo $GLOBALS['bloginfo_url']; ?>/?s=&pptfavs=compare" class="floatr iconcompare"><?php echo $PPT->_e(array('myaccount','39')); ?></a>
      	
     	<?php } ?> 
    
    </div>
    <?php } ?>
    
    <div class="clearfix"></div>
    
    <!-- end top buttons -->
     
    <div class="gray_box">
    <div class="gray_box_content">
    
    
    <div class="galbit1">  
      
        <span  class="left">
        
            <?php if($GLOBALS['query_total_num'] > 0 && !isset($GLOBALS['setflag_article']) && !isset($GLOBALS['tag_search']) && !isset($GLOBALS['setflag_faq']) ){  echo $ThemeDesign->OrderBy(); } ?>
        
        </span>
        
        <div class="galbit1a left">
        
         <?php echo $PPTDesign->PageNavigation(true,true); ?>
        
        </div>
     
    </div>
    <!-- end galbit1 --> 
    
    <?php if($GLOBALS['query_total_num'] != 0 && isset($post->post_type) && $post->post_type == "post"){  ?>
    <div class="galbit2">
    
        <span class="left"><?php echo $PPT->_e(array('gallerypage','10')); ?>:</span><a href="#" class="switch_thumb">&nbsp;&nbsp;</a>
        
    </div>
    <?php } ?>
    <!-- end galbit 2 -->
    
    <div class="clearfix"></div>
    </div>
    </div>
    
    <!-- end gray box --> 
	<?php 
       
     /* CUSTOM CATEGORY DESCRIPTION */
       
     if(isset($GLOBALS['catText']) && strlen($GLOBALS['catText']) > 1){   echo $GLOBALS['catText'];  }
       
     /* END CUSTOM CAT DESCRIPTION */
       
    ?>      
     
    <?php /*------------------------- sub CATEGORIES BLOCK ----------------------------*/ ?>   
    
    <?php if($GLOBALS['query_total_num'] != 0 && isset($GLOBALS['premiumpress']['catID']) && is_numeric($GLOBALS['premiumpress']['catID']) && get_option("display_sub_categories") =="yes" ){ 
	$STRING = $PPTDesign->HomeCategories();
	if(strlen($STRING) > 5){
	?>
    
 	<div class="green_box">
    	<div class="green_box_content nopadding" id="subcategories">        
        	<?php echo $STRING; ?>        
        <div class="clearfix"></div>
        </div>    
 	</div>
        
    <?php } } ?>    
      
    <?php /*------------------------- DISPLAY GALLERY BLOCK ----------------------------*/ ?>
    <?php if($GLOBALS['query_total_num'] != 0){ ?>
    
    <div id="SearchContent"> <div class="clearfix"></div>
    
    <ul class="display <?php if(!isset($GLOBALS['setflag_article'])){  if(get_option('display_liststyle') == "gal"){ echo "thumb_view"; }   }  ?>"> 
 
	<?php $GLOBALS['galleryblockstop'] = 3; echo $PPTDesign->GALLERYBLOCK(); ?>
    
    </ul>
    
    <div class="clearfix"> </div>
    
    </div>
    
    <?php } ?>
    
    
    <?php /*------------------------- PAGE NAVIGATION BLOCK ----------------------------*/ ?>   
 
    <div class="clearfix"> </div>
    
	<?php if($GLOBALS['query_total_num'] > 0){ ?>
     
	<ul class="pagination paginationD paginationD10"><?php echo $PPTDesign->PageNavigation(); ?></ul>
  
 	<?php }else{ ?>
    
 	<div class="yellow_box">
    	<div class="yellow_box_content">        
        	<div align="center"><img src="<?php echo get_template_directory_uri(); ?>/PPT/img/exclamation.png" align="absmiddle" alt="nr" />  <?php echo $PPT->_e(array('gallerypage','11')); ?> </div>       
        <div class="clearfix"></div>
        </div>    
 	</div>    
    
    <?php } ?>
 
	<div class="clearfix"> </div><br /> 
    
 
</div></div>

  
 

<?php get_footer(); ?>