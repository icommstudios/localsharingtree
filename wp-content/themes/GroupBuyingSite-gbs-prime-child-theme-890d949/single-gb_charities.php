<?php

get_header(); ?>

		<div id="single" class="container charity_single prime main clearfix">
			
			<div id="content" class="clearfix">

				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

					<?php get_template_part('inc/loop-single') ?>
				
				<?php endwhile; ?>
				
			
			</div>
			<div id="page_sidebar" class="sidebar clearfix">
				<?php dynamic_sidebar( 'charity-sidebar' ); ?>
			</div>
			
		</div>
		
<?php get_footer(); ?>