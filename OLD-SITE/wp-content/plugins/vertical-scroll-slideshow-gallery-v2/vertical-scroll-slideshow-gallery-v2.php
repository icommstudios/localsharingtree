<?php

/*
Plugin Name: Vertical scroll slideshow gallery v2
Plugin URI: http://www.gopiplus.com/work/2010/07/18/vertical-scroll-slideshow-gallery-v2/
Description:  Vertical scroll slideshow gallery plugin will create the vertical scrolling image slideshow gallery on the wordpress widget.
Author: Gopi.R
Version: 7.1
Author URI: http://www.gopiplus.com/work/2010/07/18/vertical-scroll-slideshow-gallery-v2/
Donate link: http://www.gopiplus.com/work/2010/07/18/vertical-scroll-slideshow-gallery-v2/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

function vssg2_slideshow() 
{
	$vs2_str = "";
	$vssg2_siteurl = get_option('siteurl');
	$vssg2_pluginurl = $vssg2_siteurl . "/wp-content/plugins/vertical-scroll-slideshow-gallery-v2/";
	$vssg2_xml = get_option('vssg2_xml');
	if($vssg2_xml=="")
	{
		$vssg2_xml = "widget.xml";
	}
	?>
	<script language="JavaScript1.2">
	var vs2_scrollerwidth='<?php echo get_option('vssg2_width'); ?>';
	var vs2_scrollerheight='<?php echo get_option('vssg2_height'); ?>';
	var vs2_pausebetweenimages=<?php echo get_option('vssg2_time'); ?>;
	var vs2_slideimages=new Array();
	<?php
	$doc = new DOMDocument();
	$doc->load( $vssg2_pluginurl . 'gallery/'.$vssg2_xml );
	$images = $doc->getElementsByTagName( "image" );
	$vs2_count = 0;
	foreach( $images as $image )
	{
	  $paths = $image->getElementsByTagName( "path" );
	  $path = $paths->item(0)->nodeValue;
	  $targets = $image->getElementsByTagName( "target" );
	  $target = $targets->item(0)->nodeValue;
	  $titles = $image->getElementsByTagName( "title" );
	  $title = $titles->item(0)->nodeValue;
	  $links = $image->getElementsByTagName( "link" );
	  $link = $links->item(0)->nodeValue;
	  $vs2_str = $vs2_str . "vs2_slideimages[$vs2_count]='<a href=\'$link\' target=\'$target\'><img src=\'$path\' title=\'$title\' alt=\'$title\' border=\'0\'></a>'; ";
	  $vs2_count++;
	}
	echo $vs2_str;
	?>
	</script>
	<script src="<?php echo $vssg2_pluginurl; ?>script.js"></script>
	<ilayer id="vs2_main" width=&{vs2_scrollerwidth}; height=&{vs2_scrollerheight}; visibility=hide>
	<layer id="vs2_first" width=&{vs2_scrollerwidth};>
	<script language="JavaScript1.2">
	if (document.layers)
	document.write(vs2_slideimages[0])
	</script>
	</layer>
	<layer id="vs2_second" width=&{vs2_scrollerwidth}; visibility=hide>
	<script language="JavaScript1.2">
	if (document.layers)
	document.write(vs2_slideimages[dyndetermine=(vs2_slideimages.length==1)? 0 : 1])
	</script>
	</layer>
	</ilayer>
	<script language="JavaScript1.2">
	if (ie||dom)
	{
		document.writeln('<div style="padding:8px 0px 8px 0px;">')
		document.writeln('<div id="vs2_main2" style="position:relative;width:'+vs2_scrollerwidth+';height:'+vs2_scrollerheight+';overflow:hidden;">')
		document.writeln('<div style="position:absolute;width:'+vs2_scrollerwidth+';height:'+vs2_scrollerheight+';clip:rect(0 '+vs2_scrollerwidth+' '+vs2_scrollerheight+' 0);">')
		document.writeln('<div id="vs2_first2" style="position:absolute;width:'+vs2_scrollerwidth+';left:0px;top:1px;">')
		document.write(vs2_slideimages[0])
		document.writeln('</div>')
		document.writeln('<div id="vs2_second2" style="position:absolute;width:'+vs2_scrollerwidth+';visibility:hidden">')
		document.write(vs2_slideimages[dyndetermine=(vs2_slideimages.length==1)? 0 : 1])
		document.writeln('</div>')
		document.writeln('</div>')
		document.writeln('</div>')
		document.writeln('</div>')
	}
	</script>
	<?php
}

function vssg2_install() 
{
	add_option('vssg2_title', "Slide Show");
	add_option('vssg2_width', "100px");
	add_option('vssg2_height', "85px");
	add_option('vssg2_time', "3000");
	add_option('vssg2_xml', "widget.xml");
}

function vssg2_widget($args) 
{
	extract($args);
	echo $before_widget . $before_title;
	echo get_option('vssg2_title');
	echo $after_title;
	vssg2_slideshow();
	echo $after_widget;
}

function vssg2_control()
{
	echo '<p>Vertical Scroll Slideshow Gallery V2.<br> To change the setting goto <b>Vertical Scroll Slideshow Gallery V2</b> link under <b>Settings</b> menu.';
	echo ' <a href="options-general.php?page=vertical-scroll-slideshow-gallery-v2">';
	echo 'click here</a></p>';
}

function vssg2_option() 
{
	?>
	<div class="wrap">
	  <div class="form-wrap">
		<div id="icon-edit" class="icon32 icon32-posts-post"></div>
		<h2>Vertical scroll slideshow gallery v2</h2>
		<?php
		$vssg2_title = get_option('vssg2_title');
		$vssg2_width = get_option('vssg2_width');
		$vssg2_height = get_option('vssg2_height');
		$vssg2_time = get_option('vssg2_time');
		$vssg2_xml = get_option('vssg2_xml');
			
		if (isset($_POST['vssg2_form_submit']) && $_POST['vssg2_form_submit'] == 'yes')
		{
			//	Just security thingy that wordpress offers us
			check_admin_referer('vssg2_form_setting');
			
			$vssg2_title = stripslashes($_POST['vssg2_title']);
			$vssg2_width = stripslashes($_POST['vssg2_width']);
			$vssg2_height = stripslashes($_POST['vssg2_height']);
			$vssg2_time = stripslashes($_POST['vssg2_time']);
			$vssg2_xml = stripslashes($_POST['vssg2_xml']);
			
			update_option('vssg2_title', $vssg2_title );
			update_option('vssg2_width', $vssg2_width );
			update_option('vssg2_height', $vssg2_height );
			update_option('vssg2_time', $vssg2_time );
			update_option('vssg2_xml', $vssg2_xml );
			
			?>
			<div class="updated fade">
				<p><strong>Details successfully updated.</strong></p>
			</div>
			<?php
		}
		?>
		<h3>Plugin setting</h3>
		<form name="vssg2_form" method="post" action="#">
		
			<label for="tag-title">Title</label>
			<input name="vssg2_title" type="text" value="<?php echo $vssg2_title; ?>"  id="vssg2_title" size="70" maxlength="200">
			<p>Please enter your widget title.</p>
			
			<label for="tag-title">Width</label>
			<input name="vssg2_width" type="text" value="<?php echo $vssg2_width; ?>"  id="vssg2_width" maxlength="5">
			<p>Set the scroller width and scroller height to the width/height of the largest image in your slideshow. (Example: 100px)</p>
			
			<label for="tag-title">Height</label>
			<input name="vssg2_height" type="text" value="<?php echo $vssg2_height; ?>"  id="vssg2_height" maxlength="5">
			<p>Set the scroller width and scroller height to the width/height of the largest image in your slideshow. (Example: 85px)</p>
			
			<label for="tag-title">Timeout</label>
			<input name="vssg2_time" type="text" value="<?php echo $vssg2_time; ?>"  id="vssg2_time" maxlength="5">
			<p>Please enter slideshow timeout. (Example: 3000)</p>
			
			<label for="tag-title">Enter XML filename</label>
			<input name="vssg2_xml" type="text" value="<?php echo $vssg2_xml; ?>"  id="vssg2_xml" size="40" maxlength="200">
			<p>Please enter slideshow XML filename. (Example: widget.xml)</p>
			
			<div style="height:10px;"></div>
			<input type="hidden" name="vssg2_form_submit" value="yes"/>
			<input name="vssg2_submit" id="vssg2_submit" class="button" value="Submit" type="submit" />
			<a class="button" target="_blank" href="http://www.gopiplus.com/work/2010/07/18/vertical-scroll-slideshow-gallery-v2/">Help</a>
			<?php wp_nonce_field('vssg2_form_setting'); ?>
		</form>
		</div>
		<h3>Plugin configuration option</h3>
		<ol>
			<li>Drag and drop the widget to your sidebar.</li>
			<li>Add directly in to the theme using PHP code.</li>
		</ol>
	<p class="description">Check official website for more information <a target="_blank" href="http://www.gopiplus.com/work/2010/07/18/vertical-scroll-slideshow-gallery-v2/">click here</a></p>
	</div>
	<?php	
}

function vssg2_widget_init() 
{
	if(function_exists('wp_register_sidebar_widget')) 	
	{
		wp_register_sidebar_widget('Vertical-Scroll-Slideshow-Gallery-V2', 'Vertical Scroll Slideshow Gallery V2', 'vssg2_widget');
	}
	if(function_exists('wp_register_widget_control')) 	
	{
		wp_register_widget_control('Vertical-Scroll-Slideshow-Gallery-V2', array('Vertical Scroll Slideshow Gallery V2', 'widgets'), 'vssg2_control');
	} 
}

function vssg2_deactivation() 
{
	delete_option('vssg2_title');
	delete_option('vssg2_width');
	delete_option('vssg2_height');
	delete_option('vssg2_time');
	delete_option('vssg2_xml');
}

function vssg2_add_to_menu() 
{
	add_options_page('Vertical Scroll Slideshow Gallery V2', 'Vertical Scroll Slideshow Gallery V2', 'manage_options', 'vertical-scroll-slideshow-gallery-v2', 'vssg2_option' );
}

if (is_admin()) 
{
	add_action('admin_menu', 'vssg2_add_to_menu');
}

add_action("plugins_loaded", "vssg2_widget_init");
register_activation_hook(__FILE__, 'vssg2_install');
register_deactivation_hook(__FILE__, 'vssg2_deactivation');
add_action('init', 'vssg2_widget_init');
?>