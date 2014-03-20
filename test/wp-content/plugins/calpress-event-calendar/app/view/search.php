<?php foreach($events['current_events'] as $event) : ?>
    <div class="calp-list calp-property">
        <div class="calp-heading">
              <div class="calp-left"><?php echo date_i18n('l', $event->start);?></div>
              <div class="calp-right"><?php echo date_i18n('F', $event->start). ' '.date_i18n('d', $event->start) ;?></div>
        </div>
        
        <div class="calp-item calp-search-item" eventid="<?php echo $event->instance_id;?>">
              <?php if( $event->category_colors ): ?>
                <div class="calp-category-colors popop-colors"><?php echo $event->category_colors ?></div>
              <?php endif; ?>
              <div class="calp-bullet calp-cal-1"></div>
              <div class="calp-list-info">
                <div class="calp-list-title"><?php echo esc_html( apply_filters( 'the_title', $event->post->post_title ) ) ?></div>
                    <div class="calp-list-location"><?php echo esc_html( $event->post_excerpt ) ?></div>
                </div>
                <?php if ( date('Y-m-d', $event->start) == date('Y-m-d', $event->end) ) : ?>
                        <div class="calp-list-date"><?php echo $event->short_start_time.' to '.$event->short_end_time;?></div>
                <?php else :?>
                    <div class="calp-list-date-double">
                        <?php echo $event->short_start_date.' '.$event->short_start_time;?>
                        <br><?php echo _e( 'to', CALP_PLUGIN_NAME ) ?>
                        <?php echo $event->short_end_date.' '.$event->short_start_time;?>
                    </div>
                <?php endif;?>
        </div>
        
    </div>
<?php endforeach;?>
<?php if (!empty($events['older_events']) || !empty($events['upcoming_events']) ) : ?>
    <div class="calp-list calp-property calp-footer">
        <?php if (!empty($events['older_events']) && !empty($events['upcoming_events']) ) : ?>
            <?php echo _e( 'Another', CALP_PLUGIN_NAME ) ?> <a onclick="CALPSearch.close();" href="#action=calp_agenda&calp_search=<?php echo urlencode($search_text);?>&calp_older=1"><?php echo $events['older_events'];?>
            <?php echo _e( 'older', CALP_PLUGIN_NAME ) ?></a>
            &amp; <a onclick="CALPSearch.close();" href="#action=calp_agenda&calp_search=<?php echo urlencode($search_text);?>&calp_older=0"><?php echo $events['upcoming_events'];?> <?php echo _e( 'upcoming', CALP_PLUGIN_NAME ) ?></a>
            <?php echo _e( 'events', CALP_PLUGIN_NAME ) ?>
        <?php elseif (!empty($events['older_events']) && empty($events['upcoming_events']) ) :?>
            Another <a onclick="CALPSearch.close();" href="#action=calp_agenda&calp_search=<?php echo urlencode($search_text);?>&calp_older=1"><?php echo $events['older_events'];?> <?php echo _e( 'older', CALP_PLUGIN_NAME ) ?></a>
            <?php echo _e( 'events', CALP_PLUGIN_NAME ) ?>
        <?php elseif (empty($events['older_events']) && !empty($events['upcoming_events']) ) :?>
            Another <a onclick="CALPSearch.close();" href="#action=calp_agenda&calp_search=<?php echo urlencode($search_text);?>&calp_older=0"><?php echo $events['upcoming_events'];?> upcoming</a>
            <?php echo _e( 'events', CALP_PLUGIN_NAME ) ?>
        <?php endif;?>
    </div>
<?php endif;?>
<?php if (!empty($events['older_events']) || !empty($events['upcoming_events']) || !empty($events['current_events']) ) : ?>
<script type="text/javascript">
    jQuery('.calp-search-item').live('click', function(){
        CALPSearch.close();
        var current_item = jQuery(this).attr('eventid');
        document.location.hash = '#action=calp_agenda&calp_search=<?php echo urlencode($search_text);?>&calp_item_id='+current_item;
    });
</script>
<?php endif;?>
