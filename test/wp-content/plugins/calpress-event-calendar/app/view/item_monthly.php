<span class="<?php echo $invisibleItem ? 'calp-invisible-item': null ?> calp-event-id-<?php echo $event->post_id ?>">
    <a style="cursor: default;" class="calp-event-container
            calp-event-id-<?php echo $event->post_id ?>
            calp-event-instance-id-<?php echo $event->instance_id ?>
            <?php if( $event->allday ) echo 'calp-allday' ?>">

        <?php // Insert post ID for use by JavaScript filtering later ?>
        <input type="hidden" class="calp-post-id" value="<?php echo $event->post_id ?>" />

        <div class="calp-event <?php if( $event->post_id == $active_event ) echo 'calp-active-event' ?>">
            <span class="calp-category-color"><?php echo $event->category_colors ?></span>
            <span class="calp-event-title" popupid="<?php echo $event->instance_id ?>"><?php echo esc_html( apply_filters( 'the_title', $event->post->post_title ) ) ?></span>
        </div>                                       
    </a>
</span>
