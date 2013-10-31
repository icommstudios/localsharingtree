<?php

if(isset($_POST['feed'])){

require( $_POST['feed_path'].'/wp-config.php' );
// SHOW DAYS UNTIL EXPIRES
function days_until1($date){
    return (isset($date)) ? floor((strtotime($date) - time())/60/60/24) : FALSE;
}
global $PPT;

	if(!is_array($_POST['cat'])){ die("<h1> You didnt select any category to save the products too </h1> <p> Click back and select a category.</p>"); }
	if($_POST['ebay_api'] ==""){ die("<h1> Ebay Developer API Key Missing</h1><p>You need to enter your ebay developer API key into the configuration settings</p>"); }

if ( get_magic_quotes_gpc() ) {
    $_POST      = array_map( 'stripslashes_deep', $_POST );
}

	$count=0;
	$cats="";
	foreach($_POST['cat'] as $cat){			
	$cats .= $cat.",";
	}

$appid 		= $_POST['ebay_api'];  // Replace with your own AppID
if($_POST['ebay_api'] != ""){
update_option('ebay_api',$_POST['ebay_api']);
}
// API request variables
$endpoint 	= 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
$version 	= '1.0.0';  // API version supported by your application
$globalid 	= $_POST['ebay_globalid'];  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
$query 		= $_POST['keyword'];  // You will need to supply your own query
$SafeQuery 	= urlencode($query);  // Make the query URL-friendly


$pagenum = $_POST['start_page'];

///
$urlfilter="";
$i 	= '0';  // Initialize the item filter index array to 0

$filterArray =
	array(
	
	  array(
		'name' => '',
		'value' => '0',
		'paramName' => 'Currency',
		'paramValue'  => 'USD'),
		
		
	); 


 
// Construct the findItemsByKeywords call 
$apicall = "$endpoint?";
$apicall .= "OPERATION-NAME=".$_POST['findby']; //
$apicall .= "&SERVICE-VERSION=$version";
$apicall .= "&SECURITY-APPNAME=$appid";
$apicall .= "&GLOBAL-ID=$globalid";
$apicall .= "&keywords=$SafeQuery";
$apicall .= "&paginationInput.entriesPerPage=10"; // items per page
$apicall .= "&paginationInput.pageNumber=$pagenum&outputSelector(0)=SellerInfo&outputSelector(1)=StoreInfo"; // page nav
$apicall .= "&sortOrder=".$_POST['sortOrder'];
if(strlen($_POST['storeName']) > 1){
	$apicall .= "&storeName=".$_POST['storeName'];
}
 
$f=0;
  
foreach($_POST['filter'] as $filter){
 
 foreach($filter as $key=> $filterThis){ 
 
 
	if($filterThis == "ListingType123"){
	
		$apicall .= "&itemFilter(".$f.").name=".$filterThis."&itemFilter(".$f.").value=".$_POST['filter']['value'][$key];
	
	}else{ 
	
		if($filterThis !="" && $filterThis  != $_POST['filter']['value'][$key] && isset($_POST['filter']['value'][$key]) && $_POST['filter']['value'][$key] !="123"){
		
		$apicall .= "&itemFilter(".$f.").name=".$filterThis."&itemFilter(".$f.").value=".$_POST['filter']['value'][$key];
		
		}
	
 	}
	$f++;

 }

}

if($_POST['buyerPostalCode']){

$apicall .= "&buyerPostalCode=".$_POST['buyerPostalCode'];
}


//if(strlen($_POST['storeName']) > 1){
$apicall .= "&affiliate.networkId=9";
$apicall .= "&affiliate.trackingId=".get_option("ebay_tracking");
$apicall .= "&affiliate.customId=".get_option("ebay_customid");
//} 
$apicall .= "$urlfilter";
 
// Load the call and capture the document returned by eBay API
$resp = simplexml_load_file($apicall);
 
 
$error = @$resp->errorMessage->error->message;

$GLOBALS['ebay_tracking'] = get_option("ebay_tracking");
$GLOBALS['ebay_customid'] = get_option("ebay_customid");
 
// Check to see if the response was loaded, else print an error
 if (strlen($error) < 5) {  
 
	$results = '';
	
	
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style>
	* { padding:0px; margin:0px; }
	body { padding:0px; margin:0px; font: 12px "Lucida Grande", Verdana, Arial, sans-serif;  margin-right:20px; }
	
	.ppt-form-line {
display: block;
border-bottom: 1px solid #E5E5E5;
padding: 15px 0px;
}

.pagenav { width:170px; margin-top:10px; }
.pagenav li { border:1px solid #ddd; background:#fff; padding:5px; float:left; margin-right:5px; list-style:none; }
.left { float:left; }
.right { float:right; }
	</style> 
    
          <script type='text/javascript' src='<?php echo $_POST['web_path']; ?>/PPT/ajax/actions.js?ver=3.3.1'></script>  

    <script type='text/javascript'>
	
	function gobackpage(page){

	document.getElementById('start_page').value = page -1;
	document.subform.submit();
	
	}
 
	</script>  
    
</head>

<body> 
 
    <div style="background:#efefef; border:1px solid #ddd; padding:9px; font-size:11px;">
		<?php  if(isset($resp->paginationOutput)){ print "<b>".$resp->paginationOutput->totalEntries."</b> results for '".str_replace('*','"',$_POST['keyword'])."' in <b>".$resp->paginationOutput->totalPages."</b> pages.";  } ?>
	</div>
<div id="resultstable">

<p><br />page: <?php echo $resp->paginationOutput->pageNumber; ?></p>
    
    
    <?php
	
	
		$termString="";
	if(is_array($_POST['tax'])){
		foreach($_POST['tax'] as $key=>$val){
		 
			$termString .= $key."-".$val[0]."*";
		}	
	}

    if(isset($resp->searchResult->item)){ foreach($resp->searchResult->item as $item) {
	
	//die(print_r($resp->searchResult->item));
 
		$ID    		= $item->itemId;
        $pic   		= $item->galleryURL;
        $link  		= $item->viewItemURL;
        $title 		= $item->title;
		$subtitle   = $item->subtitle;
  		$location	= $item->location;
		$postcode 	= $item->postalCode;
		$cat_name 	= $item->primaryCategory->categoryName;

		$price = $item->sellingStatus->currentPrice;
		$type = $item->listingInfo->listingType;
		
		 
		$startTime = $item->listingInfo->startTime;

		$shippingType = $item->shippingInfo->shippingType;
		$shipping_to = $item->shippingInfo->shipToLocations;
		$shipping_cost = $item->shippingInfo->shippingServiceCost;
		
		$listing_endtime = explode(":",$item->listingInfo->endTime);
		
		$endTime = substr($listing_endtime[0],0,-3);
		
		$storename = $item->storeInfo->storeName;
		
		$feedbackStar = $item->storeInfo->feedbackRatingStar;
		
		$sellerUsername = $item->storeInfo->sellerUserName;
		
		

	?>
    
    <div id="A<?php echo $ID; ?>">
    
    
    <div class="ppt-form-line">  
    
     <a href="<?php echo $pic; ?>" target="_blank"><img src="<?php echo $pic; ?>" style="float:left; padding-right:20px;  max-height:60px; max-width:60px; padding-bottom:10px;"></a>
   
    
    <p><b><?php echo $title; ?></b></p><br />
    <p> <?php echo number_format((int)$price,2); ?> / Type: <?php echo $type; ?>  <?php if(strlen($storename) > 2){ echo " / Store Name: ".$storename; } ?> </p>
    <div style="clear:both;"></div>
    </div>
    <div style="clear:both;"></div>
    
    <div class="ppt-form-line"> 
     
    
    <p>   <small>Auction Ends: <?php $fg = days_until1($endTime); if($fg  <= 0){ echo "Today"; }else{ echo $fg." days";} ?>  / Location: <?php echo $location; ?> </small>  </p><br />
    
    
    
    <p>[<a href='javascript:void(0);' onclick="getElementById('div<?php echo $ID; ?>').innerHTML='saving product, please wait...';document.getElementById('A<?php echo $ID; ?>').style.display = 'none';addEbayProduct('<?php echo $cats; ?>','<?php echo $_POST['ebay_globalid']; ?>','<?php echo $ID; ?>','<?php echo $_POST['ebay_api']; ?>','<?php echo $GLOBALS['ebay_tracking']; ?>','<?php echo $GLOBALS['ebay_customid']; ?>','<?php echo str_replace("http://","",$_POST['web_path']); ?>/PPT/ajax/','div<?php echo $ID; ?>','<?php echo $termString; ?>','<?php echo $_POST['matchID'] ?>');">Add Product</a>] 
    
    [ <a href="<?php echo $link; ?>" target="_blank">View Product</a>]</p>
    
    
    <div style="clear:both;"></div> 
    
    </div>
    
    </div>
    <div id="div<?php echo $ID; ?>" style="font-size:12px; font-weight:bold; background:#e6ffd7;color:#296900; margin-bottom:5px;"></div>
    <?php } } //Shipping To: ".$shipping_to." Cost: ".$shipping_cost." ?>
    
 
    
    
    <?php
 
	 
}
// If there was no response, print an error
else {
	echo "<h1>".$error."</h1>";
}
?> 

 
    <div style="background:#eee; border:1px solid #ddd; padding:10px; margin-top:10px; font-size:12px;"> 
  
    <?php if($_POST['start_page'] > 1){ ?><a href="javascript:void(0);" onclick="gobackpage(<?php echo $_POST['start_page']; ?>)">Previous Page</a>  <?php }else{ echo "&nbsp;&nbsp;"; } ?>  
    
     <a href="#" onclick="getElementById('resultstable').innerHTML='<br>Loading results, please wait...'; document.subform.submit();" style="float:right;">Next Page</a> </div>
    
	<div style="clear:both;"></div>
    
    </div>
    
    
    <?php  
	
	$cpage = $_POST['start_page']; 
	$i=$cpage;
	
	
	echo '<ul class="pagenav right">';
	while($i < $cpage+5){ ?>
    <li><a href="#" onclick="getElementById('start_page').value='<?php echo $i; ?>';getElementById('resultstable').innerHTML='<br>Loading results, please wait...'; document.subform.submit();" style="float:right;"><?php echo $i; ?></a></li>
    <?php $i++; }
	echo '</ul>';
	
	
	$i=$cpage-5; 
	echo '<ul class="pagenav left">';
	while($i < $cpage){ if($i > 1){?>
    <li><a href="#" onclick="getElementById('start_page').value='<?php echo $i; ?>';getElementById('resultstable').innerHTML='<br>Loading results, please wait...'; document.subform.submit();" style="float:right;"><?php echo $i; ?></a></li>
    <?php } $i++; }
	echo '</ul>'; 	
	
	?>
    
    
    

    <script>
	
	function gobackpage(page){

	document.getElementById('start_page').value = page -1;
	document.subform.submit();
	
	}
	
	</script> 
        
    <form method="post" target="_self" id="subform" name="subform">			
    <input type="hidden" name="feed" value="1">
	<input type="hidden" name="feed_path" value="<?php echo $_POST['feed_path']; ?>">

    <?php
 $keyArray = array();
	foreach($_POST as $key=>$val){
	 
	
		if($key == "filter"){
		
			foreach($_POST['filter'] as $filter){
			 
			 foreach($filter as $key=> $filterThis){ 
				
					 if($filterThis !="" && $filterThis  != $_POST['filter']['value'][$key] && isset($_POST['filter']['value'][$key]) && $_POST['filter']['value'][$key] !="123" && !in_array($filterThis, $keyArray) ){
					
					echo '<input type="hidden" name="filter[name][]" value="'.$filterThis.'">';
					echo '<input type="hidden" name="filter[value][]" value="'.$_POST['filter']['value'][$key].'">';
					
					$keyArray[] = $filterThis;	
					  
					
					 }
			
			}
			
		}
		
	
		}elseif($key != "filter" && is_array($val)){
		
			foreach($val as $key=>$val1){ if($key !== "start_page"){	 
				 print '<input type="hidden" name="'.$key.'" value="'.$val1.'">';			 
			} }
		
		
		}else{

			if($key == "start_page"){	
				if($val ==""){ $val=1; }else{ $val++; }	
				print '<input type="hidden" name="'.$key.'" value="'.$val.'" id="start_page">';	
			}elseif($key !== "start_page"){	
				print '<input type="hidden" name="'.$key.'" value="'.$val.'">';
			}
		}

	}
	
	foreach($_POST['cat'] as $cat){			
		print '<input type="hidden" name="cat[]" value="'.$cat.'">';	
	}
	
	if($_POST['buyerPostalCode']){
		print '<input type="hidden" name="buyerPostalCode" value="'.$_POST['buyerPostalCode'].'">';	 
	}
 
	
	?>
	
    </form>
    
</body>
</html>
<?php } ?>     