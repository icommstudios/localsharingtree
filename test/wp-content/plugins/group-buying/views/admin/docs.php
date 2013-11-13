<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function($){
		jQuery("#save_api_key").on('click', function(event) {
			event.preventDefault();
			var $save_button = $( this ),
			api_key_value = $('#group_buying_site_api_key').attr( 'value' );
			$.post( ajaxurl, { action: 'gbs_save_api_key', api_key: api_key_value, save_api_key_nonce: '<?php echo wp_create_nonce( Group_Buying_Help::NONCE ) ?>' },
				function( data ) {
						$('#api-key-save-container').empty().html('<?php self::_e("API Key Saved") ?>: ' + api_key_value);
					}
				);
		});
		jQuery('#submit').remove();
	});
</script>
<div id="welcome-panel" class="welcome-panel">
	<div class="welcome-panel-content">
		
		<h3>Welcome to Group Buying Site!</h3>

		<div class="welcome_columns clearfix">
			<div id="api_key_column">
				<h4><?php gb_e("Let's start by entering your API Key") ?></h4>
				<div id="api-key-save-container">
					<input type="text" name="group_buying_site_api_key" id="group_buying_site_api_key" value="<?php echo get_option( 'group_buying_site_api_key' ) ?>" size="40" class="style_me"> <button id="save_api_key" class="slye_me button button-primary"><?php self::_e('Save') ?></button>
				</div><!-- #api-key-save-container -->
				<p class="about-description clearfix"><?php gb_e('Now, here are some quick links to get you going.') ?><br />
				<?php gb_e('You should also check out the <a href="http://groupbuyingsite.com/docs/">complete documentation</a>') ?></p>
			</div>

			<div id="welcome_video_column">
				<iframe src="//player.vimeo.com/video/25015409?byline=0" width="300" height="200" frameborder="0" class="welcome-video" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			</div>
		</div>


		<div class="welcome-panel-column-container">
			<div class="welcome-panel-column">
				<h4><?php gb_e('Get Started') ?></h4>
				<ul>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?190-Group-Buying-Plugin-Installation-amp-Setup"><?php gb_e('GBS Installation &amp; Setup') ?></a></li>
					<li><?php gb_e( sprintf( 'Settings: <a href="%s">General</a>, <a href="%s">Payment</a>, <a href="%s">Tax</a>, <a href="%s">Shipping</a>', 'http://groupbuyingsite.com/forum/showthread.php?744-Overview-of-Settings', 'http://groupbuyingsite.com/forum/showthread.php?813-Payment-Settings', 'http://groupbuyingsite.com/forum/showthread.php?3456-Tax-Settings', 'http://groupbuyingsite.com/forum/showthread.php?3457-Shipping-Settings' ) ) ?></li>
					<li><?php gb_e( sprintf( 'Adding: <a href="%s">Deals</a>, <a href="%s">Vendors</a>, <a href="%s">Sample Deals &amp; Site Data</a>', 'http://groupbuyingsite.com/forum/showthread.php?745-Adding-a-Deal', 'http://groupbuyingsite.com/forum/showthread.php?808-Adding-a-Merchant', 'http://groupbuyingsite.com/forum/showthread.php?8219-Sample-Deal-and-Site-Data-(XML-Imports)' ) ) ?></li>
					<li><?php gb_e( sprintf( 'Subscriptions: <a href="%s">MailChimp</a>, <a href="%s">Constant Contact</a>', 'http://groupbuyingsite.com/forum/showthread.php?711-MailChimp-Setup', 'http://groupbuyingsite.com/forum/showthread.php?713-Constant-Contact-Setup' ) ) ?></li>
					<li><a href="http://groupbuyingsite.com/forum/forumdisplay.php?130-CrowdFunding-Documentation"><?php gb_e('CrowdFunding Specific Tutorials') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?731-Redirects-What-is-the-logic-behind-theme"><?php gb_e('Prime and Response Theme Homepage Logic') ?></a></li>
				</ul>
			</div>
			<div class="welcome-panel-column">
				<h4><?php gb_e('Next Steps') ?></h4>
				<ul>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?810-SSL-Integration"><?php gb_e('SSL Integration') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?573-Allow-Users-to-Login-with-their-Facebook-Account"><?php gb_e('Facebook Login') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?3203-Setting-Up-and-Using-a-Child-Theme"><?php gb_e('Child Theme Customizations Overview') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?1714-Translating-GBS"><?php gb_e('Customize Text &amp; Translating') ?></a></li>
					<li><?php gb_e( sprintf( '<a href="%s">Filtering</a>: <a href="%s">Countries</a> and <a href="%s">States</a>', 'http://groupbuyingsite.com/forum/showthread.php?370-How-to-Use-Filters', 'http://groupbuyingsite.com/forum/showthread.php?289-Filter-the-Countries', 'http://groupbuyingsite.com/forum/showthread.php?288-Filter-the-States' ) ) ?></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?201-Custom-Front-Page"><?php gb_e('Creating a Custom Front Page') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?708-Blog-Section"><?php gb_e('Creating a Blog Section') ?></a></li>
				</ul>
			</div>
			<div class="welcome-panel-column welcome-panel-last">
				<h4><?php gb_e('Need Help?') ?></h4>
				<ul>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?5774-Troubleshooting-First-Steps"><?php gb_e('First Steps for Troubleshooting') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum/showthread.php?2404-How-To-Update-GBS-3.X"><?php gb_e('Automatic Update Issues') ?></a></li>
					<li><a href="http://groupbuyingsite.com/forum"><?php gb_e('Search the Support Forums') ?></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div id="dashboard_primary" class="postbox metabox-holder">
	<h3><span><?php gb_e('Group Buying Site Blog') ?></span></h3>
	<div class="inside" style="">
		<?php
			include_once( ABSPATH . WPINC . '/feed.php' );
			$rss = fetch_feed( 'http://feeds.feedburner.com/GroupBuyingSite' );
			if ( ! is_wp_error( $rss ) ) :
				$maxitems = $rss->get_item_quantity( 5 );
				$rss_items = $rss->get_items( 0, $maxitems );
			endif;
		?>
		<div class="rss-widget">
			<ul>
				<?php if ( $maxitems == 0 ) : ?>
					<li><?php gb_e( 'Could not connect to GBS server' ); ?></li>
				<?php else : ?>
					<?php foreach ( $rss_items as $item ) :
						$excerpt = gb_get_truncate( strip_tags( $item->get_content() ), 100 );
						?>
						<li><a class="rsswidget" href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php echo $excerpt; ?>"><?php echo esc_html( $item->get_title() ); ?></a> <span class="rss-date"><?php echo $item->get_date('j F Y'); ?></span><div class="rssSummary"><?php echo $excerpt; ?></div></li>
					<?php endforeach; ?>
				<?php endif; ?>

			</ul>
		</div>
	</div>
</div>

