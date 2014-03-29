<div class="biz_listing clearfix"><!-- Begin .biz_listing -->
	
	<div class="biz_wrapper clearfix">
	
        <div class="merchant_logo contrast"><!-- Begin .merchant-logo -->
            <a href="<?php the_permalink() ?>" title="<?php the_title() ?>">
                          <?php 
                          if (function_exists('the_post_thumbnail')) {
                            the_post_thumbnail('medium'); }
                          else {
                              echo '<img src="http://localsharingtree.com/wp-content/uploads/2014/03/no_image_found.jpg">';
                            }
                          ?>
            </a>
        </div><!-- End .merchant-logo -->
	
		<div class="biz_content contrast">
	
			<h2 class="gb_ff merchant-title"><a href="<?php the_permalink() ?>" title="<?php the_title() ?>"><?php the_title() ?></a></h2>	
		</div>
	
	</div>

</div><!-- End .biz_listing -->