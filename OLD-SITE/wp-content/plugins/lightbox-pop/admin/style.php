<?php require_once( dirname( __FILE__ ) .'/../../../../wp-load.php'); ?>
<style type="text/css">

#system_notice_area {
	position: fixed;
	margin-bottom:40px;
	left:25%;
	width:50%;
	height:20px;
	border-radius:5px;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	font-weight:bold;
	display:none;
	padding:5px;
	color: #000000;
	text-align: center;
	<?php
	if(function_exists('is_admin_bar_showing') && is_admin_bar_showing()){
	?>
	top	:28px;
	<?php
	}else{
	?>
	top: 0px;
	<?php
	}
	?>


	}


	
.xyz_fbook{
	
	background-image: url('<?php echo plugins_url("images/facebook.png",__FILE__) ?>');
	height:16px;
	background-repeat: no-repeat;
	background-position: left center;
	padding-left: 15px;
	text-decoration: none;
	
	
	
}
.xyz_support{
	
	background-image: url('<?php echo plugins_url("images/support.png",__FILE__) ?>');
	height:16px;
	background-repeat: no-repeat;
	background-position: left center;
	padding-left: 15px;
	
	
	
	
}
.xyz_twitt{
	
	background-image: url('<?php echo plugins_url("images/twitter.png",__FILE__) ?>');
	height:16px;
	background-repeat: no-repeat;
	background-position: left center;
	padding-left: 15px;
	text-decoration: none;
	
	
	
}
.xyz_gplus{
	
	background-image: url('<?php echo plugins_url("images/gplus.png",__FILE__) ?>');
	height:16px;
	background-repeat: no-repeat;
	background-position: left center;
	padding-left: 15px;
	text-decoration: none;
	
	
}


#lightbox-pop .xyz_gplus{
margin-left: 3px;
}

#lightbox-pop .plugin-version-author-uri {
	
	background-color: #F4F4F4;
	min-height:16px;
	border-radius:5px;
	margin-bottom: 10px;
	font-weight:bold;
	padding: 5px;
	color: #111111;

-webkit-box-shadow: 0 8px 6px -6px black;
	   -moz-box-shadow: 0 8px 6px -6px black;
	        box-shadow: 0 8px 6px -6px black;
	        
}
#lightbox-pop{
background: #a9e8f5; /* Old browsers */

background: -moz-linear-gradient(top,  #ffffff 0%, #a9e8f5 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(100%,#a9e8f5)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #ffffff 0%,#a9e8f5 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #ffffff 0%,#a9e8f5 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #ffffff 0%,#a9e8f5 100%); /* IE10+ */
background: linear-gradient(top,  #ffffff 0%,#a9e8f5 100%); /* W3C */


}
#lightbox-pop .plugin-version-author-uri a,
#lightbox-pop .plugin-version-author-uri a:link,
#lightbox-pop .plugin-version-author-uri a:hover,
#lightbox-pop .plugin-version-author-uri a:active,
#lightbox-pop .plugin-version-author-uri a:visited{
	
	
	color: #111111;
	text-decoration: none;
	
}
#lightbox-pop .plugin-version-author-uri a:hover{

color:#cc811a;
}
#lightbox-pop .plugin-title{

background-image: url('<?php echo plugins_url("images/xyz_logo.png",__FILE__) ?>');
background-repeat: no-repeat;
background-position: left  bottom;

}

#lightbox-pop .plugin-version-author-uri a#xyz_update,
#lightbox-pop .plugin-version-author-uri a#xyz_update:link,
#lightbox-pop .plugin-version-author-uri a#xyz_update:hover,
#lightbox-pop .plugin-version-author-uri a#xyz_update:active,
#lightbox-pop .plugin-version-author-uri a#xyz_update:visited
{
color: red;
font-weight: bold;
text-decoration: blink;
}


.xyz-premium-comparison
{
width: 100%;
padding:0px;
 border: 1px solid #CCCCCC;
}
.xyz-premium-comparison td
{
 padding: 1px;
 border: 1px solid #CCCCCC;
 height: 25px; 
}
</style>