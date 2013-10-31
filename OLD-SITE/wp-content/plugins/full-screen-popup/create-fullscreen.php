<?php

add_action ( 'get_footer', 'fsp_lightbox_create');//, [priority], [accepted_args] );

wp_enqueue_script('jquery');
function fsp_lightbox_create()
{
	$page_option=get_option('xyz_fsp_page_option');
	
	if($page_option==3)
	{

		return false;
	}
	echo fsp_lightbox_display();
}

function fsp_lightbox_display()
{
	$imgpath=plugins_url()."/full-screen-popup/images/";
	$closeimage=$imgpath."close.png";
	$dbcloseimage=$imgpath."dbclose.png";

	$html=get_option('xyz_fsp_html');
	
	$delay=get_option('xyz_fsp_delay');
	$page_count=get_option('xyz_fsp_page_count');
	if($page_count==0) $page_count=1;
	$mode=get_option('xyz_fsp_mode');
	$repeat_interval=get_option('xyz_fsp_repeat_interval');
	$repeat_interval_timing=get_option('xyz_fsp_repeat_interval_timing');
	if($repeat_interval_timing==1)
	{
		$repeat_interval=$repeat_interval*60;
	}
$z_index=get_option('xyz_fsp_z_index');
$corner_radius=get_option('xyz_fsp_corner_radius');

$border_color=get_option('xyz_fsp_border_color');
$bg_color=get_option('xyz_fsp_bg_color');

$border_width=get_option('xyz_fsp_border_width');

$iframe_option=get_option('xyz_fsp_iframe');

 
global $wpdb;

ob_flush();
ob_start();
	?>
	<style type="text/css">

.fsp_content {
display: none;
position: fixed;
_position: fixed;
  
top:0px;
left:0px;
width: 100%;
height: 100%;
padding: 0;
margin:0;
border: <?php echo $border_width; ?>px solid <?php echo $border_color;?>;
background-color: <?php echo $bg_color;?>;
z-index:<?php echo $z_index+1;?>;
overflow: hidden;
border-radius:<?php echo $corner_radius;?>px

}
.fsp_iframe{

width:100%;
height:100%;
border:0;


}

#closediv{
position:absolute;
cursor:pointer;
top: 0px;
right: 0px;
}
</style>


<div id="fsp_light" class="fsp_content"><?php if(!isset($_COOKIE['_xyz_fsp_until'])) {?>
<img id="closediv"   src="<?php  echo $dbcloseimage;?>" onclick = "javascript:fsp_hide_lightbox()"><?php ?>
<!-- <div width="100%" height="20px" style="text-align:right;padding:0px;margin:0px;"><a href = "javascript:void(0)" onclick = "javascript:fsp_hide_lightbox()">CLOSE</a></div> -->
<?php if($iframe_option==1) { ?><iframe  src="<?php echo  plugins_url();?>/full-screen-popup/iframe.php" class="fsp_iframe" scrolling="no"></iframe><?php }else{  
echo do_shortcode($html);}
}?>
</div>

<script type="text/javascript">


var screenheight=jQuery(window).height(); 
var screenwidth=jQuery(window).width(); 

var bordwidth=<?php echo $border_width;?>;

	var newheight;
	var newwidth;
	
	
		
		 newheight=screenheight-(2*bordwidth);
		 document.getElementById("fsp_light").style.height=newheight+'px';
	
		
		
		 newwidth=screenwidth-(2*bordwidth);
			document.getElementById("fsp_light").style.width=newwidth+'px';


	
	



var xyz_fsp_tracking_cookie_name="_xyz_fsp_until";
var xyz_fsp_pc_cookie_name="_xyz_fsp_pc";
var xyz_fsp_tracking_cookie_val=xyz_fsp_get_cookie(xyz_fsp_tracking_cookie_name);
var xyz_fsp_pc_cookie_val=xyz_fsp_get_cookie(xyz_fsp_pc_cookie_name);
var xyz_fsp_today = new Date();

if(xyz_fsp_pc_cookie_val==null)
xyz_fsp_pc_cookie_val = 1;
else
xyz_fsp_pc_cookie_val = (xyz_fsp_pc_cookie_val % <?php echo $page_count;?> ) +1;

expires_date = new Date( xyz_fsp_today.getTime() + (24 * 60 * 60 * 1000) );
document.cookie = xyz_fsp_pc_cookie_name + "=" + xyz_fsp_pc_cookie_val + ";expires=" + expires_date.toGMTString() + ";path=/";


function xyz_fsp_get_cookie( name )
{
var start = document.cookie.indexOf( name + "=" );
//alert(document.cookie);
var len = start + name.length + 1;
if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) )
{
return null;
}
if ( start == -1 ) return null;
var end = document.cookie.indexOf( ";", len );
if ( end == -1 ) end = document.cookie.length;
return unescape( document.cookie.substring( len, end ) );
}


function fsp_hide_lightbox()
{
document.getElementById("fsp_light").style.display="none";
document.getElementById("fsp_light").innerHTML="";

}

function fsp_show_lightbox()
{

//alert(fsp_tracking_cookie_val);

if(xyz_fsp_tracking_cookie_val==1)
return;

if( "<?php echo $mode;?>" == "page_count_only"  || "<?php echo $mode;?>" == "both" )
{
if(xyz_fsp_pc_cookie_val != <?php echo $page_count;?>)
return;
}


document.getElementById("fsp_light").style.display="block";



//expires_date = new Date( xyz_fsp_today.getTime() + (24 * 60 * 60 * 1000) );
//alert(xyz_fsp_today.toGMTString());
	expires_date = new Date(xyz_fsp_today.getTime() + (<?php echo $repeat_interval;?> * 60 * 1000));
document.cookie = xyz_fsp_tracking_cookie_name + "=1;expires=" + expires_date.toGMTString() + ";path=/";


}

if("<?php echo $mode;?>" == "page_count_only")
fsp_show_lightbox();
else
setTimeout("fsp_show_lightbox()",<?php echo $delay*1000;?>);

</script>





<?php 
$lbc=ob_get_contents();
ob_clean();
return  $lbc;

}


?>