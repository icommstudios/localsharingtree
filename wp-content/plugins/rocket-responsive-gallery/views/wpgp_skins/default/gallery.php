<?php
/**
Plugin Name: Default Lightbox Skin
**/
?>

<div class="wpgp-gallery-default " id="gallery-<?php echo esc_attr( $gallery_id ) ?>" data-slider_id="<?php echo esc_attr( $gallery_id ) ?>" data-options="<?php echo esc_attr(json_encode( $gallery_settings )); ?>">

<?php foreach( $slides as $slide ):

      $uid = uniqid();
?>
  
  <div class="wpgpbox">
    <div class="wpgpboxInner">
      <a class="wpgp-open-lightbox" href="#<?php echo $uid ?>" title="<?php echo esc_attr($slide['alt']) ?>">
        <img class="wpgp-gallery-thumb-img" src="<?php echo self::get_image_thumb( $slide['url'], $size ) ?>" width="<?php echo $gallery_settings['width'] ?>" height="<?php echo $gallery_settings['height'] ?>" alt="<?php echo esc_attr( $slide['alt'] ) ?>" />
      </a>
    </div>
  </div>

  <?php if ( empty( $slide['html'] ) ): ?>
    <div class="mfp-hide" id="<?php echo $uid ?>">
      <div class="mfp-figure"><button title="Close (Esc)" type="button" class="mfp-close">×</button><figure><?php if ( empty( $slide['link'] ) ):  ?><img class="mfp-img" src="<?php echo $slide['url'] ?>" alt="<?php echo $slide['alt'] ?>"><?php else: ?><a title="<?php echo $slide['alt'] ?>" href="<?php echo $slide['link'] ?>"><img class="mfp-img" src="<?php echo $slide['url'] ?>" alt="<?php echo $slide['alt'] ?>"></a><?php endif; ?><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"><?php if ( ! empty( $slide['caption'] ) ) echo $slide['caption']; ?></div></div></figcaption></figure></div>
    </div>
  <?php else: ?>

  <?php endif; ?>

<?php endforeach; ?>
</div>

<?php do_action( 'wpgp_skin_end' ) ?>