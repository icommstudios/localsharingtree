<div id="calp-single-top">
<div class="calp-header">
  <?php if( $current_post->category_colors ): ?>
    <div class="calp-category-colors popop-colors"><?php echo $current_post->category_colors ?></div>
  <?php endif; ?>
  <div class="calp-event-info">
    <div class="calp-title"><?php echo $current_post->post->post_title;?></div>
    
    <div class="calp-location">
      <?php if ( $current_post->venue ): ?>
        <span class="calp-location-text"><?php echo $current_post->venue ?></span>
        <div class="calp-clear"></div>
      <?php endif ?>
    
      <?php if ( $current_post->address ): ?>
        <span class="calp-location-text"><?php echo $current_post->address;?></span>
        <span class="calp-map" style="position: relative;" onclick="javascript:window.open('http://maps.google.com/maps?z=15&amp;output=embed&amp;iwloc=near&amp;q=<?php echo $current_post->address;?>', '_blank')" title="Click here to see the address on Google maps">
          <span class="calp-map-s"></span><span class="calp-map-bg"><?php _e('Show on map', CALP_PLUGIN_NAME);?></span><span class="calp-map-e"></span>
        </span>
        <div class="calp-clear"></div>
      <?php endif; ?>
    </div>
        
      </div>

      <div class="calp-share">
        <span id="calp-agenda-plusone" class="calp-google"><g:plusone size="small"  href="<?php echo $current_url; ?>"></g:plusone></span>
        <a class="calp-facebook" title="Share on Facebook" href="http://www.facebook.com/sharer.php?s=100&p[url]=<?php echo urlencode($current_url);?>" target="_blank"></a>
        <a class="calp-twitter" title="Share on Twitter" href="http://twitter.com/home?status=<?php echo urlencode($current_post->post->post_title." ".$current_url);?>" target="_blank"></a>
        <a class="calp-linkedin" title="Share on LinkedIn" href="https://www.linkedin.com/cws/share?url=<?php echo urlencode($current_url);?>" target="_blank"></a>

      </div>
  
</div>
<div class="calp-hr"></div>
</div>


<div id="calp-single-contents">

  <div class="calp-subheader">
    
    <div class="calp-startend">
      <span class="calp-icon"></span>
      <span class="calp-time">
        <?php if( $current_post->allday ): ?>
          <?php echo esc_html( $current_post->short_start_date ); ?>
          <?php if( $current_post->short_end_date != $current_post->short_start_date ): ?>
                   <?php _e( 'to', CALP_PLUGIN_NAME );?>
                   <?php echo esc_html( $current_post->short_end_date ) ?>
          <?php endif ?>
              <?php echo _e( ' (all-day)', CALP_PLUGIN_NAME );?>
          <?php else: ?>
              <?php echo esc_html( $current_post->short_start_date.' '.$current_post->short_start_time ); ?>
              <?php _e( 'to', CALP_PLUGIN_NAME );?>
              <?php echo esc_html( $current_post->current_post.' '.$current_post->short_end_time ); ?>
        <?php endif ?>
      </span>
    </div>
    
      </div>
      
<?php if ( !empty($current_post->post->post_content )) : ?>
    <div class="calp-notes "><?php echo nl2br(strip_tags( $current_post->post->post_content ));?></div>
<?php endif;?>
    
</div>

<script type="text/javascript">    
    jQuery(document).ready(function(){
        // scroll to current element with 165px skip space
        jQuery('#calp-list-list .calp-scroll-viewport').scrollTo('.calp-agenda-selected', 500, {offset: {top: -165 }});
        jQuery('#calp-list-list').tinyscrollbar_update();
        gapi.plusone.go('calp-agenda-plusone');
    });
</script>

