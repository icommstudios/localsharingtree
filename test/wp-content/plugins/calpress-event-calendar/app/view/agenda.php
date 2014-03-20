<div id="calp-event-list">
    <div class="calp-bg-topl">
        <div class="calp-bg-topr">
            <div class="calp-bg-top"></div>
        </div>
    </div>
    <div class="calp-bgl">
        <div class="calp-bgr">
        <div class="calp-bg">
        <div class="calp-list-header"><?php _e($title, CALP_PLUGIN_NAME);?></div>
        <div id="calp-list-list" class="calp-list">
            <?php if( $empty_days ): ?>
                <div class="calp-no-wrapper"><div class="calp-no-events"><?php _e('No Events', CALP_PLUGIN_NAME);?></div></div>
            <?php else: ?>
            
            <?php foreach( $cell_array as $week ): ?>
            <?php foreach( $week as $day ): ?>
                <?php if( !empty($day['events']) ): ?>
                    <div class="calp-heading">
                        <div class="calp-left"><?php echo date_i18n("l", $day['timestamp']);?></div>
                        <div class="calp-right"><?php echo date_i18n("F", $day['timestamp']);?> <?php echo date_i18n("j", $day['timestamp']);?></div>
                    </div>
                    <?php foreach($day['events'] as $event) : ?>

                        <div class="calp-item
                         <?php if (  $event->current && empty($current_post) ) { $current_post = $event; echo 'calp-agenda-selected'; } ?>
                        " scroll_id="<?php echo $event->instance_id;?>">
                        <?php if( $event->category_colors ): ?>
                            <div class="calp-category-colors popop-colors"><?php echo $event->category_colors ?></div>
                        <?php endif; ?>
                            <div class="calp-list-info">
                                <div class="calp-list-title"><?php echo $event->post->post_title;?></div>
                                <?php if ( $event->address ): ?>
                                    <div class="calp-list-location"><?php echo $event->address; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="calp-list-date-double">
                                <?php if( !$event->allday ): ?>
                                    <?php echo esc_html( $event->short_start_date .' '.$event->short_start_time ); ?><br />
                                    <?php _e('to', CALP_PLUGIN_NAME);?>  <?php echo esc_html($event->short_end_date .' '.$event->short_end_time ) ?>
                                <?php else: ?>
                                    <?php _e('all-day', CALP_PLUGIN_NAME);?>
                                <?php endif; ?>
                          </div>
                        </div>
                    <?php endforeach;?>
                <?php endif ;?>
              <?php endforeach;?>
              <?php endforeach;?>

            <?php endif ;?>
        </div>
        </div>
        </div>
    </div>
    <div class="calp-bg-bottoml">
        <div class="calp-bg-bottomr">
            <div class="calp-bg-bottom"></div>
        </div>
    </div>
</div>

<div id="calp-list-box">
<div class="calp-list-container-lb">
<div class="calp-list-container-rb">
<div class="calp-list-container-bb">
<div class="calp-list-container-blc">
<div class="calp-list-container-brc">
<div class="calp-list-container-tb">
<div class="calp-list-container-tlc">
<div class="calp-list-container-trc">
    <div id="calp-event-single" style="position:relative;">
        <?php if( $empty_days ): ?>
        <div class="calp-no-events"><div style="padding-top: 230px"><?php _e('No Events', CALP_PLUGIN_NAME);?></div></div>
        <?php elseif( !isset($current_post) ): ?>
        <div class="calp-no-events"><div style="padding-top: 230px"><?php _e('No Upcoming Events', CALP_PLUGIN_NAME);?></div></div>
        <?php else: 
            require 'agenda_list.php';
         endif ;?>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<script type="text/javascript">
    // Pagination
    jQuery('#calp-navigator-prev').unbind();
    jQuery('#calp-navigator-prev').bind('click', function() {
        navigation_prev('<?php echo $pagination_links['middle']['2']['href'];?>');
    });
    jQuery('#calp-navigator-next').unbind();
    jQuery('#calp-navigator-next').bind('click', function() {
        navigation_next('<?php echo $pagination_links['middle']['4']['href'];?>');
    });
    var paginator = '<div class="calp-footer-bg calp-left-nav">';
        paginator += '<span id="calp-quick-prev"><a id="<?php echo $pagination_links['previous']['0']['id'] ?>" class="<?php echo $pagination_links['previous']['0']['class'] ?>" href="<?php echo esc_attr( $pagination_links['previous']['0']['href'] ) ?>">';
        paginator +=  '<?php echo esc_html( $pagination_links['previous']['0']['text'] ) ?>';
        paginator += '</a></span>';
    paginator += '<span class="calp-footer-dates-separator"></span>';
        paginator += '<span id="calp-nav-label"><a id="current-date-item" class="<?php echo $pagination_links['previous']['1']['class'] ?>" >';
        paginator +=  '<?php echo esc_html( $pagination_links['previous']['1']['text'] ) ?>';
        paginator += '</a></span>';
    paginator += '</div>';
    paginator += '<div id="calp-navigator-array" class="calp-footer-bg">';
    <?php foreach ($pagination_links['middle'] as $link ) : ?>
        paginator += '<span><a id="<?php echo $link['id'] ?>" class="<?php echo $link['class'] ?>" href="<?php echo esc_attr( $link['href'] ) ?>">';
        paginator +=  '<?php echo esc_html( $link['text'] ) ?>';
        paginator += '</a></span>';
    <?php endforeach ; ?>
    paginator += '</div>';
    paginator += '<div class="calp-footer-bg calp-right-nav">';
    paginator += '<span class="calp-footer-dates-separator"></span>';
        paginator += '<span id="calp-quick-next"><a class="<?php echo $pagination_links['next']['0']['class'] ?>" href="<?php echo esc_attr( $pagination_links['next']['0']['href'] ) ?>">';
        paginator +=  '<?php echo esc_html( $pagination_links['next']['0']['text'] ) ?>';
        paginator += '</a></span>';
    paginator += '</div>';
    jQuery('#calp-paginator-place').html(paginator);
    jQuery('#calp-today').attr('href', '#action=calp_agenda');
    
    // scrollbar and agenda list
    jQuery(document).ready(function(){
        jQuery('#calp-list-list').tinyscrollbar();
        jQuery('#calp-list-list .calp-item').die();
      jQuery('#calp-list-list .calp-item').live('click', function(event) {
        var e = jQuery(this);
        go_to_event(e.attr('scroll_id'));
        // remove selected class
        jQuery('#calp-list-list .calp-item').each( function() {
            jQuery(this).removeClass('calp-agenda-selected');
        });
        e.addClass('calp-agenda-selected');
        // scroll to current element with 165px skip space
        jQuery('#calp-list-list .calp-scroll-viewport').scrollTo('.calp-agenda-selected', 500, {offset: {top: -165 }});
      });
    });
</script>
