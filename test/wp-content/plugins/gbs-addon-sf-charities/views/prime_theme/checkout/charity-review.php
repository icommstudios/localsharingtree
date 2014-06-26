<?php
$charity_name_link = '<a href="'.get_permalink($charity_id).'" target="_blank">'.get_the_title( $charity_id ).'</a>';
?>
<p class="contrast_light message"><strong><?php self::_e('Non Profit Contribution:'); ?></strong> <?php echo $donation_percentage.'% '; printf(self::__("of your purchase will be donated to <span class='charity-recipient'>%s</span>. Thank you!"), $charity_name_link ); ?></p>