<?php

if(isset($_POST['feed'])){

 

require( $_POST['feed_path'].'/wp-config.php' );

	global $PPT;

	require_once (TEMPLATEPATH ."/PPT/class/class_amazon.php");		
	$obj = new AmazonProductAPI();		

	if ( get_magic_quotes_gpc() ) {
		$_POST      = array_map( 'stripslashes_deep', $_POST );
	}

	if(!is_array($_POST['cat']) || $_POST['cat'][0] =="0"){ die("<h1> You didnt select any category to save the products too </h1>"); }

	if($_POST['amazon']['keyword'] == ""){ die("<h1>You forgot to enter a search keyword.</h1>"); }

	// IF THIS IS A SAVED SEARCH LETS SAVE IT
	if(isset($_POST['schedule']) && $_POST['amazon']['keyword'] !=""){
	
		if($_POST['schedule']['time'] != ""){
		
				if(isset($_POST['cat'][0])){
				 
				$obj->AmazonSavedSearch();
				
				die("<h1>Schedule Search Saved Successfully</h1><p>This search will automatically run in the background on the time period set.</p>");
				
				}
		
		}
	}



	$count=0;
	$cats="";
	foreach($_POST['cat'] as $cat){			
	$cats .= $cat.",";
	}			
	
	$_POST['amazon']['keyword'] = str_replace('*','"',$_POST['amazon']['keyword']);
	
		
	try
    {
		$result = $obj->searchProducts($_POST['amazon']['keyword'],$_POST['amazon']['keyword_cat'],$_POST['amazon']);	

	}
    catch(Exception $e)
    {

        echo $e->getMessage();
		exit();		
    }
	
	$TotalResults = str_replace("!ad","",$result->Items->TotalResults);
	$TotalPages	  = str_replace("!ad","",$result->Items->TotalPages);
	 
	 
					
	$_POST['amazon']['keyword'] = str_replace('"',"*",$_POST['amazon']['keyword']);
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
		<?php  if(isset($TotalResults)){ print "<b>".number_format($TotalResults)."</b> results for '".str_replace('*','"',$_POST['amazon']['keyword'])."' in <b>".number_format($TotalPages)."</b> pages.";  } ?>
	</div>
<div id="resultstable">
    
    <?php 
	
	$termString="";
	if(is_array($_POST['tax'])){
		foreach($_POST['tax'] as $key=>$val){
		 
			$termString .= $key."-".$val[0]."*";
		}	
	}

	foreach($result->Items->Item as $val){
	
	
	//print_r($val);
	
	//HasReviews
	 //die(print_r($val));
	 
	//$data['totalReviews'] 	= $val->CustomerReviews->TotalReviews;
	$data['image'] 			= $val->LargeImage->URL;
	$data['thumbnail']		= $val->MediumImage->URL;
	$data['desc']			= $val->EditorialReviews->EditorialReview->Content;
	$data['title'] 			= $val->ItemAttributes->Title;
	$data['asin'] 			= $val->ASIN;
	$data['url'] 			= $val->DetailPageURL;
	$data['old_price'] 		= "";
	$data['price']			= $val->ItemAttributes->ListPrice->Amount;
	$data['CurrencyCode']	= $val->ItemAttributes->ListPrice->CurrencyCode;
	
	
	// IMAGE SETS	
	if(isset($val->ImageSets->ImageSet)){	
	 
	$i=1;
		foreach($val->ImageSets->ImageSet as $img){ 
			$data['images'] .= $img->LargeImage->URL.",";
		$i++;  }
	}	
	
	
	
	
	if($data['price'] == ""){
		$data['price'] = $val->OfferSummary->LowestNewPrice->Amount;
	}
	
	if(isset($val->Offers->Offer->OfferListing->Price->Amount) && strlen($val->Offers->Offer->OfferListing->Price->Amount) > 1){
	
			$data['old_price'] = $data['price'];
			$data['price'] 	=  $val->Offers->Offer->OfferListing->Price->Amount;	
	}	
	
	 
 
	// Load price options
	if($_POST['amazon']['country'] == "jp"){
	
	}else{
		$data['old_price'] = number_format(substr($data['old_price'],0, -2)).".".substr($data['old_price'],-2);
		if($data['price'] == ".99"){ $data['price'] = "0.99"; }else{
 		$data['price'] = @number_format(substr($data['price'],0, -2)).".".substr($data['price'],-2);
		}
	}	
	 
 	if(strlen($data['old_price']) < 3){ $data['old_price']=""; }
			
	$AFFLINK = "http://www.amazon.".$_POST['amazon']['country']."/o/ASIN/%asin%/%amazon_id%";
	$AFFLINK = str_replace("%asin%",$data['asin'],$AFFLINK);
	$AFFLINK = str_replace("%amazon_id%",'YOURUSERID',$AFFLINK);
	
	
	if($data['price'] != "" && $data['price'] != 0){
	?>  
    
    
    
    <div id="A<?php echo $data['asin']; ?>" style="clear:both;">
    
    <div class="ppt-form-line">    
    <a href="<?php echo $data['image']; ?>" target="_blank"><img src="<?php echo $data['thumbnail']; ?>" style="float:left; max-width:60px; max-height:60px; padding-right:20px; padding-bottom:30px; "/></a>
    
    <b><?php echo $data['title']; ?></b>
    <p style="font-size:10px;"><?php echo strip_tags(substr($data['desc'],0,200)); ?>... <a href="<?php echo $data['url']; ?>" target="_blank">View Item</a></p>
    </div>
    
    <div style="clear:both;"></div>
    <div class="ppt-form-line">  
    
   <?php echo $data['CurrencyCode']." ".$data['price']; ?>  / Reviews: <?php if($val->CustomerReviews->HasReviews){ echo "Yes"; }else{ echo "No"; } ?> /
    
    
   <a href='javascript:void(0);' onclick="getElementById('Alert<?php echo $data['asin']; ?>').innerHTML='saving product, please wait...'; addAmazonProduct('<?php echo $cats; ?>','<?php echo $_POST['amazon']['country']; ?>','<?php echo $data['asin']; ?>','<?php echo str_replace("http://","",$_POST['web_path']); ?>/PPT/ajax/','<?php echo $termString; ?>','');document.getElementById('A<?php echo $data['asin']; ?>').style.display = 'none';">Add To Website</a> 
    
   </div>
     </div>
     
     <div id="Alert<?php echo $data['asin']; ?>" style="font-size:12px; font-weight:bold; background:#e6ffd7;color:#296900; margin-bottom:5px;"></div>
    <?php } } ?>
    
    </div>
  
    <div style="background:#eee; border:1px solid #ddd; padding:10px; margin-top:10px; font-size:12px;">
  <?php if($_POST['amazon']['start_page'] > 1){ ?><a href="javascript:void(0);" onclick="getElementById('resultstable').innerHTML='<br>Loading results, please wait...';gobackpage(<?php echo $_POST['amazon']['start_page']; ?>)">Previous Page</a>   <?php } ?>
    <div style="float:right; text-align:right;"> 
       <a href="#" onclick="getElementById('resultstable').innerHTML='<br>Loading results, please wait...';document.subform.submit();">Next Page</a> </div>
    

    <div style="clear:both;"></div></div>
    

    <?php  
	
	
	
	$cpage = $_POST['amazon']['start_page']; 
	$i=$cpage;
	
	if($_POST['amazon']['keyword_cat'] != "All" || ( $_POST['amazon']['keyword_cat'] == "All" && $_POST['amazon']['start_page'] < 4 ) ){ 
	echo '<ul class="pagenav right">';
	while($i < $cpage+5){ if($_POST['amazon']['keyword_cat'] != "All" && $i < 11 || ( $_POST['amazon']['keyword_cat'] == "All" && $i < 5 ) ){ ?>
    <li><a href="#" onclick="getElementById('start_page').value='<?php echo $i; ?>';getElementById('resultstable').innerHTML='<br>Loading results, please wait...'; document.subform.submit();" style="float:right;"><?php echo $i; ?></a></li>
    <?php } $i++;  }
	echo '</ul>';
	}
	 
	$i=$cpage-5; 
	echo '<ul class="pagenav left">';
	while($i < $cpage){ if($i > 1){?>
    <li><a href="#" onclick="getElementById('start_page').value='<?php echo $i; ?>';getElementById('resultstable').innerHTML='<br>Loading results, please wait...'; document.subform.submit();" style="float:right;"><?php echo $i; ?></a></li>
    <?php } $i++; }
	echo '</ul>'; 	
	
	
	?>


    
    <form method="post" target="_self" id="subform" name="subform">			
    <input type="hidden" name="feed" value="1">
	<input type="hidden" name="feed_path" value="<?php echo $_POST['feed_path']; ?>">
    <?php
	
	foreach($_POST['cat'] as $cat){			
		print '<input type="hidden" name="cat[]" value="'.$cat.'">';	
	}
	
	foreach($_POST as $key=>$val){
	if(!is_array($val)){
	print '<input type="hidden" name="'.$key.'" value="'.$val.'">';
	}	
	}
	
	foreach($_POST['amazon'] as $key=>$val){
	
		if(is_array($val)){
		
			foreach($val as $key=>$val1){
				 print '<input type="hidden" name="'.$key.'" value="'.$val1.'">';			 
			}
		
		
		}else{
		
			if($key == "start_page"){	
				if($val ==""){ $val=2; }else{ $val++; }	
				print '<input type="hidden" name="amazon['.$key.']" value="'.$val.'" id="start_page">';	
			}else{	
				print '<input type="hidden" name="amazon['.$key.']" value="'.$val.'">';
			}
		}
	
	}
	
	?>
	</form>
</body>
</html>
<?php } ?>