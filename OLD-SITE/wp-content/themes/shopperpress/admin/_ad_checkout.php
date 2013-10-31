<?php global $wpdb; PremiumPress_Header();  ?>

<style>
select { border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px; }
</style>

<div id="premiumpress_box1" class="premiumpress_box premiumpress_box-100"><div class="premiumpress_boxin"><div class="header">
<h3><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/new/block5.png" align="middle"> Checkout Setup</h3> <a class="premiumpress_button" href="javascript:void(0);" onclick="PPHelpMe()"><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/youtube.png" align="middle" style="margin-top:-10px;"> Help Me</a> 							 
<ul>
	 
 
</ul>
</div>

<div id="premiumpress_tab1" class="content">

<div class="grid400-left">



<fieldset>
<div class="titleh"> <h3>Shipping Basics</h3>  </div>
<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" />


<div class="ppt-form-line">	
<span class="ppt-labeltext">Checkout Setup</span>	
 <select name="adminArray[checkout_setup]" class="ppt-forminput">				
			<option value="pay" <?php if(get_option("checkout_setup") =="pay"){ print "selected"; }?>>Ask for payment at checkout.</option>
            <option value="message" <?php if(get_option("checkout_setup") =="message"){ print "selected"; }?>>Do not ask for payment (add custom message).</option>
			</select>
<div class="clearfix"></div>
</div>

<?php if(get_option("checkout_setup") =="message"){ ?>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Checkout Message</span>	
 <textarea name="adminArray[checkout_message_text]" type="text"  class="ppt-forminput" style="height:100px;"><?php echo stripslashes(get_option("checkout_message_text")); ?></textarea>
 
<div class="clearfix"></div>
</div>
 <?php } ?>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Checkout Layout</span>	
 <select name="adminArray[checkout_skip_registration]" class="ppt-forminput">
				<option value="no" <?php if(get_option("checkout_skip_registration") =="no"){ print "selected";} ?>>Full Checkout</option>
                <option value="1page" <?php if(get_option("checkout_skip_registration") =="1page"){ print "selected";} ?>>1 page checkout (with confirmation page)</option>				
                <option value="yes" <?php if(get_option("checkout_skip_registration") =="yes"){ print "selected";} ?>>1 page checkout (no shipping options, no confirmation page)</option>
			</select>
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Guest Checkouts</span>	
 <select name="adminArray[checkout_display_guest]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("checkout_display_guest") =="yes"){ print "selected";} ?>>Enable</option>
				<option value="no" <?php if(get_option("checkout_display_guest") =="no"){ print "selected";} ?>>Disable</option>
			</select> 
             
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Tax Options</span>	
     <select name="adminArray[enable_tax_admin]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_tax_admin") =="1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_tax_admin") =="0"){ print "selected"; }?>>Disable</option>
			</select>
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Quantity Promotions</span>	
 <select name="adminArray[enable_promotionqty]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_promotionqty") =="1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_promotionqty") =="0"){ print "selected"; }?>>Disable</option>
			</select>
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Coupon Codes</span>	
 <select name="adminArray[coupon_enable]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("coupon_enable") =="yes"){ print "selected"; }?>>Enable</option>
				<option value="no" <?php if(get_option("coupon_enable") =="no"){ print "selected"; }?>>Disable</option>
			</select>
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Global Markup Price</span>	
 <?php echo get_option("currency_symbol"); ?> <input name="adminArray[product_price_extra]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("product_price_extra"); ?>" /><br />
<small>Add the value entered on top of all products within your website.</small>			
<div class="clearfix"></div>
</div>

 


<p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p>
</form> 
</fieldset>


</div><div class="grid400-left last">






<fieldset>

<?php if(get_option("checkout_ctax_enable") =="yes"){ ?>
<form method="post" name="ShopperPress_tax" target="_self">
 
<?php if(isset($_POST['showtax'])){ 
$ctax1 = get_option("ctax_".$_POST['billing']['country']);
//$ctax2 = get_option("ctax_"$_POST['billing']['country']);
?>
<input name="submitted" type="hidden" value="yes" />
<input name="ctax" type="hidden" value="yes" />
 
<?php } ?>

<?php if(isset($_POST['showtax'])  && strlen($_POST['billing']['country']) > 2){  ?>

<script type="text/javascript"> jQuery(document).ready(function() { ShopperPress_ChangeStateWithValue1('<?php echo $_POST['billing']['country']; ?>'); }); </script>
 
<div class="titleh"> <h3>Tax For <?php echo $_POST['billing']['country']; ?></h3>  </div>
        
 <div class="ppt-form-line">	
<span class="ppt-labeltext">Country Tax Amount</span>	
<input type="text" class="txt" name="adminArray[ctax_<?php echo $_POST['billing']['country']; ?>]" style="width:50px;" value="<?php echo $ctax1; ?>"> %
 	
<div class="clearfix"></div>

<small>Current value set for <b><?php echo $_POST['billing']['country']; ?></b> is <?php if($ctax1 ==""){ echo 0; }else{ echo $ctax1; } ?>% </small>
</div>
 
 <div class="ppt-form-line">	
<span class="ppt-labeltext">State Tax Amount</span>	
<div id="ShopperPressState"></div><!-- AJAX STATE LIST -->
<input type="text" class="txt" name="citytax" style="width:50px; margin-left:140px;" value=""> %
 	
<div class="clearfix"></div>
<div id="ShopperPressState_amount"></div><!-- AJAX STATE LIST -->
</div>			 
			
  <p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p>        
 
<?php } ?>  
</form> 
<?php } ?>


<?php if(get_option("enable_tax_admin") =="1"){  ?>
<div class="titleh"> <h3>Tax Options</h3>  </div>



<form method="post"  target="_self" id="showtaxoptions" name="showtaxoptions">
<input name="submitted" type="hidden" value="yes" />
 <input name="showtax" type="hidden" value="yes" />
 
 <div class="ppt-form-line">	
<span class="ppt-labeltext">Global Tax</span>	
 	<select name="adminArray[checkout_tax_enable]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("checkout_tax_enable") =="yes"){ print "selected"; }?>>Yes</option>
				<option value="no" <?php if(get_option("checkout_tax_enable") =="no"){ print "selected"; }?>>No</option>
			</select>		
<div class="clearfix"></div>
<?php if(get_option("checkout_tax_enable") =="yes"){ ?>
<div style="margin-left:140px;"> 		 <input type="text" class="txt" name="adminArray[checkout_tax_amount]" style="width:50px;" value="<?php echo get_option("checkout_tax_amount"); ?>"> % 	 
 </div>
 <?php } ?>     
        
</div>

 <div class="ppt-form-line">	
<span class="ppt-labeltext">VAT  </span>	
 	<select name="adminArray[enable_VAT]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("enable_VAT") =="yes"){ print "selected"; }?>>Yes</option>
				<option value="no" <?php if(get_option("enable_VAT") =="no"){ print "selected"; }?>>No</option>
			</select>		
<div class="clearfix"></div>
</div>

 <div class="ppt-form-line">	
<span class="ppt-labeltext">Country/State Tax</span>	
 			
    			<select name="adminArray[checkout_ctax_enable]" class="ppt-forminput">
				<option value="no" <?php if(get_option("checkout_ctax_enable") =="no"){ print "selected"; }?>>No</option>
				<option value="yes" <?php if(get_option("checkout_ctax_enable") =="yes"){ print "selected"; }?>>Yes</option>
				
			</select>
<div class="clearfix"></div>
</div>


    <?php if(get_option("checkout_ctax_enable") =="yes"){    ?>
    <div style="padding:10px; background:#e6ffca;">
    <h3>Select a country to view tax options</h3>
    <select name="billing[country]" class="mid2" onchange="document.showtaxoptions.submit();" class="ppt-forminput" style="width:350px;">
                    
    
    <?php include(str_replace("functions/","",THEME_PATH)."_countrylist.php"); ?>
    </select>
    </div>                  
    <?php } ?>  
    

<p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p>
</form><?php } ?>

  
</fieldset>








<?php if(get_option("enable_promotionqty") =="1"){ ?>
<fieldset>
<div class="titleh"> <h3>Promotions</h3>  </div> 

<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="promo" type="hidden" value="yes" />
 
<div id="aa1" style="display:none;">
<table  > 
 
 <tr class="mainrow">
	 
		<td class="forminp">
			If the order quantity is between
			<input name="adminArray[promotion_qty1a]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty1a"); ?>" /> and 
			<input name="adminArray[promotion_qty1b]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty1b"); ?>" />			
			 items.<br />
			 <br />
			 Offer a 
			 price discount of %
			 <input name="adminArray[promotion_qty1c]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty1c"); ?>" /> 
		    or <?php echo get_option("currency_code"); ?>		    <input name="adminArray[promotion_qty1d]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty1d"); ?>" />			 <br />		</td>
	</tr>

	<tr class="mainrow">
	 
		<td class="forminp">
		If the order quantity is between
			<input name="adminArray[promotion_qty2a]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty2a"); ?>" /> and 
			<input name="adminArray[promotion_qty2b]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty2b"); ?>" />			
			 items.<br />
			 <br />
			 Offer a 
			 price discount of %
			 <input name="adminArray[promotion_qty2c]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty2c"); ?>" /> 
		    or <?php echo get_option("currency_code"); ?>		    <input name="adminArray[promotion_qty2d]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty2d"); ?>" />			 <br />		</td>


</td>
	</tr>

	<tr class="mainrow">
 
		<td class="forminp">

		If the order quantity is between
			<input name="adminArray[promotion_qty3a]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty3a"); ?>" /> and 
			<input name="adminArray[promotion_qty3b]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty3b"); ?>" />			
			 items.<br />
			 <br />
			 Offer a 
			 price discount of %
			 <input name="adminArray[promotion_qty3c]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty3c"); ?>" /> 
		    or <?php echo get_option("currency_code"); ?>		    <input name="adminArray[promotion_qty3d]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("promotion_qty3d"); ?>" />			 <br />		</td>

</td>
	</tr>


<tr>
<td colspan="3"><p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p></td>
</tr>
</table>
</form>
</div>
 
<a href="javascript:void(0);" onclick="toggleLayer('aa1');" class="ppt_layout_showme">Show/Hide Options</a>
 
</fieldset>
<?php } ?>












<?php if(get_option("coupon_enable") =="yes"){ ?>
<fieldset>
<div class="titleh"> <h3>Coupons Options</h3>  </div>
<div id="aa2" style="display:none;">
<form method="post" name="ShopperPress" target="_self">
<input name="couponcode" type="hidden" value="1" />
<input name="submitted" type="hidden" value="yes" />

<div class="ppt-form-line">	
<span class="ppt-labeltext">Enter Coupon Code</span>	
 <input name="coupon[name]" type="text" class="ppt-forminput" style="width: 150px;" /> 
<div class="clearfix"></div>
			
</div>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Discount</span>	
 <?php echo get_option("currency_code"); ?> <input name="coupon[price]" type="text" class="ppt-forminput" style="width: 50px;" /> or %<input name="coupon[percentage]" type="text" class="ppt-forminput" style="width: 50px;" />
<div class="clearfix"></div> 
</div>

<p><input class="premiumpress_button" type="submit" value="Add Coupon" style="color:#fff;" /></p>
<table style="margin-bottom: 20px;"></table>
<h3 class="title">Current Coupon Codes</h3>
	<table width="100%"  border="0" style="border:1px solid #ddd;">
    <tr >
    <th style="background:#ceffb4 !important; border-top:1px solid #666; border-bottom:1px solid #83c162;font-weight:bold;">COUPON CODE</th>
    <th style="background:#ceffb4 !important; border-top:1px solid #666; border-bottom:1px solid #83c162;font-weight:bold;">DISCOUNT</th>
    <th style="background:#ceffb4 !important; border-top:1px solid #666; border-bottom:1px solid #83c162;font-weight:bold;">USAGE</th>
    <th style="background:#ceffb4 !important; border-top:1px solid #666; border-bottom:1px solid #83c162;font-weight:bold;">ACTIONS</th>
    </tr>
	<?php
	$i=0;
	$ArrayCoupon = get_option("coupon_array");
	if(is_array($ArrayCoupon)){ foreach($ArrayCoupon as $value){ if(strlen($value['name']) > 1){ 
	
	$SQL = "SELECT count(*) AS total FROM ".$wpdb->prefix."orderdata  WHERE order_couponcode='".$value['name']."'";
	$f = (array)$wpdb->get_results($SQL);
 	
	?>
	  <tr style="background:<?php if($i % 2){ ?>white;<?php }else{ ?>#E3FCE4<?php } ?>">
		<td style="padding:5px;"> <p> <?php echo $value['name']; ?> </p></td>
        <td><?php if($value['price'] !=""){ echo get_option("currency_code").$value['price']; }else{ echo $value['percentage']."% OFF"; } ?></td>
        <td>Used <?php echo $f[0]->total; ?> times</td>
        <td align="center"> <a href="admin.php?page=checkout&delc=<?php echo $i; ?>">Delete Coupon</a>  </td> </tr>
	
	<?php  $i++; }} }  ?>
	</table>
 

</form>
</div>
 
<a href="javascript:void(0);" onclick="toggleLayer('aa2');" class="ppt_layout_showme">Show/Hide Options</a>
 
</fieldset>
<?php } ?>


 


</div>

<div class="clearfix"></div>


 

 





























</div>
<div id="premiumpress_tab2" class="content">

</div>
<div id="premiumpress_tab3" class="content">

</div>
<div id="premiumpress_tab4" class="content">
<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="credit_packages" type="hidden" value="yes" />
<table>	
 
	<tr class="mainrow">
		<td class="titledesc">Page Text
		</td><td class="forminp">
			 
			<textarea name="adminArray[credit_page_text]" type="text" class="txt" style="width:550px;height:150px;"><?php echo stripslashes(get_option("credit_page_text")); ?></textarea>
 
			<br />
			<small>Enter any text you like to be displayed on the purchase/section credit package page. Allows HTML code.</small>

		</td>
	</tr> 

<td colspan="3">

<table width="600"  border="0">


  <tr>
    <td width="51%" height="47"><strong>Package Name </strong></td>
    <td width="13%"><div align="center"><strong>Credits (Numeric)</strong></div></td>
    <td width="24%"><div align="center"><strong>Price</strong></div></td>
    <td width="12%"><div align="center"><strong>Enabled</strong></div></td>
    </tr>
    
  <tr>  
    <td><input type="text" class="txt" name="credit_name_1" style="width:300px;" value="<?php echo get_option("credit_name_1"); ?>"></td>
    <td><div align="center"><input name="credit_del_1" type="text" class="txt" size="5" value="<?php echo get_option("credit_del_1"); ?>" style="width:100px;"></div></td>  
    <td><div align="center"><input name="credit_price_1" type="text" class="txt" size="5" value="<?php echo get_option("credit_price_1"); ?>"></div></td>
    <td><div align="center"><input type="checkbox" name="credit_enable_1" value="1" <?php if(get_option("credit_enable_1") ==1){ echo "checked"; } ?>> </div></td>
  </tr>
   <tr>  
    <td><input type="text" class="txt" name="credit_name_2" style="width:300px;" value="<?php echo get_option("credit_name_2"); ?>"></td>
    <td><div align="center"><input name="credit_del_2" type="text" class="txt" size="5" value="<?php echo get_option("credit_del_2"); ?>" style="width:100px;"></div></td>  
    <td><div align="center"><input name="credit_price_2" type="text" class="txt" size="5" value="<?php echo get_option("credit_price_2"); ?>"></div></td>
    <td><div align="center"><input type="checkbox" name="credit_enable_2" value="1" <?php if(get_option("credit_enable_2") ==1){ echo "checked"; } ?>> </div></td>
  </tr>
    <tr>  
    <td><input type="text" class="txt" name="credit_name_3" style="width:300px;" value="<?php echo get_option("credit_name_3"); ?>"></td>
    <td><div align="center"><input name="credit_del_3" type="text" class="txt" size="5" value="<?php echo get_option("credit_del_3"); ?>" style="width:100px;"></div></td>  
    <td><div align="center"><input name="credit_price_3" type="text" class="txt" size="5" value="<?php echo get_option("credit_price_3"); ?>"></div></td>
    <td><div align="center"><input type="checkbox" name="credit_enable_3" value="1" <?php if(get_option("credit_enable_3") ==1){ echo "checked"; } ?>> </div></td>
  </tr>
    <tr>  
    <td><input type="text" class="txt" name="credit_name_4" style="width:300px;" value="<?php echo get_option("credit_name_4"); ?>"></td>
    <td><div align="center"><input name="credit_del_4" type="text" class="txt" size="5" value="<?php echo get_option("credit_del_4"); ?>" style="width:100px;"></div></td>  
    <td><div align="center"><input name="credit_price_4" type="text" class="txt" size="5" value="<?php echo get_option("credit_price_4"); ?>"></div></td>
    <td><div align="center"><input type="checkbox" name="credit_enable_4" value="1" <?php if(get_option("credit_enable_4") ==1){ echo "checked"; } ?>> </div></td>
  </tr>
    <tr>  
    <td><input type="text" class="txt" name="credit_name_5" style="width:300px;" value="<?php echo get_option("credit_name_5"); ?>"></td>
    <td><div align="center"><input name="credit_del_5" type="text" class="txt" size="5" value="<?php echo get_option("credit_del_5"); ?>" style="width:100px;"></div></td>  
    <td><div align="center"><input name="credit_price_5" type="text" class="txt" size="5" value="<?php echo get_option("credit_price_5"); ?>"></div></td>
    <td><div align="center"><input type="checkbox" name="credit_enable_5" value="1" <?php if(get_option("credit_enable_5") ==1){ echo "checked"; } ?>> </div></td>
  </tr> 
 
</table><p>&nbsp;</p></td>
</tr>
    
    
<tr>
<td colspan="3"><p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p></td>
</tr>
</table>
</table>

</form>  

</div>
<div id="premiumpress_tab5" class="content">





</div>
<div id="premiumpress_tab6" class="content">




</div>            
					 
       
</div>
</div>
<div class="clearfix"></div> 


