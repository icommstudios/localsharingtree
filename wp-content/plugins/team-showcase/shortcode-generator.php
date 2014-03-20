<?php
//add shortcode generator page
function tshowcase_shortcode_page_add() {
	
	$menu_slug = 'edit.php?post_type=tshowcase';
	$submenu_page_title = 'Shortcode Generator';
    $submenu_title = 'Shortcode Generator';
	$capability = 'manage_options';
    $submenu_slug = 'tshowcase_shortcode';
    $submenu_function = 'tshowcase_shortcode_page';
    $defaultp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
	
	add_action($defaultp, 'tshowcase_enqueue_admin_js');
	
   }

function tshowcase_enqueue_admin_js() {
	
	//Slider JS
	wp_deregister_script( 'tshowcase-bxslider' );
	wp_register_script( 'tshowcase-bxslider', plugins_url( '/js/bxslider/jquery.bxslider.js', __FILE__ ),array('jquery'),false,false);
	wp_enqueue_script( 'tshowcase-bxslider' );	
	
	//Filter JS
	wp_deregister_script( 'tshowcase-filter' );
	wp_register_script( 'tshowcase-filter', plugins_url( '/js/filter.js', __FILE__ ),array('jquery'),false,false);
	wp_enqueue_script( 'tshowcase-filter' );
	
	wp_deregister_script( 'tshowcase-enhance-filter' );
	wp_register_script( 'tshowcase-enhance-filter', plugins_url( '/js/filter-enhance.js', __FILE__ ),array('jquery'),false,false);
	wp_enqueue_script( 'tshowcase-enhance-filter' );
	
	
	wp_deregister_script('tshowcaseadmin');
	wp_register_script( 'tshowcaseadmin', plugins_url( '/js/shortcode-builder.js' , __FILE__ ), array('jquery') );
	wp_enqueue_script( 'tshowcaseadmin' );
	
	// in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'tshowcaseadmin', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	
	
	//All themes
	global $ts_theme_names;	
	foreach ($ts_theme_names as $themearray) {	
		foreach($themearray as $theme) {		
		wp_deregister_style( $theme['name']);
		wp_register_style($theme['name'], plugins_url($theme['link'], __FILE__ ),array(),false,false);
		wp_enqueue_style($theme['name'] );	
		}
	}
	
			
				

	//global styles
	wp_deregister_style( 'tshowcase-global-style' );
	wp_register_style( 'tshowcase-global-style', plugins_url( '/css/global.css', __FILE__ ),array(),false,false);
	wp_enqueue_style( 'tshowcase-global-style' );	
			
	//small icons
	wp_deregister_style( 'tshowcase-smallicons' );
	wp_register_style( 'tshowcase-smallicons', plugins_url( '/css/font-awesome/css/font-awesome.min.css', __FILE__ ),array(),false,false);
	wp_enqueue_style( 'tshowcase-smallicons' );	
	
}



add_action('wp_ajax_tshowcase', 'tshowcase_run_preview');

function tshowcase_run_preview() {	
	
	$orderby = $_POST['porder'];
	$limit = $_POST['plimit'];
  $idsfilter = $_POST['pidsfilter'];
	$category = $_POST['pcategory'];
	$url =  $_POST['purl'];
	$layout = $_POST['playout'];
	$style = $_POST['pstyle'];
	$display = $_POST['pdisplay']; 
	$img = $_POST['pimg'];
  $pagination = 'false';
  $searchact = 'false'; 	 
	$html = build_tshowcase($orderby,$limit,$idsfilter,$category,$url,$layout,$style,$display,$pagination,$img,$searchact);
	
	echo $html;
	die(); // this is required to return a proper result
}



function tshowcase_shortcode_page() { 
	$options = get_option('tshowcase-settings');	
	$categories = $options['tshowcase_name_category'];
	
	global $ts_labels;
	global $ts_theme_names;

?>
	
<h1>Shortcode Generator</h1>
<table cellpadding="5" cellspacing="5">
  <tr>
    <td width="20%" valign="top"><div class="postbox" style="width:360px;">
      <form id="shortcode_generator" style="padding:20px;">
        <h2>What entries do you want to display:</h2>
         Multiple <?php echo $categories; ?> Selection <input name="multiple" type="checkbox" id="multiple" onChange="tshowcaseshortcodegenerate()" value="multiple">
        <span id="multiplemsg" class="howto"></span>
        <p>



          <label for="category"><?php echo $categories; ?>:</label>
          <select id="category" name="category" onChange="tshowcaseshortcodegenerate()">
            <option value="0">All</option>
            <?php 
		
				 $terms = get_terms("tshowcase-categories");
				 $count = count($terms);
				 if ( $count > 0 ){
					 
					 foreach ( $terms as $term ) {
					    echo "<option value='".$term->slug."'>".$term->name."</option>";
						 }
					 
				 }
		
		?>
            </select>
          </p>
        
        <p>
          <label for="orderby">Order By:</label>
          <select id="orderby" name="orderby" onChange="tshowcaseshortcodegenerate()">
            <option value="none">Default (Order Field)</option>
            <option value="title">Name</option>
            <option value="ID">ID</option>
            <option value="date">Date</option>
            <option value="modified">Modified</option>
            <option value="rand">Random</option>
            </select>
          </p>
        <p>
          <label for="limit">Number of entries to display:</label>
          <input size="3" id="limit" name="limit" type="text" value="0" onChange="tshowcaseshortcodegenerate()" />
          <span class="howto"> (Leave blank or 0 to display all)</span></p>
        
        
        </p>

        <p>
          <label for="idsfilter">IDs to display:</label>
          <input size="10" id="idsfilter" name="idsfilter" type="text" value="0" onChange="tshowcaseshortcodegenerate()" />
          <span class="howto"> (Comma sperated ID values of specific entries you want to display. Example: 7,11. Leave blank or 0 to display all)</span></p>
        
        
        </p>
        
        
        <h2>What information do you want to display:</h2>
        <table width="100%" border="0" cellspacing="5" cellpadding="0">
          <tr>
            <td><input name="name" type="checkbox" id="name" onChange="tshowcaseshortcodegenerate()" value="name" checked>
              <label for="name"><?php echo $ts_labels['name']['label']; ?></label></td>
            <td><input name="photo" type="checkbox" id="photo" onChange="tshowcaseshortcodegenerate()" value="photo" checked>
              <label for="photo"><?php echo $ts_labels['photo']['label']; ?></label></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td><input name="smallicons" type="checkbox" id="smallicons" value="smallicons" onChange="tshowcaseshortcodegenerate()">
              <label for="smallicons"><?php echo $ts_labels['smallicons']['label']; ?></label>
              &nbsp;</td>
            <td colspan="2"><span class="howto">Will display small icons before the information</span></td>
            </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td><input name="social" type="checkbox" id="social" onChange="tshowcaseshortcodegenerate()" value="social" checked>
              <label for="social"><?php echo $ts_labels['socialicons']['label']; ?></label></td>
            <td><label for="position">
              <input name="position" type="checkbox" id="position" onChange="tshowcaseshortcodegenerate()" value="position" checked>
              <?php echo $ts_labels['position']['label']; ?></label></td>
            <td><label for="location">
              <input name="location" type="checkbox" id="location" value="location" onChange="tshowcaseshortcodegenerate()">
              <?php echo $ts_labels['location']['label']; ?> &nbsp;</label></td>
            </tr>
          <tr>
            <td><input name="email" type="checkbox" id="email" onChange="tshowcaseshortcodegenerate()" value="email" checked>
              <label for="email"><?php echo $ts_labels['email']['label']; ?></label>
              &nbsp;</td>
            <td> <input name="freehtml" type="checkbox" id="freehtml" value="freehtml" onChange="tshowcaseshortcodegenerate()">
              <label for="freehtml"><?php echo $ts_labels['html']['label']; ?></label>&nbsp;</td>
            <td><input name="telephone" type="checkbox" id="telephone" value="telephone" onChange="tshowcaseshortcodegenerate()">
              <label for="telephone"><?php echo $ts_labels['telephone']['label']; ?> </label></td>
            </tr>
          <tr>
            <td><input name="website" type="checkbox" id="website" value="website" onChange="tshowcaseshortcodegenerate()">
              <label for="website"><?php echo $ts_labels['website']['label']; ?></label></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
          </table>
        
        <p>
          <label for="singleurl">Single Page Link:          </label>
          <select id="singleurl" name="singleurl" onChange="tshowcaseshortcodegenerate()">
            <option value="inactive">Inactive</option>
            <option value="active">Active</option>
            </select>
          <span class="howto">Only considered if Single Page is Active on Settings</span></p>
        
        <h2>How you want it to look like:</h2>
        
        <div style="border:1px solid #ccc; background:#FFF; padding:10px;">
        Load a Layout Preset:
          <br>
          <select name="preset" id="preset" onChange="tshowcasepreset()">
          <option value="none">None</option>
           <option value="polaroid">Polaroid Grid </option>
           <option value="white-polaroid">White Polaroid Grid </option>
           <option value="gray-card-grid">Gray Card Grid </option>
           <option value="circle-grid">Circle Centered Grid</option>
           <option value="content-right-simple-grid" selected>Simple Grid with content right</option>
           <option value="content-below-simple-grid">Simple Grid with content below</option>
           <option value="hover-circle-white-grid">Circle Images With Info on Hover I</option>
           <option value="hover-circle-grid">Circle Images With Info on Hover II</option>
           <option value="hover-square-grid">Squared Images With Info on Hover</option>
           <option value="simple-table">Simple Table Layout</option>
            <option value="simple-pager">Simple Thumbnails Pager</option>
             <option value="circle-pager">Circle Thumbnails Pager</option>
               <option value="gallery-pager">Gallery style Thumbnails Pager</option>
                 
        </select>
          <span class="howto">Choosing a  preset will automaticaly select predefined values for the visuals.
        You can then adjust the options to your needs.</span></div>
        
        <p>
          <label for="layout">Layout:</label>
          <select id="layout" name="layout" onChange="tshowcaseshortcodegenerate()">
            <option value="grid">Grid</option>
            <option value="hover">Hover Grid</option>
            <option value="pager">Thumbnails Pager</option>
            <option value="table">Table</option>
            </select>
          </p>
        
        <div id="columnsdiv">
          
          <p>
            <label for="columns">Columns:</label>
            <select name="columns" id="columns" onChange="tshowcaseshortcodegenerate()">
              <option value="normal-float">Normal Float</option>
              <option value="1-column">1 Column</option>
              <option value="2-columns" selected>2 Columns</option>
              <option value="3-columns">3 Columns</option>
              <option value="4-columns">4 Columns</option>
              <option value="5-columns">5 Columns</option>
              <option value="6-columns">6 Columns</option>
              </select>
            </p>
          
          </div>          
        <div id="griddiv">
        
         <div style="border:1px solid #ccc; background:#FFF; padding:5px;">
          <label for="filtergrid"><?php echo $categories.' '.$ts_labels['filter']['label']; ?>:</label>
           <select name="filtergrid" id="filtergrid" onChange="tshowcaseshortcodegenerate()">
             
             <option value="inactive" selected>Inactive</option>
             <option value="filter">Active - Hide Filter</option>
             <option value="enhance-filter">Active - Enhance Filter</option>
             
           </select>
           <span class="howto">When active, a jQuery Category filter will display above the Grid. </span>
          </div>
        
          
          <p>Theme: 
            <label for="grid-styling"></label>
            <select name="grid-styling" id="grid-styling" onChange="tshowcaseshortcodegenerate()">
              
              <?php 
		   foreach ($ts_theme_names['grid'] as $tbstyle) {
		   ?>
              
              <option value="<?php echo $tbstyle['key'] ?>"><?php echo $tbstyle['label'] ?></option>
              
              <?php } ?>
              </select>
            </p>
          
          
          
          <p>
            <label for="composition">Composition:</label>
            <select name="composition" id="composition" onChange="tshowcaseshortcodegenerate()">
              <option value="img-left" selected>Image Left - Content Right</option>
              <option value="img-right">Content Right - Image Left</option>
              <option value="img-above">Image Above - Content Below</option>
              </select>
            </p>
          </div>
        
        <div id="pagerdiv">
          
          <div style="border:1px solid #ccc; background:#FFF; padding:5px;">
          <label for="filterpager"><?php echo $categories.' '.$ts_labels['filter']['label']; ?>:</label>
           <select name="filterpager" id="filterpager" onChange="tshowcaseshortcodegenerate()">
             
             <option value="inactive" selected>Inactive</option>
             <option value="filter">Active - Hide Filter</option>
             <option value="enhance-filter">Active - Enhance Filter</option>
             
           </select>
           <span class="howto">When active, a jQuery Category filter will display above the Grid. </span>
          </div>


          <p>Theme: 
            <label for="pager-styling"></label>
            <select name="pager-styling" id="pager-styling" onChange="tshowcaseshortcodegenerate()">
              
              <?php 
		   foreach ($ts_theme_names['pager'] as $tbstyle) {
		   ?>
              
              <option value="<?php echo $tbstyle['key'] ?>"><?php echo $tbstyle['label'] ?></option>
              
              <?php } ?>
              </select>
            </p>
          
          <p>
            <label for="pagercomposition">General Composition:</label>
            
            <select name="pagercomposition" id="pagercomposition" onChange="tshowcaseshortcodegenerate()">
              <option value="thumbs-left" selected>Thumnails Left - Content Right</option>
              <option value="thumbs-right">Content Left - Thumbnails Right</option>
              <option value="thumbs-below">Content Above - Thumbnails Below</option>
              </select>
            </p>
          <p>
            <label for="pagerimgcomposition">Image Composition:</label>
            <select name="pagerimgcomposition" id="pagerimgcomposition" onChange="tshowcaseshortcodegenerate()">
              <option value="img-left">Image Left - Content Right</option>
              <option value="img-right">Content Right - Image Left</option>
              <option value="img-above" selected>Image Above - Content Below</option>
              </select>
            </p>
          </div>
        
        
        <div id="tablediv">
          <p>Theme: 
            <label for="table-styling"></label>
            <select name="table-styling" id="table-styling" onChange="tshowcaseshortcodegenerate()">
              
              <?php 
		   foreach ($ts_theme_names['table'] as $tbstyle) {
		   ?>
              
              <option value="<?php echo $tbstyle['key'] ?>"><?php echo $tbstyle['label'] ?></option>
              
              <?php } ?>
              </select>
            </p>
          </div>
        
        <div id="hoverdiv">
        
         <div style="border:1px solid #FFF; background:#FFF; padding:5px;">
          <label for="filter"><?php echo $categories.' '.$ts_labels['filter']['label']; ?>:</label>
           <select name="filterhover" id="filterhover" onChange="tshowcaseshortcodegenerate()">
             <option value="filter">Active - Hide Effect</option>
             <option value="enhance-filter">Active - Enhance Effect</option>
             <option value="inactive" selected>Inactive</option>
           </select>
           <span class="howto">When active, a jQuery Category filter will display above the Grid.  </span>
          </div>
        
          <p>Theme:
            <label for="hover-styling"></label>
            <select name="hover-styling" id="hover-styling" onChange="tshowcaseshortcodegenerate()">
              
              <?php 
		   foreach ($ts_theme_names['hover'] as $tbstyle) {
		   ?>
              
              <option value="<?php echo $tbstyle['key'] ?>"><?php echo $tbstyle['label'] ?></option>
              
              <?php } ?>
              </select>
            </p>
          
          
         
          
          </div>
        
        <div id="imgdiv">
          <p>Image Shape:
            <select id="imgstyle" name="imgstyle" onChange="tshowcaseshortcodegenerate()">
              <option value="img-square">Square (normal)</option>
              <option value="img-rounded">Rounded Corners</option>
              <option value="img-circle">Circular</option>
              
              </select>
            </p>
          
          <p>Image Effect:
            <select id="imgeffect" name="imgeffect" onChange="tshowcaseshortcodegenerate()">
              <option value="">None</option>
              <option value="img-grayscale">Grayscale</option>
              <option value="img-shadow">Shadow Highlight</option>
                <option value="img-white-border">White Border</option>
              <option value="img-grayscale-shadow">Shadow Highlight & Grayscale</option>
              
              </select>
            </p>
          </div>
        <p>
          
          <label for="textalign"> Text-Align:</label>
          <select name="textalign" id="textalign" onChange="tshowcaseshortcodegenerate()">
            <option value="text-left" selected>Left</option>
            <option value="text-right" >Right</option>         
            <option value="text-center">Center</option>
            </select>
          </p>
        <div id="imgsize" style="border-top:1px dashed #CCC;">
          <p>Image Size Override: 
            <label for="img"></label>
            <input type="text" name="img" id="img" onChange="tshowcaseshortcodegenerate()">
            <br>
            <span class="howto">Leave blank to use default values.<br>
              In case you want to override the default image size settings, use this field to put the width and height values in the following format: width,height <br>
              ex. 100,100. <br>
            Width value will prevail if images don't have exactly this size.</span></p>
          </div>
        
        
        
        </form>
      </div>
      <div id="howto"><a href="http://cmoreira.net/team-showcase/" target="_blank">Browse examples</a> or read more about the shortcode options at the <a href="http://cmoreira.net/team-showcase/documentation#shortcodes" target="_blank">online documentation of the plugin</a>.</div></td>
    <td width="80%" valign="top"><h3>Shortcode</h3>
      Use this shortcode to display the list of Members in your posts or pages! Just copy this piece of text and place it where you want it to display.
      <div id="shortcode" style="padding:10px;" class="updated"></div>
      <h3>PHP Function</h3>
      Use this PHP function to display the list of Members directly in your theme files!
      <div id="phpcode" style="padding:10px;" class="updated"> </div>
    <h3>Preview</h3>
    
    <div id="preview-warning" style="padding:5px; margin:10px 0px 30px 0px; border-radius:5px; font-weight:bold; font-size:0.9em; border:1px solid #CCC; background-color:#F5f5f5;">Attention! <br>
      This is a preview only. The visuals might differ after applying the shortcode or function on your theme due to extra styling rules that your Theme might have or the available space. <br>
      Some combination of settings don't work well, as they don't fit together visually or are conflictual. </div> 
    
    <div id="preview">
  
    </div>
    <div style="clear:both; margin:20px 10px;">
    <strong>Current Seetings Shortcode:</strong>
    <div id="shortcode2" style="padding:10px;" class="updated"></div>
    </div>
    
    </td>
  </tr>
</table>
 
    
<?php } 



?>