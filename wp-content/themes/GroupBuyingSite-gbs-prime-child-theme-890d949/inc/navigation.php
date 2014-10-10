<div class="topnav-wrap clearfix">

	<div class="topnav container cleasrfix">
		<?php wp_nav_menu( array( 'sort_column' => 'menu_order', 'theme_location' => 'topnav', 'depth' =>'2', 'container' => 'none' ) ); ?>
	</div>

</div>

<div id="header_wrap" class="prime boxed_prime clearfix">

	<div id="lst_header" class="container clearfix">

			<a id="lst_logo" href="<?php echo site_url() ?>"><img src="<?php gb_header_logo() ?>"></a>

		<div class="lst_header_meta">
			<div id="lst_login_form">
				<div id="login_wrap" class="gb_ff font_small clearfix">
					<?php if ( !is_user_logged_in() ): ?>
						<a href="<?php echo wp_login_url(); ?>" class="head-login-drop-link"><?php gb_e( 'Login' ) ?></a>
						<a href="<?php gb_account_register_url(); ?>" class="head-register"><?php gb_e( 'Register' ) ?></a>

						<?php gb_facebook_button(); ?>
					<?php else: ?>
						<div class="<?php if ( !is_user_logged_in() ) echo 'hide'; ?>">
							<span class="header_name">
								<span class="gravatar"><?php gb_gravatar() ?></span>
								<?php gb_e( 'Hi,' ) ?> <?php gb_name() ?></a>
							</span>
			<span><a href="<?php gb_cart_url() ?>"><?php gb_e( 'Cart' ) ?></span> | 
			<span class="header_cart"><a href="<?php gbs_account_link() ?>" class="name" title="<?php gb_e( 'Your Account' ) ?>"><?php gb_e( 'My Account' ) ?></a></span> | 
			<?php gb_logout_url(); ?>
						</div>
					<?php endif ?>
					<?php $locations = gb_get_locations();
							if ( !empty( $locations ) && !is_wp_error( $locations ) ) : ?>
								<div id="location">
									<div class="header-locations-drop-link gb_ff">
										<span class="current_location"><?php gb_current_location_extended(); ?></span>

										<div id="locations_header_wrap" class="clearfix cloak header_color">
											<?php gb_list_locations(); ?>
											</div><!-- #locations_header_wrap. -->
										</div>
								</div>
							<?php endif; ?>
				</div><!-- #login_wrap -->
			</div>

		</div>

		<div class="lst-header-search" tabindex="2">
				<?php get_search_form(); ?> 
			</div>

	</div><!-- #header -->

</div><!-- #header_wrap -->

<div id="navigation" class="container gb_ff clearfix">

	<div id="main_navigation" class="hor_navigation clearfix">
		<?php wp_nav_menu( array( 'sort_column' => 'menu_order', 'theme_location' => 'header', 'depth' =>'2', 'container' => 'none' ) ); ?>
	</div><!-- #navigation -->

	<?php if ( !is_user_logged_in() ): ?>
		<div id="nav_subscription" class="subscription_form clearfix">
			<span id="subscribe_dd" class="contrast"><?php gb_e( 'Get the Latest Deals' ) ?></span>
			<div id="subscription_form_wrap" class="cloak">
				<?php //gb_subscription_form(); ?>
                <form action="/account/register" id="gb_continue_register_form" method="post" class="clearfix">
                    <span class="option email_input_wrap clearfix">
                        <input type="text" name="gb_user_email" id="gb_user_email" value="Enter your email" onblur="if (this.value == '')  {this.value = 'Enter your email';}" onfocus="if (this.value == 'Enter your email') {this.value = '';}">
                    </span>
                    <span class="submit clearfix"><input type="submit" class="button-primary" name="gb_continue_register" id="gb_continue_register" value="Continue →"></span>
                </form>
			</div>
		</div><!-- #header_subscription.subscription_form -->
	<?php endif ?>

</div><!-- #navigation -->
