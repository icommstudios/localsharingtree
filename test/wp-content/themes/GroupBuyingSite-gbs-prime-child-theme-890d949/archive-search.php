<?php
get_header(); ?>

		<div id="archive" class="search_results container prime main clearfix">
        
            <div class="page_title"><!-- Begin #page_title -->
                <h1 class="gb_ff"><?php gb_e( 'Search Results for:' ); ?> <?php echo get_search_query(); ?></h1>
            </div><!-- End #page_title -->
			
			<div id="content" class="clearfix">
				
                <?php if ( ! have_posts() ) : ?>
                    
                    <div id="post-0" class="post no-search-results not-found">
                   
                        <div class="entry_content">
                            <p><?php gb_e( 'Apologies, your search returned no results.' ); ?></p>
                            <?php get_search_form(); ?>
                        </div>
                    </div>
                    
                <?php endif; ?>

                <?php 
				while ( have_posts() ) : the_post(); 
					$post_type_object = get_post_type_object( get_post_type() );
				?>
                
                        <div id="post-content-<?php the_ID() ?>" <?php post_class('post search_result content-excerpt background_alt blog_post clearfix'); ?>>
                
                            <div class="excerpt_wrap clearfix">
                                
                                <h2 class="contrast entry_title font_medium gb_ff"><a href="<?php the_permalink() ?>" title="Read <?php the_title() ?>"><?php the_title() ?></a><span class="alignright font_small search_result_type"><?php echo $post_type_object->labels->singular_name ?><?php //the_time('F j, Y') ?></span></h2>
                
                                <div class="the_content search_result_content the_excerpt clearfix">
                                    <?php //if (function_exists('the_post_thumbnail')) { the_post_thumbnail( array( 100, 150 ) ); } ?>
                                    <?php the_excerpt(); ?>
                                    
                                </div>
                                
                                <a href="<?php the_permalink() ?>" class="search_result_link button gb_ff alignright font_small" title="View <?php the_title() ?>"><?php gb_e('View'); ?> <?php echo $post_type_object->labels->singular_name ?></a>

                            </div>
                
                        </div>
                        
                <?php endwhile; ?>
                
                <?php global $wp_query; 
				if (  $wp_query->max_num_pages > 1 ) : ?>
					<?php wp_pagination($wp_query); ?>
				<?php endif; ?>
                
            
			</div>
			
			<div id="page_sidebar" class="sidebar clearfix">
				<?php dynamic_sidebar( 'page-sidebar' ); ?>
			</div>
			
		</div>
		
<?php get_footer(); ?>