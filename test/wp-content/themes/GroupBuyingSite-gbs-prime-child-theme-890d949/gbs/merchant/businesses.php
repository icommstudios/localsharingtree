<?php get_header(); ?>

		<div id="merchant_loop" class="container prime main clearfix">

			<div class="merchant clearfix">
				<div class="page_title business-page"><!-- Begin #page_title -->
					<h1 class="gb_ff"><?php gb_e('Local Sharing Tree Business Directory'); ?></h1>
				</div><!-- End #page_title -->
				<div class="gb_filters clearfix">
				<div class="browse_by_type">
				<?php gb_e('Browse by Type'); ?>
                <?php
				$types = gb_get_merchant_types();
				echo '<select id="merchant_type_select" name="merchant_type_select" onChange="javascript:onCatChange();">';
				echo '<option value="" '.$active.'>All types</option>';
				foreach ( $types as $type ) {
					$active = ( $type->name == $wp_query->get_queried_object()->name ) ? 'selected="selected"' : '';
					echo '<option value="'.$type->slug.'" '.$active.'>'.$type->name.'</option>';
					
				}
				echo '</select>';
				?>
				<script type="text/javascript"><!--
				jQuery(document).ready( function($){
					$('#merchant_type_select').on('change', function() {
					  //alert( this.value ); // or $(this).val()
					  if ( this.value == '' ) {
						   location.href = "<?php echo home_url('/business'); ?>";
					  } else {
					  	 location.href = "<?php echo home_url('/business-type'); ?>/"+this.value;
					 }
					 
					});
				});
                --></script>
            	</div>
                
                <?php if ( function_exists('custom_show_filter_letters') ) custom_show_filter_letters(); ?>
                <hr>
                <div class="button_reset_filters_wrap"><a href="<?php echo site_url('business'); ?>" class="button font_small">Reset filters</a></div>
            	</div>
				
				<?php if ( ! have_posts() ) : ?>
					
					<?php get_template_part( 'deal/no-deals', 'deal/index' ); ?>
					
				<?php endif; ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'inc/loop-merchant', 'inc/loop-item' ); ?>

				<?php endwhile; ?>

				<?php if (  $wp_query->max_num_pages > 1 ) : ?>
					<?php get_template_part( 'inc/loop-nav', 'inc/index-nav' ); ?>
				<?php endif; ?>
			</div><!-- #content -->
		</div>
		
<?php get_footer(); ?>