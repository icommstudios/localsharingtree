<div class="calp-navigation-header">
<h2 class="calp-calendar-title"><?php echo esc_html( $title );  ?></h2>
</div>

<table class="calp-month-view">
	<tbody>
		<?php $i = 0; $j = 0; foreach( $cell_array as $week ):  ?>
			<tr class="calp-week">
				<?php $k = 0; foreach( $week as $day ): ?>
					<?php if( $day['date'] ): ?>
						<td <?php if( $day['today'] && $timestamp ) echo 'class="calp-today"' ?>>
							<div class="calp-day">
								<div class="calp-date <?php if( $day['today'] ) echo 'calp-cell-header' ?>">
                                <?php if( $day['today'] ) : ?>
                                    <span class="calp-today-label"><?php echo _e('Today', CALP_PLUGIN_NAME);?></span>
                                <?php endif; ?>
                                    <?php
                                        if ($i == 0) {
                                            echo $weekdays[$j] .' '. $day['date'];
                                            $j++;
                                        } else {
                                            echo $day['date'];
                                        }
                                    ?>
                                    
                                </div>
                                <?php
                                    $invisibleItem = false;
                                    for ($ev = 0; $ev < count($day['events']); $ev++) {
                                        $event = $day['events'][$ev];
                                        if ($ev >= 3) {
                                            $invisibleItem = true;
                                        }
                                        require ('item_monthly.php');
                                    }
                                    
                                    if (count($day['events']) > 3) {
                                        $cnt = count($day['events']) - 3;
                                        $offset = floor(($day['timestamp'] - time()) / 86400);
                                        echo '<a class="calp-mode-link" href="#action=calp_today&calp_today_offset='.$offset.'">'.$cnt.' more...</a>';
                                    }
                                ?>
							</div>
						</td>
					<?php else: ?>
						<td class="calp-empty"><div class="bor_div"></div></td>
					<?php endif // date ?>
				<?php $k++; endforeach // day ?>
			</tr>
		<?php $i++; endforeach // week ?>
	</tbody>
</table>

<!-- end month content -->

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#calp-navigator-prev').unbind();
        jQuery('#calp-navigator-prev').bind('click', function() {
            navigation_prev('<?php echo $pagination_links['middle']['2']['href'];?>');
        });
        jQuery('#calp-navigator-next').unbind();
        jQuery('#calp-navigator-next').bind('click', function() {
            navigation_next('<?php echo $pagination_links['middle']['4']['href'];?>');
        });
        var paginator = '<div class="calp-footer-bg calp-left-nav">';
            paginator += '<span id="calp-quick-prev"><a class="<?php echo $pagination_links['previous']['0']['class'] ?>" href="<?php echo esc_attr( $pagination_links['previous']['0']['href'] ) ?>">';
            paginator +=  '<?php echo esc_html( $pagination_links['previous']['0']['text'] ) ?>';
            paginator += '</a></span>';
        paginator += '<span class="calp-footer-dates-separator"></span>';
            paginator += '<span id="calp-nav-label"><a id="current-date-item" class=" <?php echo $pagination_links['previous']['1']['class'] ?>" >';
            paginator +=  '<?php echo esc_html( $pagination_links['previous']['1']['text'] ) ?>';
            paginator += '</a></span>';
        paginator += '</div>';
        paginator += '<div id="calp-navigator-array" class="calp-footer-bg">';
        <?php foreach ($pagination_links['middle'] as $link ) : ?>
            paginator += '<span><a id="<?php echo $link['id'] ?>" class=" <?php echo $link['class'] ?>" href="<?php echo esc_attr( $link['href'] ) ?>">';
            paginator +=  '<?php echo esc_html( $link['text'] ) ?>';
            paginator += '</a></span>';
        <?php endforeach ; ?>
        paginator += '</div>';
        paginator += '<div class="calp-footer-bg calp-right-nav">';
        paginator += '<span class="calp-footer-dates-separator"></span>';
            paginator += '<span id="calp-quick-next"><a class=" <?php echo $pagination_links['next']['0']['class'] ?>" href="<?php echo esc_attr( $pagination_links['next']['0']['href'] ) ?>">';
            paginator +=  '<?php echo esc_html( $pagination_links['next']['0']['text'] ) ?>';
            paginator += '</a></span>';
        paginator += '</div>';
        jQuery('#calp-paginator-place').html(paginator);
        jQuery('#calp-today').attr('href', '#action=calp_month');
    });
        
    jQuery('.calp-event .calp-event-title ')
        .die('click').live('click', function(e) {
            var id = jQuery(this).attr('popupid');
            jQuery(this).addClass('showed-popup');
            var x = e.pageX ;
            var y = e.pageY;
            show_item_popup( id, x, y );
    });
</script>

