<?php 
global $userdata;
$post = $GLOBALS['post']; 
$oldPrice = premiumpress_price(GetPrice(get_post_meta($post->ID, "old_price", true)),$_SESSION['currency_symbol'],$GLOBALS['premiumpress']['currency_position'],1); ;
$ribbontext = get_post_meta($post->ID, 'ribbon', true);
$price = get_post_meta($post->ID, "price", true);
 

if(strlen($ribbontext) > 1){ ?> <div class="rib"><?php echo $ribbontext; ?></div> <?php } ?>

<li class="<?php if($GLOBALS['counter'] == $GLOBALS['galleryblockstop']){ ?>last<?php $GLOBALS['counter']=0; }  ?> <?php if($featured == "yes"){ ?> featured<?php } ?>" id="post_id_<?php echo $post->ID; ?>">

    <div class="right cartops">  
    
    
        <div class="price">
        <?php if($price == "0" || $price == ""){   echo $PPT->_e(array('button','19')); }else{  echo premiumpress_price(GetPrice($price),$_SESSION['currency_symbol'],$GLOBALS['premiumpress']['currency_position'],1);  } ?>
        <?php if(strlen($oldPrice) > 0){ ?><br /><span class="price-tax">WAS <?php echo $oldPrice; ?></span><?php } ?>
        </div>
    
        <div class="cart">	
            <div class="buy-btn-list"><a href="<?php echo get_permalink($GLOBALS['post']->ID); ?>"><div  class="cart-button">add to cart</div></a></div>
         </div>  
         
<?php if(get_option("display_wishlist") =="yes" && !isset($GLOBALS['flag-home']) ){ ?>

	<?php if(isset($_GET['pptfavs']) ){ ?>
    
            <div class="del"><a href="javascript:void(0);" onclick="jQuery('#post_id_<?php echo $post->ID; ?>').hide();
             PPTDeleteWishlist('<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','<?php echo $GLOBALS['backupID']; ?>','deletealter<?php echo $type; ?>');"><?php echo $PPT->_e(array('button','3')); ?></a></div>
    
    <?php }else{ ?>
    
           <div class="compare"><a href="javascript:void(0);" <?php if($userdata->ID){ ?>onclick="UpdateWishlist(<?php echo $post->ID; ?>,'add','AJAXRESULTS','<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','wishlist');" 
            <?php }else{ ?>onclick="alert('<?php echo $PPT->_e(array('ajax','1')) ?>');"<?php } ?> rel="nofollow"><?php echo $PPT->_e(array('fav','1')) ?></a></div>
            
            <div class="wishlist"><a  href="javascript:void(0);" <?php if($userdata->ID){ ?>onclick="UpdateWishlist(<?php echo $post->ID; ?>,'add','AJAXRESULTS','<?php echo str_replace("http://","",PPT_THEME_URI); ?>/PPT/ajax/','compare');" 
            <?php }else{ ?>onclick="alert('<?php echo $PPT->_e(array('ajax','1')) ?>');"<?php } ?> rel="nofollow"><?php echo $PPT->_e(array('fav','2')) ?></a></div>
    
    <?php } ?>
        
<?php } ?>  
  
          
    </div>
    
    <div class="left infoops">
    
        <?php echo premiumpress_image($post->ID,"",array('alt' => $post->post_title,  'link' => true, 'link_class' => 'img-box', 'width' => '200', 'height' => '145', 'style' => 'auto' ));  ?> 
        
        <div class="txt">
        
        <h3><a href="<?php echo get_permalink($GLOBALS['post']->ID); ?>"><?php the_title(); ?></a></h3>
        
        <div class="excpt"><?php echo substr(strip_tags($post->post_excerpt),0,530); ?></div>
        
        <?php if(get_option("display_search_tags") =="yes" && !isset($GLOBALS['flag-home']) ){ the_tags( '<div class="smalltags">', '', '</div>'); } ?> 
        
        </div>
    
    </div>
 
</li><!-- end product <?php echo $post->ID; ?> display -->