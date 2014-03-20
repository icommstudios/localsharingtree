<div class="calp-event-popup">

    <div class="calp-bg-topl">
        <div class="calp-bg-topr">
            <div class="calp-bg-top"></div>
        </div>
    </div>
    <div class="calp-bgl">
        <div class="calp-bgr">
    <div class="calp-event-summary">
        <div class="calp-left-arrow"></div>
        <div class="calp-right-arrow"></div>
        
        <div class="calp-tooltip-header">
        <span class="calp-close-popup"></span>
        
        <?php if( $event->post->post_title ): ?>
            <div class="calp-event-title">
                <?php if( $event->category_colors ): ?>
                    <div class="calp-category-colors popop-colors"><?php echo $event->category_colors ?></div>
                <?php endif; ?>
                
                    <div class="calp-tooltip-event-title"><a href="<?php echo $share_url;?>"><?php echo esc_html( apply_filters( 'the_title', $event->post->post_title ) ) ?></a></div>
                    <div class="calp-event-address"><?php echo $event->address; ?>
                        <br /><a <?php if ( ($event->show_map == 0) || (false == $event->googleMap) ) echo 'style="display:none;"' ?> class="calp-map-toggle" mid="<?php echo $event->post_id;?>"><?php echo _e('Show map');?></a>
                    </div>
                    </div>
                    
        <?php endif ?>
        </div>
        <?php if ( $event->show_map ): ?>
        <div class="calp-map-container" id="map<?php echo $event->post_id; ?>" style="display: none">
            <a target="_blank" class="calp-glink" href="http://www.google.com/maps?f=q&amp;hl=en&amp;source=embed<?php echo $event->googleMap; ?>"></a>
            <?php if ( false !== $event->googleMap ): ?>
            <iframe class="calp-google-frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;aq=0&amp;output=embed&amp;ie=UTF8&amp;hq=&amp;t=m&amp;z=13&amp;iwloc=A<?php echo $event->googleMap; ?>"></iframe>
            <?php else: ?>
            <p><?php echo _e('Address Not Available', CALP_PLUGIN_NAME);?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="calp-tooltip-contents">
            <?php if ( !empty($event->venue )): ?>
            <div class="calp-event-info-block">
                <div class="calp-ei-label"><?php echo _e('Venue', CALP_PLUGIN_NAME);?></div>
                <div class="nt-detail"><?php echo esc_html( $event->venue ) ?></div>
            </div>
            <?php endif; ?>
            <div class="calp-event-info-block">
            <div class="calp-ei-label"><?php echo _e('When', CALP_PLUGIN_NAME);?></div>
                <div class="calp-ei-data">
                    <?php if( $event->allday ): ?>
                        <?php echo _e( ' (all-day) ', CALP_PLUGIN_NAME );?>
                        <?php echo esc_html( $event->short_start_date ); ?>
                        <?php if( $event->short_end_date != $event->short_start_date ): ?>
                             <br /><?php echo _e( 'to', CALP_PLUGIN_NAME );?>
                             <?php echo esc_html( $event->short_end_date ) ?>
                        <?php endif ?>
                    <?php else: ?>
                        <?php echo esc_html( $event->short_start_date.' '.$event->short_start_time ); ?>
                        <br /><?php echo _e( 'to', CALP_PLUGIN_NAME );?>
                        <?php echo esc_html( $event->short_end_date.' '.$event->short_end_time ); ?>
                    <?php endif ?>
                </div>
            </div>
            
            <?php if( $event->post->post_content ): ?>
            <div class="calp-event-info-block">
                <div class="calp-ei-label"><?php _e('Notes', CALP_PLUGIN_NAME);?></div>
                <div class="nt-detail">
                    <?php
                        $notes = strip_tags( str_replace('[gallery]', '', $event->post->post_content) );
                        if ( strlen( $notes ) < 450 ) {
                            echo nl2br($notes);
                        } else {
                            $string = implode(array_slice(explode('<br>',wordwrap($notes, 450,'<br>',false)),0,1));
                            echo nl2br($string);
                        } ?>
                        <br /><a class="calp-popup-read-more" href="<?php echo $share_url ?>"><?php _e('Read more', CALP_PLUGIN_NAME);?>..</a>
                </div>
            </div>
            <?php endif; ?>
        </div> 
        <?php if ( $event->show_map ): ?>
            <span class="calp-open-map" style="display: none">
                <a target="_blank" href="http://www.google.com/maps?f=q&amp;hl=en&amp;source=embed<?php echo $event->googleMap; ?>"><?php _e('Open in Google Maps', CALP_PLUGIN_NAME);?></a>
            </span>
        <?php endif; ?>
        <div class="calp-tooltip-contents calp-no-border">
                        
            <div class="calp-share">
                <span id="calp-plusone" class="calp-google"><g:plusone size="small"  href="<?php echo $share_url; ?>"></g:plusone></span>
                <a class="calp-facebook" title="Share on Facebook" href="http://www.facebook.com/sharer.php?s=100&p[url]=<?php echo urlencode($share_url);?>" target="_blank"></a>
                <a class="calp-twitter" title="Share on Twitter" href="http://twitter.com/home?status=<?php echo urlencode($event->post->post_title." ".$share_url);?>" target="_blank"></a>
                <a class="calp-linkedin" title="Share on LinkedIn" href="https://www.linkedin.com/cws/share?url=<?php echo urlencode($share_url);?>" target="_blank"></a>        
            </div>
        </div>
    </div>
        </div>
    </div>
    <div class="calp-bg-bottoml">
        <div class="calp-bg-bottomr">
            <div class="calp-bg-bottom"></div>
        </div>
    </div>
</div><!-- .event-popup -->
<script type="text/javascript">
    gapi.plusone.go('calp-plusone');
</script>
