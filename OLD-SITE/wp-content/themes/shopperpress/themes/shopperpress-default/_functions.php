<?php

 

// SOCIAL BAR WITH ICONS
function new_theme_header(){

global $wpdb;

$icons = get_option('ppt_socialicons');

echo "<div class='header-top'>
		<div class='w_960'>
			<div class='header-top-left'>
			
			  <div class='f_half left'>
			  <div class='links'>".$icons['text']."</div>
			   
			  </div>
			  
			  <div class='f_half left rightme'>";
			   echo "<ul class='socialicons'>";
            
            	if(strlen($icons['twitter']) > 1){ echo "<li><a href='".$icons['twitter']."' class='twitter' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
            	if(strlen($icons['dribbble']) > 1){ echo "<li><a href='".$icons['dribbble']."' class='dribbble' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				if(strlen($icons['facebook']) > 1){ echo "<li><a href=".$icons['facebook']."' class='facebook' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				if(strlen($icons['linkedin']) > 1){ echo "<li><a href='".$icons['linkedin']."' class='linkedin' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				if(strlen($icons['youtube']) > 1){ echo "<li><a href='".$icons['youtube']."' class='youtube' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				
				
 				if(strlen($icons['google']) > 1){ echo "<li><a href='".$icons['google']."' class='google' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				if(strlen($icons['skype']) > 1){ echo "<li><a href='".$icons['skype']."' class='skype' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				if(strlen($icons['msn']) > 1){ echo "<li><a href='".$icons['msn']."' class='msn' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				
				if(strlen($icons['rss']) > 1){ echo "<li><a href='".$icons['rss']."' class='rss' rel='nofollow'><div>&nbsp;&nbsp;</div></a></li>"; } 
				
              echo "</ul>
			  
			  
			  </div>
			
			</div> 
 
		<div class='clearfix'></div>
		</div> 
	</div>";

}
add_action('premiumpress_header_before','new_theme_header');


// ADMIN OPTIONS FOR SOCIAL ICONS
function ppt_add_watermark_adminpanel(){

global $wpdb;

$icons = get_option('ppt_socialicons');

echo '<fieldset>
<div class="titleh"><h3>Header Text + Icons</h3></div>


<div class="ppt-form-line">	
<span class="ppt-labeltext">Custom Text (left)</span>	 
 <input type="text" name="adminArray[ppt_socialicons][text]" class="ppt-forminput" value="'.$icons['text'].'">
<div class="clearfix"></div>
</div>



<div class="ppt-form-line">	
<span class="ppt-labeltext">Twitter</span>	 
 <input type="text" name="adminArray[ppt_socialicons][twitter]" class="ppt-forminput" value="'.$icons['twitter'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Dribbble</span>	 
 <input type="text" name="adminArray[ppt_socialicons][dribbble]" class="ppt-forminput" value="'.$icons['dribbble'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Facebook</span>	 
 <input type="text" name="adminArray[ppt_socialicons][facebook]" class="ppt-forminput" value="'.$icons['facebook'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Linkedin</span>	 
 <input type="text" name="adminArray[ppt_socialicons][linkedin]" class="ppt-forminput" value="'.$icons['linkedin'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Youtube</span>	 
 <input type="text" name="adminArray[ppt_socialicons][youtube]" class="ppt-forminput" value="'.$icons['youtube'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">RSS</span>	 
 <input type="text" name="adminArray[ppt_socialicons][rss]" class="ppt-forminput" value="'.$icons['rss'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Skype</span>	 
 <input type="text" name="adminArray[ppt_socialicons][skype]" class="ppt-forminput" value="'.$icons['skype'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Google</span>	 
 <input type="text" name="adminArray[ppt_socialicons][google]" class="ppt-forminput" value="'.$icons['google'].'">
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">MSN</span>	 
 <input type="text" name="adminArray[ppt_socialicons][msn]" class="ppt-forminput" value="'.$icons['msn'].'">
<div class="clearfix"></div>
</div>


<div class="savebarb clear"> 
<input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;">
</div>
</fieldset>';
 
 
} 
add_action('premiumpress_admin_setup_left_column','ppt_add_watermark_adminpanel');





		
function custommenu($menu){

global $wpdb, $PPT; 

return str_replace('<div class="w_960"><ul>','<div class="w_960"><ul><li style="min-width:30px;"><a href="'.$GLOBALS['bloginfo_url'].'/" class="homeli">'.$PPT->_e(array('head','1')).'</a></li>',$menu );
}
add_action('premiumpress_menu_inside','custommenu');

 



function demo_home_image(){

	global $wpdb;
	if(isset($GLOBALS['flag-home']) ){
	echo "<img src='".get_template_directory_uri()."/themes/".$GLOBALS['premiumpress']['theme']."/images/home-demo.jpg' title='shopperpress' style='margin-top:10px;'><div class='clearfix'></div>";
	}
	
}
if(defined('PREMIUMPRESS_DEMO')){ 
add_action( 'premiumpress_content_before', 'demo_home_image' ); 
}
 





 

?>