<?php 
global $userdata;
$post = $GLOBALS['post']; 
$oldPrice = premiumpress_price(GetPrice(get_post_meta($post->ID, "old_price", true)),$_SESSION['currency_symbol'],$GLOBALS['premiumpress']['currency_position'],1);
$ribbontext = get_post_meta($post->ID, 'ribbon', true);
$price = get_post_meta($post->ID, "price", true);
 
?>

<li class="<?php if($GLOBALS['counter'] == $GLOBALS['galleryblockstop']){ ?>last<?php $GLOBALS['counter']=0; }  ?> <?php if($featured == "yes"){ ?> featured<?php } ?>" id="post_id_<?php echo $post->ID; ?>">

<?php if(strlen($ribbontext) > 1){ ?> <div class="rib"><?php echo $ribbontext; ?></div> <?php } ?>

<?php
// IMAGE DISPLAY // V7 
echo premiumpress_image($post->ID,"",array('alt' => $post->post_title,  'link' => true, 'link_class' => 'img-box', 'width' => '200', 'height' => '145', 'style' => 'auto' ));  
?> 
 
<h3 <?php if(strlen($ribbontext) > 1){ ?>class="ribh3"<?php } ?>><a href="<?php echo get_permalink($GLOBALS['post']->ID); ?>"><?php the_title(); ?></a></h3>


<div class="data-box"> 

<p><?php echo substr(strip_tags($post->post_excerpt),0,180); ?>..<a href="<?php echo get_permalink($GLOBALS['post']->ID); ?>"><?php echo $PPT->_e(array('button','13')) ?></a></p>

<?php if($post->post_type == "post"){ ?> 
<p>
<span  class="icon cat"><?php the_category(', ') ?></span>
<?php if($GLOBALS['shopperpress']['StockControl'] == "yes"){ ?>
<span class="icon stock"><?php if(get_post_meta($post->ID, "qty", true) > 0){ echo get_post_meta($post->ID, "qty", true)." ".$PPT->_e(array('sp','8')); }else{  echo $PPT->_e(array('sp','11')); } ?></span>
<?php }  ?>
</p>
<?php } ?> 


<?php if(get_option("display_search_tags") =="yes"){ the_tags( '', ', ', ''); } ?>    
 
        
</div>   
    
 
<?php if($GLOBALS['shopperpress']['price_tag'] == "no" || ($GLOBALS['shopperpress']['price_tag'] =="member" && $userdata->ID < 1 ) || $post->post_type != "post" ){   }else{ ?>
    
    
<div class="savebuttons"  class="left">

<?php if(get_option("display_wishlist") =="yes" && !isset($GLOBALS['flag-home']) ){ ?>


		<?php if(isset($_GET['pptfavs']) && $_GET['pptfavs'] == "compare" ){ ?>
        
         <a  class="link-wishlist" style="color:red; text-decoration:underline" href="javascript:void(0);" onclick="jQuery('#post_id_<?php echo $post->ID; ?>').hide();
         PPTDeleteWishlist('<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','<?php echo $GLOBALS['backupID']; ?>','AJAXRESULTS');">- <?php echo $PPT->_e(array('button','3')); ?></a>
        
        <?php }else{ ?>

 		<a class="link-comparelist"  href="javascript:void(0);" <?php if($userdata->ID){ ?>onclick="UpdateWishlist(<?php echo $post->ID; ?>,'add','AJAXRESULTS','<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','compare');" 
        <?php }else{ ?>onclick="alert('<?php echo $PPT->_e(array('ajax','1')) ?>');"<?php } ?> rel="nofollow">+ <?php echo $PPT->_e(array('fav','2')) ?></a>
      
        <?php  } ?>
        
        
		
		<?php if(isset($_GET['pptfavs']) && $_GET['pptfavs'] == "yes" ){ ?>
         
         <a  class="link-wishlist" style="color:red; text-decoration:underline" href="javascript:void(0);" onclick="jQuery('#post_id_<?php echo $post->ID; ?>').hide();
         PPTDeleteWishlist('<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','<?php echo $GLOBALS['backupID']; ?>','AJAXRESULTS');">- <?php echo $PPT->_e(array('button','3')); ?></a>
    
        <?php }else{ ?>
    
        <a class="link-wishlist"  href="javascript:void(0);" <?php if($userdata->ID){ ?>onclick="UpdateWishlist(<?php echo $post->ID; ?>,'add','AJAXRESULTS','<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','wishlist');" 
        <?php }else{ ?>onclick="alert('<?php echo $PPT->_e(array('ajax','1')) ?>');"<?php } ?> rel="nofollow">+ <?php echo $PPT->_e(array('fav','1')) ?></a>
              
        <?php } ?>


        
<?php } ?> 

</div>
    
    
<div class="actions">

    <a href="<?php echo get_permalink($GLOBALS['post']->ID); ?>"><span class="add-box"><span><?php echo $PPT->_e(array('sp','2')) ?></span></span></a>
    
    <div class="price-box">
    
    	<span class="price">
        
        <?php if(strlen($oldPrice) > 0){ ?><strike><?php echo $oldPrice; ?></strike><?php } ?>
        
        <?php if($price == "0" || $price == ""){ ?>
        
        <b><?php echo $PPT->_e(array('button','19')) ?></b>
        
        <?php }else{ ?>
        
        <b><?php echo premiumpress_price(GetPrice($price),$_SESSION['currency_symbol'],$GLOBALS['premiumpress']['currency_position'],1);  ?></b>
        
        <?php } ?>
                    	   
        </span>
        
    </div>
        
</div>

<?php } ?>
 
</li><!-- end product <?php echo $post->ID; ?> display -->