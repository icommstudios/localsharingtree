<?php
if (!defined('PREMIUMPRESS_SYSTEM')) {	header('HTTP/1.0 403 Forbidden'); exit; }
global $PPT, $wpdb;

/* =============================================================================
   AMAZON START SEARCH QUERY
   ========================================================================== */ 
 
if(isset($_GET['runnow'])){

	require_once (TEMPLATEPATH ."/PPT/class/class_amazon.php");
	$obj = new AmazonProductAPI();
	 
	$obj->AmazonRunSearch($_GET['runnow']);
	$GLOBALS['error'] 		= 1;
	$GLOBALS['error_type'] 	= "ok"; //ok,warn,error,info
	$GLOBALS['error_msg'] 	= "Test Run Completed Successfully";
	
}elseif(isset($_GET['delf']) && !isset($_POST['feed'])){
 	require_once (TEMPLATEPATH ."/PPT/class/class_amazon.php");
	$obj = new AmazonProductAPI();
	$obj->AmazonDeleteSearch($_GET['delf']);
	$GLOBALS['error'] 		= 1;
	$GLOBALS['error_type'] 	= "ok"; //ok,warn,error,info
	$GLOBALS['error_msg'] 	= "Schedule Deleted Successfully";

} 

/* =============================================================================
   EBAY IMPORT TOOLS
   ========================================================================== */ 


if(isset($_POST['ebay'])){
update_option("ebay_api",$_POST['ebay_api']);
//update_option("ebay_tracking",$_POST['ebay_tracking']);
//update_option("ebay_customid",$_POST['ebay_customid']);
$GLOBALS['ebaysearch'] =1;
}
 

$GLOBALS['suggesttab']=true;
 
 	// DATAFEEDR
	require_once (TEMPLATEPATH ."/PPT/class/class_datafeedr.php");		
	$PPTDatafeedr			= new PremiumPressTheme_Datafeedr;
	
	if(isset($_GET['action']) && $_GET['action'] == "datafeedr"){
	
		$dd = explode("-",$PPTDatafeedr->IMPORTPRODUCTS());	
		 print '<div class="msg msg-success">Datafeedr Update Successful. Added ('.$dd[0].') / Updated ('.$dd[1].')</p></div>';
	 
	}
	
	// AMAZON
	if(isset($_GET['action']) && $_GET['action'] == "amazon-debug"){
	
		require_once (TEMPLATEPATH ."/PPT/class/class_amazon.php");		 
		$obj = new AmazonProductAPI();	 
		$obj->AmazonAutoUpdaterTool();
		die();
		
	}
 	
	 

?>

<?php if (!defined('PREMIUMPRESS_SYSTEM')) {	header('HTTP/1.0 403 Forbidden'); exit; }  global $PPT,$PPTDesign; PremiumPress_Header(); ?>

<?php 
 

if(isset($GLOBALS['ebaysearch'])){
  
	include("importtools/_ebay_results.php");	
 
}else{


function iscurlinstalled() {
	if  (in_array  ('curl', get_loaded_extensions())) {
		return true;
	}
	else{
		return false;
	}
}

if (iscurlinstalled()){ }else{  die("The <a href='http://en.wikipedia.org/wiki/CURL' targe='_blank'>cURL</a> function used to connect to Amazon is NOT installed on your hosting account, please contact your hosting provider to enable this."); }

?>





<div id="premiumpress_box1" class="premiumpress_box premiumpress_box-100"><div class="premiumpress_boxin"><div class="header">
<h3><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/new/block7.png" align="middle"> Import Tools</h3>	 <a class="premiumpress_button" href="javascript:void(0);" onclick="PPHelpMe()"><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/youtube.png" align="middle" style="margin-top:-10px;"> Help Me</a> 						 
<ul>
	
	<li><a rel="premiumpress_tab1" href="#" class="active">Amazon</a></li>
    
    <li><a rel="premiumpress_tab2" href="#">Ebay</a></li>
   
    <li><a rel="premiumpress_tab3" href="#">Datafeedr</a></li>
     
    
    <li><a rel="premiumpress_tab4" href="#">Affiliate ID's</a></li>
 
    
</ul>
</div>

<style>
select { border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px; }
</style>


<div id="premiumpress_tab1" class="content">  

<div class="grid400-left">

<?php $ff = explode("wp-content",TEMPLATEPATH); ?>
<form method="post"  target="upload_target" action="<?php echo get_template_directory_uri(); ?>/admin/importtools/amazon_results.php" enctype="multipart/form-data" onSubmit="jQuery('#searchresultsform').show();window.frames['upload_target'].document.body.innerHTML='<br>Loading results, please wait...';">			
<input type="hidden" name="feed" value="1">
<input type="hidden" name="feed_path" value="<?php echo $ff[0]; ?>">
<input type="hidden" name="web_path" value="<?php echo PPT_THEME_URI; ?>">

<input type="hidden" name="amazon[start_page]" value="1">
<fieldset>
<div class="titleh"> <h3>Amazon Search</h3>  </div>

<div class="ppt-form-line">     
<span class="ppt-labeltext">Search Keyword</span>
<input name="amazon[keyword]"  type="text" class="ppt-forminput" style="background:#D9F9D8;"> 
<div class="clearfix"></div>
</div>  

<div class="ppt-form-line">     
<span class="ppt-labeltext">Search Category</span>
 <select name="amazon[keyword_cat]" class="ppt-forminput">
            <option value="All" selected>All Categories (see notes below)</option>
            <option>Apparel</option>
            <option>Automotive</option>
            <option>Baby</option>
            <option>Beauty</option>
            <option>Blended</option>
            <option>Books</option>
            <option>Classical</option>
            <option>DigitalMusic</option>
            <option>DVD</option>
            <option>Electronics</option>
            <option>ForeignBooks</option>
            <option>GourmetFood</option>
            <option>Grocery</option>
            <option>HealthPersonalCare</option>
            <option>HomeGarden</option>
            <option>HomeImprovement</option>
            <option>Industrial</option>
            <option>Jewelry</option>
            <option>KindleStore</option>
            <option>Kitchen</option>
            <option>Magazines</option>
            <option>Merchants</option>
            <option>Miscellaneous</option>
            <option>MP3Downloads</option>
            <option>Music</option>
            <option>MusicalInstruments</option>
            <option>MusicTracks</option>
            <option>OfficeProducts</option>
            <option>OutdoorLiving</option>
            <option>PCHardware</option>
            <option>PetSupplies</option>
            <option>Photo</option>
            <option>Shoes</option>
            <option>SilverMerchant</option>
            <option>Software</option>
            <option>SoftwareVideoGames</option>
            <option>SportingGoods</option>
            <option>Tools</option>
            <option>Toys</option>
            <option>VHS</option>
            <option>Video</option>
            <option>VideoGames</option>
            <option>Watches</option>
            <option>Wireless</option>
            <option>WirelessAccessories</option>
            </select>
<div class="clearfix"></div>
   <small>Note, you can not order by 'All Category Searches'. category search also have mixed sort options.  <a href="http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/SortingbyPopularityPriceorCondition.html" target="_blank">See valid Sort</a></small>
</div>

<div class="ppt-form-line">     
<span class="ppt-labeltext">Automatic Import</span>
         <select name="schedule[time]"  style="width: 180px; font-size:14px; border:1px solid #666;">
         <option value="">-----------</option>
            <option value="hourly">Search Every Hourly</option>
            <option value="twicedaily">Search Twice Daily</option>
            <option value="daily">Search Once Daily</option>
            </select>
<div class="clearfix"></div>
</div> 

<p><input class="premiumpress_button" type="submit" value="Start Search" style="color:white;" /></p>  
<br />
           
     <?php /*        
<div class="titleh"> <h3>Word Replace  - <a href="javascript:void(0);" onclick="toggleLayer('amazon1');">Show/Hide Options</a></h3>  </div>
<br /><div id="amazon1" style="display:none">
  <div class="ppt-form-line">     
<span class="ppt-labeltext">Search / Replace</span>
 <input name="searchfor[0]" class="ppt-forminput" style="width:70px;"> With <input name="replacewith[0]" class="ppt-forminput" style="width:70px;"> 
<div class="clearfix"></div>
</div>                   

<div class="ppt-form-line">     
<span class="ppt-labeltext">Search / Replace</span>
 <input name="searchfor[1]" class="ppt-forminput" style="width:70px;"> With  <input name="replacewith[1]" class="ppt-forminput" style="width:70px;">
<div class="clearfix"></div>
</div>       
</div>*/ ?>


<div class="titleh"> <h3>Filter Options - <a href="javascript:void(0);" onclick="toggleLayer('amazon2');">Show/Hide Options</a></h3>    </div>
<br /><div id="amazon2" style="display:none">
<div class="ppt-form-line">     
<span class="ppt-labeltext">Order By</span>
 <select name="amazon[Sort]" class="ppt-forminput">
            <option value=""></option>
            <option value="price">Price (low - high)</option>
            <option value="-price">Price (high - low)</option>
            <option value="inverseprice">Inverse Price</option>
            <option value="sale-flag">On Sale</option>
            <option value="salesrank">Bestselling</option>
            <option value="pmrank">Featured items</option>
            <option value="relevancerank">Relevance Rank</option>
            <option value="reviewrank">reviewrank</option>
            <option value="titlerank">Alphabetical: A to Z</option>
              <option value="-titlerank">Alphabetical: Z to A</option>
              <option value="-launch-date">Newest arrivals</option>
               </select>
<div class="clearfix"></div>
</div> 
 <div class="ppt-form-line">     
<span class="ppt-labeltext"> Price</span>
Min <input name="amazon[minprice]" type="text" class="ppt-forminput" style="width:80px;">  
<div class="clearfix"></div>
<div style="margin-left:140px;">Max <input name="amazon[maxprice]" type="text" class="ppt-forminput" style="width:80px;"> </div>
</div>    

 <div class="ppt-form-line">     
<span class="ppt-labeltext">Condition</span>
<select name="amazon[condition]" style="width: 150px;">
            <option>All</option>
            <option>New</option>
            <option>Used</option>
            <option>Refurbished</option>
            <option>Collectible</option>
            </select> 
<div class="clearfix"></div>
</div>    

<div class="ppt-form-line">     
<span class="ppt-labeltext">Amazon Store</span>
  <select name="amazon[country]" class="ppt-forminput">
            <option value="com" selected>United States (.com Store)</option>
            <option value="ca">Canada (.ca Store)</option>
            <option value="co.uk">United Kingdom (.co.uk Store)</option>
            <option value="fr">France (.fr Store)</option>
            <option value="de">Germany (.de Store)</option>
            <option value="jp">Japan (.jp Store)</option>
            <option value="it">Italy (.it Store)</option>
            <option value="es">Spain (.es Store)</option> 
            <option value="cn">China (.cn Store)</option> 
            </select>
<div class="clearfix"></div>
</div>    

 <div class="ppt-form-line">     
<span class="ppt-labeltext">Brand</span>
<input name="amazon[brand]" type="text" class="ppt-forminput"> 
<div class="clearfix"></div>
</div> 
 <div class="ppt-form-line">     
<span class="ppt-labeltext">Amazon Browse Node </span>
<input name="amazon[node]" type="text" class="ppt-forminput">  
<div class="clearfix"></div>
   <small>Use the Browse Node parameter to narrow your search to a specific category of products in the Amazon catalog. The Browse Node parameter may contain the ID of any Amazon browse node. <a href="http://www.browsenodes.com/" target="_blank">Click here to see the full list</a></small>
</div>   
</div>


<div class="titleh"> <h3>Save Products  - <a href="javascript:void(0);" onclick="toggleLayer('amazon3');">Show/Hide Options</a></h3>  </div>
<br /><div id="amazon3" style="display:none">

<?php $taxArray = get_option("ppt_custom_tax");  
 
if(is_array($taxArray)){ 
 
	foreach($taxArray as $tax){	
	
	
	 
	if(strlen($tax['title']) > 1){
	
	 
		$NewTax = strtolower(htmlspecialchars(str_replace(" ","-",str_replace("&","",str_replace("'","",str_replace('"',"",str_replace('/',"",str_replace('\\',"",strip_tags($tax['name'])))))))));
	 
		  $terms = get_terms($NewTax,array("hide_empty"=>false));
		 
			 $count = count($terms);
			 
			 if ( $count > 0 ){
			  	echo "<div class='ppt-form-line'><span class='ppt-labeltext'>".$tax['title']."</span>";
				 echo "<select name='tax[".$NewTax."][]' multiple='multiple' size=5 style='font-size:14px; height:100px;'><option value=''>-----------------------------------</option>";
				 foreach ( $terms as $term ) {
			 
				   echo "<option value='".$term->term_id. "'>" . $term->name . "</option>";					
				 }
				 echo "</select><div class='clearfix'></div></div>";
			 }
			}
    		  
    	 }	 
	 } 

?>


 <div class="ppt-form-line">     
<span class="ppt-labeltext">Category:</span>
       <select name="cat[]2" multiple="multiple" size="5" style="font-size:14px; height:200px;">
              <option value="0">-----------------------------------</option>
<?php echo premiumpress_categorylist('',false,false,"category",0,true); ?>
            </select>
<div class="clearfix"></div>
</div>


</div>
 
           
           


</fieldset>
</form>







</div><div class="grid400-left last">

<fieldset id="searchresultsform" style="display:none;">
<div class="titleh"> <h3>Search Results</h3></div>
<iframe id="upload_target" name="upload_target" src="<?php echo PPT_THEME_URI; ?>/admin/importtools/index.html" style="width:390px;height:600px;border:0px solid #fff; margin-left:0px;margin-top:10px; "></iframe>


</fieldset>

 



<fieldset>
<div class="titleh"> <h3>Amazon Connection Settings</h3>  </div>
<?php if(get_option("affiliates_20_ID") == "" || get_option("amazon_KEYID") == "" || get_option("amazon_SECRET") == ""){ ?>
<div  class="msg msg-info" style="margin-top:10px;">
  <p>Before you can search for Amazon products you need to fill in the details above provided to you by Amazon once you setup a FREE Amazon AWS account.
            
           <p>You setup this account here: <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html" target="_blank">https://aws-portal.amazon.com/gp/aws/developer/account/index.html</a></p>
            
            <p>Once your account is created simply login and under <b>My Account -> Security Credentials</b> you will find the details above.</p>
            
            
            
            <p>The direct page link is: <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank">https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key</a></p>
             
           
</div>
<?php } ?>
<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" /> 
<input type="hidden" name="adminArray[affiliates_20_TRACK]" value="" />

<div id="amazonsetting"<?php if(get_option("affiliates_20_ID") == "" || get_option("amazon_KEYID") == "" || get_option("amazon_SECRET") == ""){ }else{ ?> style="display:none;"<?php } ?>>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Amazon Affiliate ID</span>	
 # <input name="adminArray[affiliates_20_ID]" type="text" class="ppt-forminput" value="<?php echo get_option("affiliates_20_ID"); ?>" />
<div class="clearfix"></div>
<small>The affiliate ID is used to ensure you get comission from all sales made.<br /> <a href="https://affiliate-program.amazon.com/gp/associates/network/main.html" target="_blank">Don't have an ID? Setup an account here.</a></small>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Amazon Access Key ID</span>	
# <input name="adminArray[amazon_KEYID]" type="text" class="ppt-forminput" value="<?php echo get_option("amazon_KEYID"); ?>" /> 
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Amazon Secret Access Key</span>	
# <input name="adminArray[amazon_SECRET]" type="text" class="ppt-forminput" value="<?php echo get_option("amazon_SECRET"); ?>" /> 
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Amazon Auto Updater</span>	
  <select   name="adminArray[enabled_amazon_updater]" class="ppt-forminput">
        <option value="yes">Enable</option>
        <option value="no" <?php if(get_option('enabled_amazon_updater') == "no"){ print "selected='selected'"; } ?>>Disable</option>
        </select><br />
        <a href="admin.php?page=import_products&action=amazon-debug">Click here to debug</a>
<div class="clearfix"></div>
</div>





 

    <div class="ppt-form-line">	
<span class="ppt-labeltext">Buy Now Buttons</span>	
<select name="adminArray[display_single_amazonbutton]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("display_single_amazonbutton") =="yes"){ print "selected";} ?>>Enable</option>
				<option value="no" <?php if(get_option("display_single_amazonbutton") =="no"){ print "selected";} ?>>Disable</option>
			</select>
            
<div class="clearfix"></div>
 
</div>    
       


<div class="ppt-form-line">	
<span class="ppt-labeltext">Amazon Checkout</span>	
<select name="adminArray[display_amazon_checkout]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("display_amazon_checkout") =="yes"){ print "selected";} ?>>Enable</option>
				<option value="no" <?php if(get_option("display_amazon_checkout") =="no"){ print "selected";} ?>>Disable</option>
			</select>            
<div class="clearfix"></div> 
  <?php
 $S1C = get_option('enabled_amazon_updater_country');
 ?>
<select name="adminArray[enabled_amazon_updater_country]" class="ppt-forminput" style="margin-left:140px;">
<option value="com" <?php if($S1C == "com"){ print "selected='selected'"; } ?>>United States (.com Store)</option>
<option value="ca" <?php if($S1C == "ca"){ print "selected='selected'"; } ?>>Canada (.ca Store)</option>
<option value="co.uk" <?php if($S1C == "co.uk"){ print "selected='selected'"; } ?>>United Kingdom (.co.uk Store)</option>
<option value="fr" <?php if($S1C == "fr"){ print "selected='selected'"; } ?>>France (.fr Store)</option>
<option value="de" <?php if($S1C == "de"){ print "selected='selected'"; } ?>>Germany (.de Store)</option>
<option value="jp" <?php if($S1C == "jp"){ print "selected='selected'"; } ?>>Japan (.jp Store)</option>
<option value="it" <?php if($S1C == "it"){ print "selected='selected'"; } ?>>Italy (.it Store)</option>
<option value="es" <?php if($S1C == "es"){ print "selected='selected'"; } ?>>Spain (.es Store)</option> 
</select>
 

<small>Amazon checkout allows the user to add Amazon products to their basket and then checkout at Amazon. The store you select above will be used to determine where the user will be sent to checkout. Note, only products that are imported from that store will work. For example, you cannot import Amazon products from amazon.co.uk and checkout at amazon.com.</small>
</div> 

   <p><input class="premiumpress_button" type="submit" value="Update Settings" style="color:white;"/></p>
   
</div>

<a href="javascript:void(0);" onclick="toggleLayer('amazonsetting');" class="ppt_layout_showme">Show/Hide Options</a>

         
</form>     

</fieldset>
 
<div class="videobox" id="videobox1">
<a href="javascript:void(0);" onclick="PlayPPTVideo('BdJ96Cj325c','videobox1');"><img src="<?php echo $GLOBALS['template_url']; ?>/PPT/img/admin/video/6.jpg" align="absmiddle" /></a>
</div>



</div>

<div class="clearfix"></div>


 
 
            
            
                  
    

        

 
 
            
        
 
            
           
      


            <?php
            
            $ASS 	= get_option("AmazonSavedSearch_Data");
		 
            if(is_array($ASS) && count($ASS) > 0){
            
            ?>
            
            <div id="premiumpress_box1" class="premiumpress_box premiumpress_box-100" style="margin-top:40px;">
            <div class="premiumpress_boxin"><div class="header">
            <h3><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/new/block7.png" align="middle"> My Scheduled Searches</h3>
            </div><div id="premiumpress_tab1" class="content">
            <table cellspacing="0"><thead>
            
            <td class="tc">Search Title</td>
            <th>Status</th>
            <td class="tc">Last Run</td>
            <td class="tc">Total Imported</td>
            <td class="tc">Actions</td></tr></thead><tfoot><tr><td colspan="6"></td></tr></tfoot><tbody>
            
            <?php
            $i= 0;
            foreach($ASS as $key => $value){ 
            ?> 
            <tr class="first">
            <td class="tc"><b><?php if(isset($value['name'])){ echo $value['name']; } ?></b></td>
            <td class="tc"><?php echo strip_tags($value['status']); ?> <br><small>Keyword used <?php if(isset($value['keyword'])){ echo str_replace("\\","",$value['keyword']); } ?></small></td>
            <td><?php echo $value['last']; ?> <br><small>Search setup to run <?php if(isset($value['time'])){ echo $value['time']; } ?>, now on page <?php echo $value['start_page']; ?></small></td>
            <td class="tc"><?php echo $value['total']; ?></td>
            <td class="tc">
            
            <ul class="actions">
            <li><a href="admin.php?page=import_products&runnow=<?php echo $i; ?>">Run Now</a> | </li>
            
            <li><a href="admin.php?page=import_products&delf=<?php echo $i; ?>">Delete</a></li>
            
            </ul></td>
            </tr>
            <?php $i++; }  ?>
            
            </td></tr></tbody>
             
            
            </table>
            </div>
            </div>
            <div class="clearfix"></div> </div> 
            <?php } ?>


 

 
</div>




































<div id="premiumpress_tab2" class="content">


<div class="grid400-left">

<fieldset>
<div class="titleh"> <h3>Ebay Search</h3></div>
 

<form method="post"  target="upload_target_ebay" action="<?php echo get_template_directory_uri(); ?>/admin/importtools/_ebay_results.php" enctype="multipart/form-data" onSubmit="jQuery('#searchresultsform_ebay').show();window.frames['upload_target_ebay'].document.body.innerHTML='<br>Loading results, please wait...';">			
<input type="hidden" name="feed" value="1">
<input type="hidden" name="feed_path" value="<?php echo $ff[0]; ?>">
<input type="hidden" name="web_path" value="<?php echo PPT_THEME_URI; ?>">
		
<input type="hidden" name="ebay" value="1" />
<input type="hidden" name="start_page" value="1" />
 <input type="hidden" name="amazon[start_page]" value="1">
 
<div class="ppt-form-line">	
<span class="ppt-labeltext">Search Keyword</span>
<input name="keyword" type="text" class="ppt-forminput">            
<div class="clearfix"></div> 
</div>

<?php if(strtolower(PREMIUMPRESS_SYSTEM) == "comparisonpress"){ ?>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Import Type</span>
<select class="ppt-forminput" onchange="toggleLayer('matchDiv')">
<option value="" selected>As Normal Product</option>
<option value="m">As Compared Product</option>
</select>
<div style="display:none; margin-left:140px;" id="matchDiv">
<small>Enter the PARENT product ID to link with.</small>
<input name="matchID" type="text" class="ppt-forminput" id="matchID"> 
</div>
<script type="text/javascript">
jQuery('#matchID').smartSuggest({  src: '<?php echo get_template_directory_uri();?>/PPT/ajax/actions.php?action=suggest'});</script>
<div class="clearfix"></div> 
</div>
<?php } ?>


<div id="ebay1" style="display:none;"> 

<div class="ppt-form-line">	
<span class="ppt-labeltext">eBay Store</span>
  <select name="ebay_globalid" class="ppt-forminput">
        <option value="EBAY-US" selected>eBay USA</option>
        <option value="EBAY-SG">eBay Singapore</option>
        <option value="EBAY-PL">eBay Poland</option>
        <option value="EBAY-PH">eBay Philippines</option>
        <option value="EBAY-NLBE">eBay Belgium (Dutch)</option>
        <option value="EBAY-NL">eBay Netherlands</option>
        <option value="EBAY-MY">eBay Malaysia</option>
        <option value="EBAY-MOTOR">eBay Motors</option>
        <option value="EBAY-IT">eBay Italy</option>
        <option value="EBAY-IN">eBay India</option>
        <option value="EBAY-IE">eBay Ireland</option>
        <option value="EBAY-HK">eBay Hong Kong</option>
        <option value="EBAY-GB">eBay UK</option>
        <option value="EBAY-FRCA">eBay Canada (French)</option>
        <option value="EBAY-FRBE">eBay Belgium (French)</option>
        <option value="EBAY-FR">eBay France</option>
        
        <option value="EBAY-ES">eBay Spain</option>
        <option value="EBAY-ENCA">eBay Canada (English)</option>
        <option value="EBAY-CH">eBay Switzerland</option>
        <option value="EBAY-DE">eBay Germany</option>
        <option value="EBAY-AU">eBay Australia</option>
        <option value="EBAY-AT">eBay Austria</option>    
 
        </select>           
<div class="clearfix"></div> 
</div> 

<script type="application/javascript">
function ebayChangeType(val){

if(val == "findItemsIneBayStores"){
jQuery('#sstore').show();
}else{
jQuery('#sstore').hide();
}

}
function ebayCT(val,id){

if(val =="BestOfferOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="CharityOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="Condition"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>New</option><option>Used</option><option>Unspecified</option></select>';
}else if(val =="Currency"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>AUD</option><option>CAD</option><option>CHF</option><option>CNY</option><option>EUR</option><option>GBP</option><option>HKD</option><option>INR</option><option>MYR</option><option>PHP</option><option>PLN</option><option>SEK</option><option>SGD</option><option>TWD</option><option>USD</option></select>';
}else if(val =="EndTimeFrom"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="<?php echo date('Y-d-m')."T".date('h:i:s'); ?>">';
}else if(val =="EndTimeTo"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="<?php echo date('Y-d-m')."T".date('h:i:s'); ?>">';
}else if(val =="ExcludeAutoPay"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="ExcludeCategory"){
document.getElementById(id).innerHTML = '<input type="text" class="ppt-forminput" name="filter[value][]" value="">';
}else if(val =="ExcludeSeller"){
document.getElementById(id).innerHTML = '<input type="text" class="ppt-forminput" name="filter[value][]" value="">';
}else if(val =="ExpeditedShippingType"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>Expedited</option><option>OneDayShipping</option></select>';
}else if(val =="FeaturedOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="FeedbackScoreMax"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="" style="width:50px;">(numeric value)';
}else if(val =="FeedbackScoreMin"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="" style="width:50px;">(numeric value)';
}else if(val =="FreeShippingOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="GetItFastOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="HideDuplicateItems"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="ListedIn"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>EBAY-AT</option><option>EBAY-AU</option><option>EBAY-CH</option><option>EBAY-DE</option><option>EBAY-ENCA</option><option>EBAY-ES</option><option>EBAY-FR</option><option>EBAY-FRBE</option><option>EBAY-FRCA</option><option>EBAY-GB</option><option>EBAY-HK</option><option>EBAY-IE</option><option>EBAY-IN</option><option>EBAY-IT</option><option>EBAY-MOTOR</option><option>EBAY-MY</option><option>EBAY-NL</option><option>EBAY-NLBE</option><option>EBAY-PH</option><option>EBAY-PL</option><option>EBAY-SG</option><option>EBAY-US</option></select>';
}else if(val =="ListingType"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>Auction</option><option>AuctionWithBIN</option><option>Classified</option><option>FixedPrice</option><option>StoreInventory</option><option>All</option></select>';
}else if(val =="LocalPickupOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="LocalSearchOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="LotsOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="MaxBids"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="" style="width:50px;">(numeric value)';
}else if(val =="MaxDistance"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="buyerPostalCode" value="" style="width:100px;">(My Postcode)<br><input type="text" class="ppt-forminput" name="filter[value][]" value="" style="width:50px;">(distance: numeric value)';
}else if(val =="MaxHandlingTime"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="" style="width:50px;">(numeric value)';
}else if(val =="MaxPrice"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="1000.00" style="width:100px;">(decimal value)';
}else if(val =="MaxQuantity"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="1" style="width:50px;">(numeric value)';
}else if(val =="MinBids"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="1" style="width:50px;">(numeric value)';
}else if(val =="MinPrice"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="00.00" style="width:100px;">(decimal value)';
}else if(val =="MinQuantity"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="1" style="width:50px;">(numeric value)';
}else if(val =="ModTimeFrom"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="<?php echo date('Y-d-m')."T".date('h:i:s'); ?>">';
}else if(val =="PaymentMethod"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>PayPal</option><option>PaisaPay</option><option>PaisaPayEMI</option></select>';
}else if(val =="ReturnsAcceptedOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="Seller"){
document.getElementById(id).innerHTML ='<input type="text" class="ppt-forminput" name="filter[value][]" value="">';
}else if(val =="SellerBusinessType"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>Business</option><option>Private</option></select>';
}else if(val =="SoldItemsOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="TopRatedSellerOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="ValueBoxInventory"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';
}else if(val =="WorldOfGoodOnly"){
document.getElementById(id).innerHTML ='<select class="ppt-forminput" name="filter[value][]"><option>true</option><option>false</option></select>';     
}
 

}
</script>
<?php
 $filterArray = array("",
"BestOfferOnly",
"CharityOnly",
"Condition",
"Currency",
"EndTimeFrom",
"EndTimeTo",
"ExcludeAutoPay",
"ExcludeCategory",
"ExcludeSeller",
"ExpeditedShippingType",
"FeaturedOnly",
"FeedbackScoreMax",
"FeedbackScoreMin",
"FreeShippingOnly",
"GetItFastOnly",
"HideDuplicateItems",
"ListedIn",
"ListingType",
"LocalPickupOnly",
"LocalSearchOnly",
 
"LotsOnly",
"MaxBids",
"MaxDistance",
"MaxHandlingTime",
"MaxPrice",
"MaxQuantity",
"MinBids",
"MinPrice",
"MinQuantity",
"ModTimeFrom",
"PaymentMethod",
"ReturnsAcceptedOnly",
"Seller",
"SellerBusinessType",
"TopRatedSellerOnly",
"ValueBoxInventory",
"WorldOfGoodOnly");
?>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Find Products By</span>
<select name="findby" class="ppt-forminput" style="width:200px;" onchange="ebayChangeType(this.value);">           
            <option value="findItemsByKeywords"  selected> Items By Keywords </option> 
              <option value="findItemsIneBayStores">  Items In eBay Stores ( search a specific store )</option>          
            <option value="findItemsByProduct"> Items By Product ( enter product ID, such as an ISBN, UPC, EAN, or ePID )</option>              
            </select>             
<div class="clearfix"></div> 
</div> 

<div class="ppt-form-line" id="sstore" style="display:none;">	
<span class="ppt-labeltext">Ebay Store Name</span>
 <input name="storeName" type="text" value="" class="ppt-forminput">           
<div class="clearfix"></div> 
</div> 



<?php $g=0; while($g < 10){ ?>
<div  <?php if($g !=0){ ?>style="display:none"<?php } ?> id="f<?php echo $g; ?>">
<div class="ppt-form-line">	
<span class="ppt-labeltext">Search Filter</span>
<select name="filter[name][]" onchange="ebayCT(this.value,'filterId<?php echo $g; ?>')">
<?php  foreach($filterArray as $val){ echo "<option value='".$val."'>".$val."</option>"; } ?>
</select>
 <div style="margin-left:140px;"><small>Enter Filter Value (<a href="http://developer.ebay.com/devzone/finding/callref/types/ItemFilterType.html" target="_blank">more info</a>)</small><br />
 <div id="filterId<?php echo $g; ?>"></div> 
 </div>          
<div class="clearfix"></div> 
</div>
<div id="bb<?php echo $g; ?>"> 
<div class="ppt-form-line">	<a href="javascript:void(0);" onclick="toggleLayer('f<?php echo $g+1; ?>');jQuery('#bb<?php echo $g; ?>').hide();"  style="background:green; color:white; padding:4px;margin-left:10px; float:right; margin-right:20px;">Add New Filter</a><div class="clearfix"></div> 
</div>
</div>
</div>
<?php $g++; } ?>

 

<div class="ppt-form-line">	
<span class="ppt-labeltext">Sort By</span>
<select class="input" name="sortOrder" class="ppt-forminput">
            <option value="BestMatch" selected> Best Match </option>
            <option value="CurrentPriceHighest"> Current Price (Highest) </option>            
            <option value="PricePlusShippingHighest"> Price Plus Shipping (Highest) </option>
            <option value="PricePlusShippingLowest"> Price Plus Shipping (Lowest) </option>
            <option value="EndTimeSoonest"> End Time (Soonest) </option>            
            <option value="StartTimeNewest"> Start Time (Newest) </option>       
            </select>             
<div class="clearfix"></div> 
<small>Note: Store names are case sensitive. Also, if the store name contains an ampersand (&), you must use the & character entity (& amp;) in its place.</small>
</div> 

<div class="titleh"> <h3>Save Products</h3></div>
 

<?php $taxArray = get_option("ppt_custom_tax");  

if(is_array($taxArray)){ 
	foreach($taxArray as $tax){	
	
	 
	 if(strlen($tax['title']) > 3){
	 
	 
		$NewTax = strtolower(htmlspecialchars(str_replace(" ","-",str_replace("&","",str_replace("'","",str_replace('"',"",str_replace('/',"",str_replace('\\',"",strip_tags($tax['name'])))))))));
 		   
		  $terms = get_terms($NewTax,array("hide_empty"=>false));
			 $count = count($terms);
			 if ( $count > 0 ){
			  	echo "<div class='ppt-form-line'><span class='ppt-labeltext'>".$tax['title']."</span>";
				 echo "<select name='tax[".$NewTax."][]' multiple='multiple' size=5 style='font-size:14px; height:100px;'><option value=''>-----------------------------------</option>";
				 foreach ( $terms as $term ) {
			 
				   echo "<option value='".$term->term_id. "'>" . $term->name . "</option>";					
				 }
				 echo "</select><div class='clearfix'></div></div>";
			 }
    		  
    	 }	 
	 }
	 
	  }



?>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Imported Category</span>
<select name="cat[]" multiple size="5" style="font-size:14px; height:200px; " class="ppt-forminput">
<?php echo premiumpress_categorylist('',false,false,"category",0,true); ?>
 </select>            
<div class="clearfix"></div> 
</div> 
<div class="titleh"> <h3>Ebay  Connection Settings</h3></div>
 
<div class="ppt-form-line">	
<span class="ppt-labeltext">Ebay API ID</span>
 <input name="ebay_api" type="text" value="<?php echo get_option("ebay_api"); ?>" class="ppt-forminput"><br />
<div class="clearfix"></div> 
<small>You get this ID from your <A href="https://developer.ebay.com/join/default.aspx" target="_blank">Ebay developer account</A>. Eg: MarkAnth-921e-44b0-81ce-642804e36157</small>            
</div> 
 
</div> 
 <a href="javascript:void(0);" onclick="toggleLayer('ebay1');" class="ppt_layout_showme" style="float:right;">Show/Hide Options</a>

<p><input class="premiumpress_button" type="submit" value="Start Search" style="color:white;" /></p>
 
</form>

</fieldset>


</div><div class="grid400-left last">

<fieldset id="searchresultsform_ebay" style="display:none;">
<div class="titleh"> <h3>Search Results</h3></div>
<iframe id="upload_target_ebay" name="upload_target_ebay" src="<?php echo PPT_THEME_URI; ?>/admin/importtools/index.html" style="width:390px;height:600px;border:0px solid #fff; margin-left:0px;margin-top:10px; "></iframe>
</fieldset>

<div class="videobox" id="ebayvideo"> 
<a href="javascript:void(0);" onclick="PlayPPTVideo('fRA9dukijGs','ebayvideo');"><img src="<?php echo $GLOBALS['template_url']; ?>/PPT/img/admin/video/15.jpg" align="absmiddle" /></a>
</div> 

</div>
<div class="clearfix"></div>

</div>


































<div id="premiumpress_tab3" class="content">


<div class="grid400-left">

<fieldset>
<div class="titleh"> <h3>DataFeedr Settings</h3></div>


<?php if(defined('DFR_PLUGIN_VERSION') ){ ?>

 
 

    <div style="margin-left:20px; background:#efefef; padding:10px; margin-right:20px; margin-bottom:20px;">
    
    <h2>Datafeedr Integration Overview</h2>
    
    <p>Importing your Datafeedr products into your website is easy!</p>
    
    <p>Using the Datafeedr plugin you can ensure your products are constantly updated and well managed, our theme then takes any product you import using the Datafeedr plugin and turns them into store products or posts.</p>
    
    <p>You currently have <b><?php echo $PPTDatafeedr->COUNTPRODUCTS(1); ?></b> Datafeedr products in your database. </p>
    
    <p><a href="admin.php?page=import_products&action=datafeedr">click here to import and/or update your Datafeedr imported store products.</a></p>
    
    
    </div>

 

<?php }else{ ?>

<h1>Affiliate Stores Using Datafeedr</h1>
<h3>Datafeedr allows you to instantly generate thousands of store products which you earn big $$$ for each sale!</h3>
<p>Whether you're selling your own products or selling affiliate products, integrating a datafeedr product list can help generate extra revenue each month! </p>
<p>ShopperPress makes it quick and easy to integrate Datafeedr, simply signup with Datafeedr, create a product list, download the file and import it below!</p>
<p><a href="http://shopperpress.com/link/data-feedr/" target="_blank">Signup Now with Datafeedr and start earning an extra income right away!</a></p>


   <b style="font-size:12px;">Datafeedr Plugin <u>NOT</u> Detected</b> <br/> <br/> 
  
    <div class="msg msg-error" ><p>
	<span id="show_wp0"><a href="http://shopperpress.com/link/data-feedr/" target="_blank" style="font-weight:bold; text-decoration:underline">Missing Plugin</a></span>
    </div>
 
    
    
    <div class="clearfix"></div>
    
    <small style="font-size:12px;">You must install the datafeedr plugin to use the Datafeedr features. <a href="http://shopperpress.com/link/data-feedr/" target="_blank">Click here to download</a> </small>

 
<?php } ?> 


</fieldset>

</div>
<div class="grid400-left last">

<div class="videobox" id="videobox55" style="margin-bottom:10px;">
<a href="javascript:void(0);" onclick="PlayPPTVideo('pelg7FUB-oY','videobox55');"><img src="<?php echo $GLOBALS['template_url']; ?>/PPT/img/admin/video/8.jpg" align="absmiddle" /></a>
</div> 

</div>
<div class="clearfix"></div> 





 




 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 







</div>
<div id="premiumpress_tab4" class="content">
<div style="padding:10px;">
<div class="msg msg-info">
<p>The Affiliate ID section allows you to store all of your affiliate details and tracking code to be use with your products and website links.</p>
</div>
</div>
<form method="post" name="ShopperPress">
<input name="submitted" type="hidden" value="yes" />

<table class="maintable" style="style="background:white;"">

<tr class="mainrow">
<td class="titledesc">Select Affiliate Network</td>
<td class="forminp">

<select style="font-size:16px; width:250px;" onchange="toggleLayer('a_'+this.value);">
<option value="All" selected>-- Select Network --</option>
<option value="1">ShareASale </option>
<option value="2">Affiliate Future</option>
<option value="3">LinkShare</option>
<option value="4">RegNow</option>
<option value="5">Commission Junction</option>
<option value="6">Webgains</option>

<option value="7">ClickBank</option>
<option value="8">TradeDoubler</option>
<option value="9">Bridaluxe</option>
<option value="10">Link Connector</option>

<option value="11">NetShops</option>
<option value="12">Buy.at</option>
<option value="13">PepperJam</option>
<option value="14">Google Affiliate Network</option>
<option value="15">Affiliate Window</option>
<option value="16">Commission Monster</option>

<option value="17">Market Health</option>
<option value="18">Affilinet</option>
<option value="19">OneNetworkDirect</option>
<option value="20">Amazon</option>

<option value="21">TickeFeedr</option>
<option value="25">Ebay</option>
</select>

</td></tr>
 

		<tr class="mainrow" style="display:none" id="a_1">			 
			<td class="titledesc"> ShareASale </td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_1_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_1_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_1_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_1_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_2">					 
			<td class="titledesc">Affiliate Future</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_2_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_2_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_2_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_2_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_3">
			<td class="titledesc">LinkShare</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_3_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_3_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_3_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_3_TRACK"); ?>" /></td>

		<tr class="mainrow"  style="display:none" id="a_4">
			<td class="titledesc">RegNow</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_4_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_4_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_4_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_4_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow"  style="display:none" id="a_5">
			<td class="titledesc">Commission Junction</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_5_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_5_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_5_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_5_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_6">
			<td class="titledesc">Webgains</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_6_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_6_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_6_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_6_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_7">			 
			<td class="titledesc">ClickBank</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_7_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_7_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_7_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_7_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_8">
			<td class="titledesc">TradeDoubler</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_8_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_8_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_8_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_8_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_9">			 
			<td class="titledesc">Bridaluxe</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_9_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_9_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_9_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_9_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_10">	
			<td class="titledesc">Link Connector</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_10_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_10_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_10_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_10_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_11"> 
			<td class="titledesc">NetShops</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_11_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_11_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_11_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_11_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_12">
			<td class="titledesc">Buy.at </td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_12_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_12_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_12_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_12_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_13">
			<td class="titledesc">PepperJam	</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_13_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_13_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_13_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_13_TRACK"); ?>" /></td>
		</tr>
		
		<tr class="mainrow" style="display:none" id="a_14">
			<td class="titledesc">Google Affiliate Network</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_14_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_14_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_14_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_14_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_15">
			<td class="titledesc">Affiliate Window</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_15_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_15_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_15_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_15_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_16">
			<td class="titledesc" style="display:none" id="a_1">Commission Monster</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_16_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_16_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_16_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_16_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_17">
			<td class="titledesc">Market Health</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_17_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_17_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_17_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_17_TRACK"); ?>" /></td>
		</tr>
 
		<tr class="mainrow" style="display:none" id="a_18">
			<td class="titledesc">Affilinet</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_18_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_18_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_18_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_18_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_19">			
			<td class="titledesc">OneNetworkDirect </td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_19_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_19_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_19_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_19_TRACK"); ?>" /></td>
		</tr>
        
        		<tr class="mainrow" style="display:none" id="a_21">			
			<td class="titledesc">TickeFeedr </td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_21_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_21_ID"); ?>" /><br />Tracking ID<input name="adminArray[affiliates_21_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_21_TRACK"); ?>" /></td>
		</tr>

		<tr class="mainrow" style="display:none" id="a_20">		
			<td class="titledesc">Amazon</td>
			<td class="forminp">Affiliate ID<input name="adminArray[affiliates_20_ID]" type="text" style="width:100%" value="<?php echo get_option("affiliates_20_ID"); ?>" /><br />
            <!--Tracking ID<input name="adminArray[affiliates_20_TRACK]" type="text" style="width:100%" value="<?php echo get_option("affiliates_20_TRACK"); ?>" />--></td>
		</tr>
 
 	<tr class="mainrow"  style="display:none" id="a_25">
		<td class="titledesc"></td>
		<td class="forminp">
        <p><b>Ebay Affiliate Tracking ID</b></p>
			<input name="adminArray[ebay_tracking]" type="text" value="<?php echo get_option("ebay_tracking"); ?>" style="width: 500px;"><br />
			<p><b>Ebay Affiliate Custom ID</b></p>
			<input name="adminArray[ebay_customid]" type="text" value="<?php echo get_option("ebay_customid"); ?>" style="width: 500px;"><br />
		 
		</td>
	</tr>
    
  

</table>


 


 
<p><input class="premiumpress_button" type="submit" value="Save Settings" style="color:white;" /></p>

</form>

 
</div>



<div id="premiumpress_tab5" class="content">
 

 
</div>            
	
<div id="premiumpress_tab6" class="content">tab 6</div>            
					 
                        
</div>
</div>
<div class="clearfix"></div> 



<?php } ?>