<?php
get_header(); ?>

<div id="archive" class="gb_charities_archive container prime main clearfix">
            
    <div class="clearfix">
        <div class="page_title">
<h1 class="gb_ff"><?php gb_e('Local Sharing Tree Charities'); ?></h1>
</div>

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

<!--                         <div class="postmeta clearfix">
                                    <?php if ( comments_open() || '0' != get_comments_number() ) : ?>
                                        <div class="meta_container comments">
                                            <?php comments_popup_link( gb__( 'Leave a comment' ), gb__( '1 Comment' ), gb__( '% Comments' ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="meta_container">
                                        <?php the_category(', '); ?>
                                    </div>  
                        </div> -->

                    </div><!-- End .biz_listing -->
                        
                <?php endwhile; ?>
                
                <?php if (  $wp_query->max_num_pages > 1 ) : ?>
                    <?php wp_pagination(); ?>
                <?php endif; ?>

        </div> <!-- end content -->
</div> <!-- end archive -->

		
<?php get_footer(); 