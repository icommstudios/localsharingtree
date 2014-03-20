<div class="wrap">

<h2><?php _e( 'CalPress Add-Ons', CALP_PLUGIN_NAME ) ?></h2>

<?php

if ( empty($addons) ) {
    _e( 'No addons available', CALP_PLUGIN_NAME );
} else {
    foreach ($addons as $addon) : ?>
        <div class="calp-add-on">
            <h2><?php echo $addon->title; ?></h2>
            <h3><?php echo $addon->alert ?></h3>
            <div class="calp-thumb">
                <a href="<?php echo $addon->url; ?>" target="_blank">
                    <?php echo $addon->image; ?>
                </a>
            </div>
            <div class="calp-addonbody">
                <p><?php echo $addon->content ?></p>
            </div>
                  
   			<br />
            <div class="calp-bottomlink">
                <h1>$<?php echo $addon->price; ?></h1>
                <?php 
                    $button = !empty($addon->button)?$addon->button:$addon->title;
                ?>
                <!--<button href="<?php echo $addon->url; ?>" target="_blank" class="button-primary"><?php echo $addon->button;?></button>-->
                <a href="<?php echo $addon->url; ?>" target="_blank"  class="button-primary"><?php echo $button; ?></a>
            </div>
      </div>
    <?php endforeach; 
}
?>
</div>
