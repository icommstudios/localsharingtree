<script type="text/javascript">
    jQuery(document).ready(function () {
    	jQuery(".imgLiquidFill").imgLiquid({fill: true});
        jQuery("a[rel^=\"prettyPhoto\"]").prettyPhoto
        ({
            animation_speed: <?php echo $lightbox_fade_in_time;?>, 
            slideshow: <?php echo $slide_interval * 1000; ?>, 
            autoplay_slideshow: <?php echo $autoplay;?>,
            opacity: 0.80, 
            show_title: false,
            allow_resize: true
        });
    });
</script>