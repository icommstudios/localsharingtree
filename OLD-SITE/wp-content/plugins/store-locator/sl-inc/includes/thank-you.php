<?php include("sl-env.php"); extract(sl_ty(__FILE__)); ?>
<strong style='font-size:20px; text-align:justify; display:block; font-family:Georgia;'><?php print $thanks_heading; ?></strong>
<!--p>&nbsp;</p-->

<p <?php print $thanks_msg_style; ?>><?php print $thanks_msg; ?></p>
<!--p>&nbsp;</p-->

<?php if (!$is_included){ ?>
<form method="post" name="sl_ty_form">
<?php } ?>
   <!--div--><strong <?php print $action_call_style; ?>> <?php print $action_call; ?></strong>
    <p <?php print $action_buttons_style; ?>>
	<!--br--><strong style='font-size:1.5em;'><img src='<?php print SL_IMAGES_BASE_ORIGINAL."/sl_star.jpg"; ?>'></strong>&nbsp;<input type='button' class='button-primary star_button'  href="" value='  Rate Us!  ' />
	<br><br><strong style='font-size:1.5em;'><img src='<?php print SL_IMAGES_BASE_ORIGINAL."/sl_twitter.jpg"; ?>'></strong>&nbsp;<input type='button' class='button-primary twitter_button' rel="<?php print $text; ?>+<?php print $url; ?>" value='Tell Others! (Tweet #1)' />
	<input type='button' class='button-primary twitter_button' rel="<?php print $text2; ?>+<?php print $url; ?>" value='Tell Others! (Tweet #2)' />
    </p>
    <!--/div-->
	<?php if (!$is_included){ ?> <p>&nbsp;</p><br><!--p>&nbsp;</p--><?php } ?>
    <div style="text-align: center;">
        <!--p-->
        	<input type="hidden" name="sl_thanks" value="show" />
        	<?php if (!$is_included){ ?>
        	<a href='#' onclick="styf=document.forms['sl_ty_form'];styf.sl_thanks.value='true';styf.submit();return false;" style='font-weight:bold;'><?php print $done_msg; ?></a> <strong>OR</strong>  
        	<a href='#' onclick="styf=document.forms['sl_ty_form'];styf.sl_thanks.value='false';styf.submit();return false;"><?php print $noshow_msg; ?></a>
        	<?php } ?>
        <!--/p-->
    </div>
<?php if (!$is_included){ ?>
</form>
<?php } ?>