<?php
/*
Template Name: Current Deals - Random Order - (based on preferred location)
*/
session_start();
get_header(); ?>

		<div id="deals_loop" class="container prime main clearfix">
			
			<div id="page_sidebar" class="sidebar clearfix">
				<?php do_action('gb_above_default_sidebar') ?>
				<?php dynamic_sidebar( 'deals-sidebar' );?> 
				<?php do_action('gb_below_default_sidebar') ?>
			</div>

			<div id="content" class="clearfix">

				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="page_title"><!-- Begin #page_title -->
							<h1 class="entry_title gb_ff"><?php the_title(); ?></h1>
						</div><!-- End #page_title -->

						<div class="entry_content">
							<?php the_content(); ?>
							<?php wp_link_pages( array( 'before' => '<div class="page-link">' . gb__( 'Pages:' ), 'after' => '</div>' ) ); ?>
						</div><!-- .entry_content -->
					</div><!-- #post-## -->

				<?php endwhile;
                	
				$deal_query= null;
				$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
				$args=array(
					'post_type' => gb_get_deal_post_type(),
					'post_status' => 'publish',
					'paged' => $paged,
					'orderby' => 'rand',
					'meta_query' => array(
						array(
							'key' => '_expiration_date',
							'value' => array(0, current_time('timestamp')),
							'compare' => 'NOT BETWEEN'
						)),
					
				);
				// get prefered location if it's set
				if ( gb_has_location_preference() ) {
					$location = gb_get_preferred_location();
					$args = array_merge( array(gb_get_deal_location_tax() => $location), $args);
				}
				
				// Randomly order posts upon home page load and allow pagination
				add_filter('posts_orderby', 'custom_lst_edit_posts_orderby');
				/**
				 * Randomize posts, keeping same order on subsequent Home page and single post views
				 */
				function custom_lst_edit_posts_orderby($orderby_statement) {
					if(!is_admin()){
						
						if(!isset($_SESSION['seed'])) {
							//echo 'New Grid';
							$_SESSION['new_request'] = true;
						} else {
							// $refesh_flag will be true if $request_signatures match between page loads.
							$request_signature = md5($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
							// $interior_flag will be true if the last page URL contains the base url but is not the base url.
							$interior_flag = (bool)get_query_var('paged');
							// if we are staring page AND the page has been refreshed AND the last page was not an interior page,
							// unset the seed and flag as a new request so the grid will appear the same upon return.
							if($_SESSION['last_request'] == $request_signature && $interior_flag == false){
								//echo 'Reset to New Grid';
								unset($_SESSION['seed']);
								$_SESSION['new_request'] = true;
							} else {
								//echo 'Continuting Grid';
								$_SESSION['new_request'] = false;
								$_SESSION['last_request'] = $request_signature;
							}
						}
						$seed = $_SESSION['seed'];
						if (empty($seed)) {
							$seed = rand();
							$_SESSION['seed'] = $seed;
						}
						$orderby_statement = 'RAND('.$seed.')';
						
					}
					return $orderby_statement;
				}
					
				$deal_query = new WP_Query($args);
				
				// REMOVE - Randomly order post
				remove_filter('posts_orderby', 'custom_lst_edit_posts_orderby');
				?>
                
				<?php if ( ! $deal_query->have_posts() ) : ?>
                
					<?php get_template_part( 'deal/no-deals', 'deal/index' ); ?>
                
				<?php endif; ?>
                
				<?php $count; while ( $deal_query->have_posts() ) : $deal_query->the_post(); $count++; $zebra = ($count % 2) ? ' odd' : ' even'; ?>
                
					<?php get_template_part( 'inc/loop-item', 'inc/deal-item' ); ?>
                
				<?php endwhile; ?>
                
				<?php if (  $deal_query->max_num_pages > 1 ) : ?>
					<?php wp_pagination($deal_query); ?>
				<?php endif; ?>
                
				<?php wp_reset_query(); ?>

			</div><!-- #content_wrap -->

		</div><!-- #single_page -->

<?php
get_footer();
