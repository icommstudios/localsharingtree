<?php
get_header(); ?>

		<div id="archive" class="gb_charities_archive container prime main clearfix">
			
			<div id="content" class="clearfix">
				
                <?php if ( function_exists('custom_show_filter_letters') ) custom_show_filter_letters(); ?>
                
				<?php if ( ! have_posts() ) : ?>
                    
                    <div id="post-0" class="post error404 not-found">
                        <h1 class="entry_title"><?php gb_e( 'Not Found' ); ?></h1>
                        <div class="entry_content">
                            <p><?php gb_e( 'Apologies, nothing found.' ); ?></p>
                            <?php get_search_form(); ?>
                        </div>
                    </div>
                    
                <?php endif; ?>
               
                <?php while ( have_posts() ) : the_post(); ?>
                
                        <div id="post-content-<?php the_ID() ?>" <?php post_class('post content-excerpt background_alt blog_post clearfix'); ?>>
                
                            <div class="excerpt_wrap clearfix">
                                
                                <h2 class="contrast entry_title gb_ff"><a href="<?php the_permalink() ?>" title="Read <?php the_title() ?>"><?php the_title() ?></a><span class="postdate font_xx_small"><?php the_time('F j, Y') ?></span></h2>
                
                                <div class="the_content the_excerpt clearfix">
                                    <?php if (function_exists('the_post_thumbnail')) { the_post_thumbnail( array( 100, 150 ) ); } ?>
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="postmeta clearfix">
                                    <?php if ( comments_open() || '0' != get_comments_number() ) : ?>
                                        <div class="meta_container comments">
                                            <?php comments_popup_link( gb__( 'Leave a comment' ), gb__( '1 Comment' ), gb__( '% Comments' ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="meta_container">
                                        <?php the_category(', '); ?>
                                    </div>	
                                </div>
                
                            </div>
                
                        </div>
                        
                <?php endwhile; ?>
                
                <?php if (  $wp_query->max_num_pages > 1 ) : ?>
                    <?php wp_pagination(); ?>
                <?php endif; ?>
                
                
			</div>
			
			<div id="page_sidebar" class="sidebar clearfix">
				<?php dynamic_sidebar( 'charities-sidebar' ); ?>
			</div>
			
		</div>
		
<?php get_footer(); 