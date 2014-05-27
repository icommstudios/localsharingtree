<?php

get_header(); ?>

		<div id="single" class="container charity_single prime main clearfix">
			
			<div id="content" class="clearfix">

				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

					<?php get_template_part('inc/loop-single') ?>
				
				<?php endwhile; ?>
				
			
			</div>
			<div id="page_sidebar" class="sidebar clearfix">
				<div class="button_reset_filters_wrap"><a href="<?php echo site_url('charities'); ?>" class="button font_small">← Return to Directory Home</a></div>
				<?php dynamic_sidebar( 'charity-sidebar' ); ?>
			</div>
			
		</div>
		
<?php get_footer(); ?>