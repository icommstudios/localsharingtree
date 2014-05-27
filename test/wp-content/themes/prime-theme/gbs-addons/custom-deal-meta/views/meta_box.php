<?php do_action( 'gb_meta_box_deal_theme_meta_pre' ) ?>
<p>
	<textarea rows="5" cols="40" name="featured_content" style="width:98%" placeholder="<?php self::_e( 'Featured Content' ) ?>"><?php print $featured_content; ?></textarea>
	<span class="howto"><?php self::_e( 'Replace thumbnail area (in supported themes) with this featured content. Shortcodes are accepted.' ); ?></span>
</p>
<?php do_action( 'gb_meta_box_deal_theme_meta' ) ?>
