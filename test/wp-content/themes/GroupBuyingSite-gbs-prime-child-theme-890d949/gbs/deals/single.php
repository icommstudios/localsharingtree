<?php
	do_action('gb_deal_view');
	get_header(); ?>

	<div id="deal_single" class="container prime main clearfix">

		<div id="content" class="full clearfix">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div class="page_title business_page clearfix"><!-- Begin #page_title -->
						<h1 class="featured_tag">Featured Deal:</h1>
						<h1 class="gb_ff deal_title"><?php the_title() ?></h1>
						<div class="button_reset_filters_wrap"><a href="<?php echo site_url('moredeals'); ?>" class="button font_small">← Search More Deals</a></div>
					</div><!-- End #page_title -->
				<div id="main_deal_wrap" <?php post_class('clearfix'); ?>>

					<div class="deal_thumbnail">
						<?php if ( gb_has_featured_content() ) :?>
							<div class="featured_content">
								<?php gb_featured_content(); ?>
							</div>
							<?php elseif ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail('gbs_700x400'); ?>
						<?php else: ?>
							<div class="deal_thumb no_featured_image" style="background: url(<?php gb_header_logo(); ?>) no-repeat 50px center;"></div>
						<?php endif; ?>
					</div>

					<div class="purchase_options_meta clearfix">
						<div class="purchase_options">
							<?php if (function_exists('gb_deal_has_attributes') && gb_deal_has_attributes()): ?>
								<div id="deal_attributes_wrap" class="section ">
									<?php gb_add_to_cart_form() ?>
								</div>
							<?php else: ?>
								<div class="buy_button gb_ff font_x_large">
									<?php if ( gb_deal_availability() || !gb_is_deal_complete() ): ?>
										<a href="<?php gb_add_to_cart_url(); ?>" class="button"><?php gb_e('Buy it!'); ?> <span><?php echo str_replace('.00','',gb_get_formatted_money(gb_get_price()))  ?></span></a>
									<?php elseif ( gb_is_sold_out() ) : ?>
										<a class="button"><?php gb_e('Sold Out!') ?></a>
									<?php else : ?>
										<a class="button"><?php gb_e('It&rsquo;s over!') ?></a>
									<?php endif ?>
								</div>
							<?php endif ?>
						</div>

						<div class="deal_meta gb_ff background_alt clearfix">
							<div class="deal_meta_wrapper clearfix">
								<div class="meta_column">
									<span class="meta_title">
										<?php gb_e('Value') ?>
									</span>
									<div class="meta_value font_x_large">
										<?php echo str_replace('.00','',gb_get_formatted_money(gb_get_deal_worth())) ?>
									</div>
								</div>
								<div class="meta_column">
									<span class="meta_title">
										<?php gb_e('Savings') ?>
									</span>
									<div class="meta_value font_x_large">
										<?php echo str_replace('.00','',gb_get_formatted_money(gb_get_deal_worth() - gb_get_price())) ?>
									</div>
								</div>
							</div>

							<div id="deal_locations">
								<?php gb_deal_voucher_locations(); ?>
							</div>

							<?php if ( gb_deal_availability() && gb_has_expiration() ): ?>
								<div id="deal_countdown" class="clearfix">
									<?php gb_deal_countdown(); ?>
									<noscript>
										<?php gb_get_deal_end_date(); ?>
									</noscript>
								</div><!-- #label -->
							<?php endif ?>

							<div class="deal_sold clearfix gb_fx">
								<?php
									if ( gb_is_deal_complete()  ) {
										?>
										<div class="progress_msg button gb_ff font_medium">
											<?php if ( gb_is_sold_out() ) : ?>
												<?php printf(gb__('Sorry! This deal reached the maximum amount of %s buyers.'), gb_get_max_purchases() ); ?>
											<?php elseif ( !gb_deal_availability() && gb_has_expiration() ) : ?>
												<?php printf(gb__('Bummer! You just missed this deal!'), gb_get_max_purchases() ); ?>
											<?php else : ?>
												<?php printf(gb__('This deal failed to reach the minimum %s amount of buyers.'), gb_get_min_purchases()); ?>
											<?php endif; ?>
										</div><!-- .progress-msg -->
										<?php
									}
								?>
							</div><!-- #deal-sold -->

							<?php
								if ( gb_get_min_purchases() ) {
									$percentage = ( gb_get_number_of_purchases() / gb_get_min_purchases() ) * 100;
									$remaining =  gb_get_min_purchases()-gb_get_number_of_purchases();
									$remaining_message = ( (int)$remaining > 0 ) ? sprintf(gb__('%s more to tip.'), $remaining) : gb__('Tipped!') ;
									if ( gb_get_number_of_purchases() == 0 ) $percentage = '5';
									?>
									<div class="purchase_info clearfix">
										<div id="progress_bar" class="gb_ff percentage_<?php echo $percentage ?> clearfix">
											<div class="progress"></div>
											<span class="progress_bar_wrap gb_ff contrast_light amount_bought bold font_medium"><span class="progress_bar_progress contrast" style="width:<?php echo $percentage ?>%;"><?php printf(gb__('%s bought.'),gb_get_number_of_purchases()) ?></span><span class="remaining_tip"><?php echo $remaining_message ?></span></span>
										</div><!-- #progress_bar.name -->
									</div><!-- // .purchase_info-->
									<?php
								}
							?>
						</div>
						<div class="gb_ff social_share">
						<h4>Get Rewards by Sharing Deals!</h4>
						<?php get_template_part('inc/social-share') ?>
						<hr>
						<small><img class="info-icon" width="20" height="20" src="<?php echo get_stylesheet_directory_uri(); ?>/img/info-icon.png">
							<a href="<?php echo home_url(); ?>/share-and-earn/">Find out how to share and earn here.</a></small>
						</div>
					</div> <!-- .purchase_options_meta -->

				</div><!-- .main_deal_wrap -->

				<?php if (gb_has_dynamic_price()): ?>
					<div id="milestone_wrap" class="background_alt gb_ff clearfix">
						<?php gb_dynamic_prices() ?>
					</div><!-- #.milestone_wrap -->
				<?php endif ?>

				<div <?php post_class('deal_details clearfix'); ?>>
					<div class="description section clearfix">
						<div class="section_title clearfix">
							<h4 class="font_large gb_ff"><?php gb_e('About This Deal') ?></h4>
							<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
						</div>
						<div class="section_content">
							<?php the_content(); ?>
						</div>
					</div>

					<?php if ( gb_has_merchant() ): ?>
                    
                    	<?php 
						//Do we have any merchant deals
						ob_start();
						$widget_instance['title'] = strip_tags( '' );
						$widget_instance['buynow'] = strip_tags( 'Buy Now' );
						$widget_instance['deals'] = -1;
						the_widget('Custom_Merchant_RecentDeals', $widget_instance, array( ) ); 
						$merchant_deals_widget = ob_get_clean();
						//If we have any
						if ( $merchant_deals_widget ) : ?>
                                
                    	<div class="business business_deals section clearfix" style="margin-bottom: 0;">
							<div class="section_title clearfix">
								<h4 class="font_large gb_ff"><?php gb_e('More Deal Options') ?></h4>
								<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
							</div>
							<div class="section_content" style="width: 710px;">
                            	<?php echo $merchant_deals_widget; ?>
                            </div>
                            
                       </div>
                    	<?php endif; ?>
                    
						<div class="business section clearfix">
							<div class="section_title clearfix">
								<h4 class="font_large gb_ff"><?php gb_e('Business:') ?> <a href="<?php gb_merchant_url(gb_get_merchant_id()) ?>" title="<?php gb_merchant_name(gb_get_merchant_id()) ?>"><?php gb_merchant_name(gb_get_merchant_id()); ?></a></h4>
								<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
							</div>
							<div class="section_content">
								<div class="merchant_thumb_meta contrast_light clearfix">
									<?php echo get_the_post_thumbnail(gb_get_merchant_id(),'gbs_150w', array('title' => get_the_title())); ?>
									<?php if ( gb_get_merchants_types_list() != '' ): ?>
										<h4><?php gb_e('Listed') ?></h4>
										<?php gb_get_merchants_types_list(gb_get_merchant_id()) ?>
									<?php endif ?>
								</div><!-- #.merchant_types -->
								<?php if (gb_has_merchant_excerpt(gb_get_merchant_id())): ?>
									<?php echo strip_tags(gb_get_merchant_excerpt(gb_get_merchant_id())); ?>
									<span class="read_more"><a href="<?php gb_merchant_url(gb_get_merchant_id()) ?>" title="<?php gb_merchant_name(gb_get_merchant_id()) ?>"><?php gb_e('Read More.') ?></a></span>
								<?php endif ?>

								<ul class="clearfix merchant-meta">
									<?php if (gb_has_merchant_website(gb_get_merchant_id())): ?>
										<li><a href="<?php gb_merchant_website(gb_get_merchant_id()) ?>" target="_blank"><?php gb_e('Website') ?></a></li>
									<?php endif ?>
									<?php if (gb_has_merchant_facebook(gb_get_merchant_id())): ?>
										<li><a href="<?php gb_merchant_facebook(gb_get_merchant_id()) ?>" target="_blank"><?php gb_e('Facebook') ?></a></li>
									<?php endif ?>
									<?php if (gb_has_merchant_twitter(gb_get_merchant_id())): ?>
										<li><a href="<?php gb_merchant_twitter(gb_get_merchant_id()) ?>" target="_blank"><?php gb_e('Twitter') ?></a></li>
									<?php endif ?>
								</ul>
							</div>
						</div><!-- .widget -->
					<?php endif ?>

					<div class="deal_information section clearfix">
						<div class="section_title clearfix">
							<h4 class="font_large gb_ff"><?php gb_e('Deal Information') ?></h4>
							<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
						</div>
						<div class="section_content">
							<div class="section_content_column">
								<h4 class="gb_ff"><?php gb_e('Highlights:') ?></h4>
								<?php gb_highlights(); ?>
							</div>
							<div class="section_content_column">
								<h4 class="gb_ff"><?php gb_e('Fine Print:') ?></h4>
								<?php gb_fine_print(); ?>
							</div>
						</div>
					</div>

					<?php if ( gb_has_map() ) : ?>
						<div class="map section clearfix">
							<div class="section_title clearfix">
								<h4 class="font_large gb_ff"><?php gb_e('Map') ?></h4>
								<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
							</div>
							<div class="section_content">
								<?php gb_map(); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( comments_open() || '0' != get_comments_number() ) : ?>
						<div class="discussion section clearfix">
							<div class="section_title clearfix">
								<h4 class="font_large gb_ff"><?php gb_e('Discuss This Deal') ?></h4>
								<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
							</div>
							<div class="section_content">
								<?php comments_template( '', true ); ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="more section clearfix">
						<div class="section_title clearfix">
							<h4 class="font_large gb_ff"><?php gb_e('More Stuff') ?></h4>
							<span class="expand font_x_small background_alt"><?php gb_e('More info') ?></span>
						</div>
						<div class="section_content">
						</div>
					</div>

				</div><!-- // .deal_details -->

			<?php endwhile; // end of the loop. ?>

			<div id="sidebar" class="sidebar clearfix">
				<?php do_action('gb_above_default_sidebar') ?>
				<?php dynamic_sidebar( 'deal-sidebar' );?>
				<?php do_action('gb_below_default_sidebar') ?>
			</div><!-- // #sidebar -->

		</div><!-- // #content -->

	</div><!-- // #container-->

<?php get_footer(); ?>