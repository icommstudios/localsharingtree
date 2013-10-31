<?php 


	// CHOSEN
	wp_register_style( 'chosen', PPT_PATH.'js/jquery.chosen.css');
	wp_enqueue_style( 'chosen' );

 	wp_register_script( 'chosen', PPT_PATH.'js/jquery.chosen.min.js');
	wp_enqueue_script( 'chosen' ); 	

global $ThemeDesign; PremiumPress_Header(); ?>



<style>
select { border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px; }
 
</style>

<div id="premiumpress_box1" class="premiumpress_box premiumpress_box-100"><div class="premiumpress_boxin"><div class="header">
<h3><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/new/block7.png" align="middle"> Shipping Options</h3>	 <a class="premiumpress_button" href="javascript:void(0);" onclick="PPHelpMe()"><img src="<?php echo PPT_FW_IMG_URI; ?>/admin/youtube.png" align="middle" style="margin-top:-10px;"> Help Me</a> 						 
<ul>
	 
   
</ul>
</div>

<div id="premiumpress_tab1" class="content">



<div class="grid400-left">

<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="shipping_tax" type="hidden" value="yes" />

<fieldset>
<div class="titleh"> <h3>Shipping Basics</h3>  </div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Enable Shipping</span>	
   <select name="adminArray[shipping_enable]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("shipping_enable") =="yes"){ print "selected='selected'"; }?>>Enable</option>
				<option value="no" <?php if(get_option("shipping_enable") =="no"){ print "selected"; }?>>Disable</option>
				<?php if(get_option("shipping_enable") ==""){ ?><option value="" selected>--------------</option><?php } ?>
			</select>
<div class="clearfix"></div>
</div>
<?php if(get_option("shipping_enable") =="yes"){ ?>
<div class="ppt-form-line">	
<span class="ppt-labeltext">Free Shipping

<a href="javascript:void(0);" onmouseover="this.style.cursor='pointer';" 
onclick="PPMsgBox(&quot;Free shipping will be applied to order totals over XXX before extra shipping/taxes and coupons are appled.&quot;);"><img src="<?php echo PPT_FW_IMG_URI; ?>help.png" style="float:right;" /></a>
</span>	
   <select name="adminArray[shipping_free]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("shipping_free") =="yes"){ print "selected"; }?>>Enable On Orders Over <?php echo get_option("currency_code"); ?>XXX </option>
				<option value="no" <?php if(get_option("shipping_free") =="no" || get_option("shipping_free") == ""){ print "selected"; }?>>Disable</option>
			</select> 
<div class="clearfix"></div>
 
 <div style="margin-left:140px;">Orders Over: <?php echo get_option("currency_code"); ?> <input name="adminArray[shipping_free_price]" type="text" class="ppt-forminput" style="width: 60px; margin-top:5px;" value="<?php echo get_option("shipping_free_price"); ?>" /> 
			<br /><small>will receive free shipping</small>
			</div>
         
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Tax on Shipping

<a href="javascript:void(0);" onmouseover="this.style.cursor='pointer';" 
onclick="PPMsgBox(&quot;This will tell the system to include the shipping costs within the tax calculations.&quot;);"><img src="<?php echo PPT_FW_IMG_URI; ?>help.png" style="float:right;" /></a>
</span>	
   <select name="adminArray[enable_shipping_tax]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_shipping_tax") =="1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_shipping_tax") =="0" || get_option("enable_shipping_tax") == ""){ print "selected"; }?>>Disable </option>
			</select>
<div class="clearfix"></div>
</div>

 <br /><div class="titleh"> <h3>Shipping Options</h3>  </div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Price Rate Shipping

<a href="javascript:void(0);" onmouseover="this.style.cursor='pointer';" 
onclick="PPMsgBox(&quot;This option lets you choose shipping prices based on the order total. &quot;);"><img src="<?php echo PPT_FW_IMG_URI; ?>help.png" style="float:right;" /></a>
</span>	
 
             <select name="adminArray[enable_priceshipping]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_priceshipping") =="1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_priceshipping") =="0" || get_option("enable_priceshipping") == ""){ print "selected"; }?>>Disable </option>
			</select> 
<div class="clearfix"></div>
</div>


<div class="ppt-form-line">	
<span class="ppt-labeltext">Weight Rate Shipping</span>	
    <select name="adminArray[enable_weightshipping]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_weightshipping") == "1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_weightshipping") == "0"){ print "selected"; }?>>Disable </option>
			</select> 
<div class="clearfix"></div>

<div style="margin-left:140px;">
Display Metric	
 <input name="adminArray[shipping_weight_metric]" type="text" class="ppt-forminput" style="width: 50px; margin-top:5px;" value="<?php echo get_option("shipping_weight_metric"); ?>" /> (eg. LBS, KG)
</div>
</div>


<div class="ppt-form-line">	
<span class="ppt-labeltext">Flat Rate Shipping</span>	
  <select name="adminArray[enable_flatrate]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_flatrate") =="1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_flatrate") =="0" || get_option("enable_flatrate") == ""){ print "selected"; }?>>Disable </option>
			</select>
<div class="clearfix"></div>
 
<div style="margin-left:140px;"><?php echo get_option("currency_code"); ?><input name="adminArray[shipping_cost]" type="text" class="ppt-forminput" style="width: 50px; margin-top:5px;" value="<?php echo get_option("shipping_cost"); ?>" /></div>

 
</div>



<?php /*
<div class="ppt-form-line">	
<span class="ppt-labeltext">Dimensions Shipping</span>	
 
             <select name="adminArray[enable_dimensionshipping]" class="ppt-forminput">
				<option value="1" <?php if(get_option("enable_dimensionshipping") =="1"){ print "selected"; }?>>Enable</option>
				<option value="0" <?php if(get_option("enable_dimensionshipping") =="0" || get_option("enable_dimensionshipping") == ""){ print "selected"; }?>>Disable </option>
			</select> 
<div class="clearfix"></div>
</div>
*/ ?>



<?php if(get_option('checkout_skip_registration') == "no"){ ?>
<div class="ppt-form-line">	
<span class="ppt-labeltext">UPS Shipping</span>	
<select name="adminArray[shipping_enable_UPS]" class="ppt-forminput">
				<option value="yes" <?php if(get_option("shipping_enable_UPS") =="yes"){ print "selected='selected'"; }?>>Enable</option>
				<option value="no" <?php if(get_option("shipping_enable_UPS") =="no"){ print "selected"; }?>>Disable</option>
				<?php if(get_option("shipping_enable_UPS") ==""){ ?><option value="" selected>--------------</option><?php } ?>
			</select> 
<div class="clearfix"></div>
</div>
<?php } ?>

 
 

<?php } ?> 
 
 
 <p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p>
 
</fieldset>
</form>



</div> <div class="grid400-left last">





<div style="display:none;">
<div id="clonewship">
<div class="green_box"><div class="green_box_content">
<b>If the total is :</b>
<p>between 
<input name="shipping_weight[a][]" type="text" class="txt" style="width: 50px;" value="" /> and
<input name="shipping_weight[b][]" type="text" class="txt" style="width: 50px;" value="" /> the shipping cost =  
<?php echo get_option('currency_symbol'); ?><input name="shipping_weight[c][]" type="text" class="txt" style="width: 50px;" value="" />
</p>            
            
<p><b>Country</b></p>
<select name="shipping_weight[cl][][]" data-placeholder="Choose a Country..." style="width:350px; " multiple="" tabindex="3" >
<?php include(str_replace("functions/","",THEME_PATH)."_countrylist.php"); ?>
</select>
<small>Hold CTRL to select multiple values.</small>
<p>Leave blank to apply rule to all countries.</p>

<div class="clearfix"></div></div></div>
</div>
</div>




<?php if(get_option("enable_priceshipping") =="1"){ $sp_priceshipping = get_option('sp_priceshipping');  ?>
<fieldset>
<div class="titleh"> <h3>Price Based Shipping</h3>  </div>
<div id="sprice" style="display:none;">

<form method="post" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="submittedType" type="hidden" value="sp_shipping_price" />
<input name="price" type="hidden" value="yes" />



<?php $i=0; if(is_array($sp_priceshipping)){ foreach($sp_priceshipping as $data){  ?>
 
<div class="green_box" id="shipid1<?php echo $i; ?>" style="margin-top:10px;"><div class="green_box_content">

<b>If the basket total price is :</b>

<p>between 
<input name="shipping_weight[a][]" type="text" class="txt" style="width: 50px;" value="<?php echo $data['a']; ?>" /> and
<input name="shipping_weight[b][]" type="text" class="txt" style="width: 50px;" value="<?php echo $data['b']; ?>"  id="shipa1<?php echo $i; ?>" /> the shipping cost =  
<?php echo get_option('currency_symbol'); ?><input name="shipping_weight[c][]" type="text" class="txt" style="width: 50px;" value="<?php echo $data['c']; ?>" />
</p>            
            
<p><b>Country</b></p>
 
<select name="shipping_weight[cl][<?php echo $i; ?>][]" data-placeholder="Choose a Country..." style="width:350px; " multiple="" tabindex="3" class="chzn-select" >
<?php foreach($data['cl'] as $val){ echo "<option selected=selected>".$val."</option>";  } ?>


<?php include(str_replace("functions/","",THEME_PATH)."_countrylist.php"); ?>
</select>
<p>Leave blank to apply rule to all countries.</p>

<a href="javascript:void(0);" onclick="document.getElementById('shipa1<?php echo $i; ?>').value=''; jQuery('#shipid1<?php echo $i; ?>').hide();" class="button tagadd right">Remove Value</a>

<div class="clearfix"></div></div></div>
 
<?php $i++; } } ?>

<div id="newclones2"></div>
 

<!-- class="chzn-select" .trigger('liszt:updated'); -->
 
<div class="ppt-form-line">	
<p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /> <a href="javascript:void(0);" onclick="jQuery('#clonewship').clone().appendTo('#newclones2');" class="button tagadd" style="float:right;">Add New Value</a>
</p>
</div>

 
</form>
 </div>
 <a href="javascript:void(0);" onclick="toggleLayer('sprice');" class="ppt_layout_showme">Show/Hide Options</a>
 
</fieldset>
<?php } ?>
















<?php if(get_option("enable_weightshipping") =="1"){  $sp_weightshipping = get_option('sp_weightshipping');  //update_option('sp_weightshipping',"");
if(!is_array($sp_weightshipping) || empty($sp_weightshipping) ){ $sp_weightshipping = array('0' => array('a'=> '1', 'b'=> '2', 'c'=> '2', 'countrylist'=> '2')); }  ?>

<fieldset>
<div class="titleh"> <h3>Weight Based Shipping</h3>  </div>
<div id="sweigh" style="display:none;">

 
<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="submittedType" type="hidden" value="sp_shipping_weight" />





<?php $i=0; if(is_array($sp_weightshipping)){ foreach($sp_weightshipping as $data){  ?>
 
<div class="green_box" id="shipid<?php echo $i; ?>" style="margin-top:10px;"><div class="green_box_content">

<b>If the total weight is :</b>

<p>between 
<input name="shipping_weight[a][]" type="text" class="txt" style="width: 50px;" value="<?php echo $data['a']; ?>" /> and
<input name="shipping_weight[b][]" type="text" class="txt" style="width: 50px;" value="<?php echo $data['b']; ?>"  id="shipa<?php echo $i; ?>" /> the shipping cost =  
<?php echo get_option('currency_symbol'); ?><input name="shipping_weight[c][]" type="text" class="txt" style="width: 50px;" value="<?php echo $data['c']; ?>" />
</p>            
            
<p><b>Country</b></p>
 
<select name="shipping_weight[cl][<?php echo $i; ?>][]" data-placeholder="Choose a Country..." style="width:350px; " multiple="" tabindex="3" class="chzn-select" >
<?php if(is_array($data['cl'])){ foreach($data['cl'] as $val){ echo "<option selected=selected>".$val."</option>";  } } ?>


<?php include(str_replace("functions/","",THEME_PATH)."_countrylist.php"); ?>
</select>
<p>Leave blank to apply rule to all countries.</p>

<a href="javascript:void(0);" onclick="document.getElementById('shipa<?php echo $i; ?>').value=''; jQuery('#shipid<?php echo $i; ?>').hide();" class="button tagadd right">Remove Value</a>

<div class="clearfix"></div></div></div>
 
<?php $i++; } } ?>

<div id="newclones1"></div>
 

<!-- class="chzn-select" .trigger('liszt:updated'); -->
 
<div class="ppt-form-line">	
<p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /> <a href="javascript:void(0);" onclick="jQuery('#clonewship').clone().appendTo('#newclones1');" class="button tagadd" style="float:right;">Add New Value</a>
</p>
</div>


</form>
</div>
 <a href="javascript:void(0);" onclick="toggleLayer('sweigh');" class="ppt_layout_showme">Show/Hide Options</a>
 
</fieldset>
<?php } ?>


















<?php /*if(get_option("enable_dimensionshipping") =="1"){ ?>

<fieldset>
<div class="titleh"> <h3>Dimensions Based Shipping</h3>  </div>
<div id="sweigh" style="display:none;">

<form method="post" name="ShopperPress" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="weight" type="hidden" value="yes" />
<table >	

 
    
    <?php $i=1; while($i < 11){ ?>

	<tr class="mainrow">
	 
		<td class="forminp">
			If the total weight is between 
			<input name="adminArray[shipping_weight<?php echo $i; ?>a]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("shipping_weight".$i."a"); ?>" /> and
            <input name="adminArray[shipping_weight<?php echo $i; ?>b]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("shipping_weight".$i."b"); ?>" /> the shipping price is 
			<input name="adminArray[shipping_weight<?php echo $i; ?>c]" type="text" class="txt" style="width: 50px;" value="<?php echo get_option("shipping_weight".$i."c"); ?>" /><br />
		</td>
	</tr>
    
    <?php $i++; } ?>
    

<tr>
<td colspan="3"><p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p></td>
</tr>
</table>
</form>
</div>
 <a href="javascript:void(0);" onclick="toggleLayer('sweigh');" class="ppt_layout_showme">Show/Hide Options</a>
 
</fieldset>

<?php }*/ ?>














<?php if(get_option("shipping_enable_UPS") =="yes"){ ?>
   
<fieldset>
<div class="titleh"> <h3>UPS Shipping Options</h3>  </div>

<div id="ups1" <?php if(!isset($_GET['upstest'])){ ?>style="display:none;" <?php } ?>>
 <div class="msg msg-info" style="margin-top:10px;">
   <p>UPS shipping can only be accurately calculated if you are using weight based products. The weight metric you setup is <?php echo get_option("shipping_weight_metric"); ?></p>
 </div>
    
<form method="post" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="shipping_method" type="hidden" value="yes" />
  
<div class="ppt-form-line">	
<span class="ppt-labeltext">UPS ACCESSKEY</span>	
 <input name="adminArray[shipping_UPS_accesskey]" type="text" class="ppt-forminput" value="<?php echo get_option("shipping_UPS_accesskey"); ?>" />
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">UPS USERID</span>	
<input name="adminArray[shipping_UPS_userID]" type="text" class="ppt-forminput" value="<?php echo get_option("shipping_UPS_userID"); ?>" />
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">UPS PASSWD</span>	
 <input name="adminArray[shipping_UPS_password]" type="text" class="ppt-forminput" value="<?php echo get_option("shipping_UPS_password"); ?>" />
<div class="clearfix"></div>
</div>
  <p class="ppnote">You should enter details about the location where the products will be shipped FROM.</p>
          
<div class="ppt-form-line">	
<span class="ppt-labeltext">Your Country</span>	
 <input name="adminArray[shipping_UPS_userCOUNTRY]" type="text" class="ppt-forminput" value="<?php echo get_option("shipping_UPS_userCOUNTRY"); ?>" />
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Your ZIP Code</span>	
<input name="adminArray[shipping_UPS_userZIP]" type="text" class="ppt-forminput" value="<?php echo get_option("shipping_UPS_userZIP"); ?>" />
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Your State</span>	
 <select name="adminArray[shipping_UPS_userSTATE]">
 <option><?php echo get_option("shipping_UPS_userSTATE"); ?></option>
				<option value=AK>AK</option>
				<option value=AL>AL</option>
				<option value=AR>AR</option>
				<option value=AZ>AZ</option>
				<option value=CA>CA</option>
				<option value=CO>CO</option>
				<option value=CT>CT</option>
				<option value=DC>DC</option>
				<option value=DE>DE</option>
				<option value=FL>FL</option>
				<option value=GA>GA</option>
				<option value=HI>HI</option>
				<option value=IA>IA</option>
				<option value=ID>ID</option>
				<option value=IL>IL</option>
				<option value=IN>IN</option>
				<option value=KS>KS</option>
				<option value=KY>KY</option>
				<option value=LA>LA</option>
				<option value=MA>MA</option>
				<option value=MD>MD</option>
				<option value=ME>ME</option>
				<option value=MI>MI</option>
				<option value=MN>MN</option>
				<option value=MO>MO</option>
				<option value=MS>MS</option>
				<option value=MT>MT</option>
				<option value=NC>NC</option>
				<option value=ND>ND</option>
				<option value=NE>NE</option>
				<option value=NH>NH</option>
				<option value=NJ>NJ</option>
				<option value=NM>NM</option>
				<option value=NV>NV</option>
				<option value=NY>NY</option>
				<option value=OH>OH</option>
				<option value=OK>OK</option>
				<option value=OR>OR</option>
				<option value=PA>PA</option>
				<option value=RI>RI</option>
				<option value=SC>SC</option>
				<option value=SD>SD</option>
				<option value=TN>TN</option>
				<option value=TX>TX</option>
				<option value=UT>UT</option>
				<option value=VA>VA</option>
				<option value=VT>VT</option>
				<option value=WA>WA</option>
				<option value=WI>WI</option>
				<option value=WV>WV</option>
				<option value=WY>WY</option>
				<option value=AA>AA</option>
				<option value=AE>AE</option>
				<option value=AP>AP</option>
				<option value=AS>AS</option>
				<option value=FM>FM</option>
				<option value=GU>GU</option>
				<option value=MH>MH</option>
				<option value=MP>MP</option>
				<option value=PR>PR</option>
				<option value=PW>PW</option>
				<option value=VI>VI</option>
			</select>
<div class="clearfix"></div>
<small>(currently <?php echo get_option("shipping_UPS_userSTATE"); ?>)</small>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext"><input name="df1" type="checkbox" value="1"  onchange="ChangeTickValue('df1');" <?php if(get_option('shipping_UPS_doubleprice') == "1"){ ?>checked="checked"<?php } ?>   /> </span>	
Charge UPS Amount PER Item 
<div class="clearfix"></div>
</div>
<input type="hidden" name="adminArray[shipping_UPS_doubleprice]" id="df1" value="<?php if(get_option('shipping_UPS_doubleprice') == 1){ echo 1; }else{ echo 0; } ?>" />


  <script language="javascript">
 
 function ChangeTickValue(div){
 
	 if(document.getElementById(div).value==0){
	 document.getElementById(div).value=1;
	 }else{
	 document.getElementById(div).value=0;
	 }
 
 }
 
 </script>
   
          <?php if(isset($_GET['upstest'])){ ?>
           <p>Sample Data</p>
                <?php echo $ThemeDesign->UPSMETHODS(true); ?>
          <?php } ?>
 
 
 
<p><a href="admin.php?page=shipping&upstest=1" style="background:green;color:white;padding:4px; float:right;">Run UPS Test</a>
 <input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p>
</div> 
 </form> 
 <a href="javascript:void(0);" onclick="toggleLayer('ups1');" class="ppt_layout_showme">Show/Hide Options</a>
 
 
</fieldset>
<?php } ?>






<?php if(get_option("shipping_enable") =="yes"){ ?>

<fieldset>
<div class="titleh"> <h3>Fixed Rate Shipping Methods</h3>  </div>

<div id="smethod" style="display:none;">

<p>Fixed rate shipping methods allow you to create your own shipping captions and values. This is a basic but simple shipping integration where you assign a fixed cost for a fixed shipping value.</p>

<p><b>Example: Shipping by Air = $100</b></p>

<p>The user will select the option during the checkout process and the total will be added to their final cart total.</p>

<form method="post" target="_self">
<input name="submitted" type="hidden" value="yes" />
<input name="shipping_method" type="hidden" value="yes" />
<table width="600"  border="0">
  <tr>
    <td width="51%" height="47"><strong>Name </strong></td>
 
    <td width="24%"><div align="center"><strong>Price</strong></div></td>
    <td width="12%"><div align="center"><strong>Enabled</strong></div></td>
    </tr>
    
    <?php $b=1;while($b < 11){	 ?>
  <tr>  
    <td><input type="text" name="pak_name_<?php echo $b; ?>" class="ppt-forminput" value="<?php echo get_option("pak_name_".$b); ?>"></td>
     
    <td><div align="center"><input name="pak_price_<?php echo $b; ?>" type="text" class="ppt-forminput" style="width:50px;" size="5" value="<?php echo get_option("pak_price_".$b); ?>"></div></td>
    <td><div align="center"><input type="checkbox" name="pak_enable_<?php echo $b; ?>" value="1" <?php if(get_option("pak_enable_".$b) ==1){ echo "checked"; } ?>> </div></td>
  </tr>
  <?php $b++; } ?> 
<tr>
<td colspan="3"><p><input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" /></p></td>
</tr>
</table>
</form> 

</div>
 <a href="javascript:void(0);" onclick="toggleLayer('smethod');" class="ppt_layout_showme">Show/Hide Options</a>
 
</fieldset>
<?php } ?>








<?php if(get_option("shipping_enable") =="yes"){ ?>
<fieldset>
<div class="titleh"> <h3>Country Shipping</h3>  </div>

<form method="post" name="ship1" target="_self">
 <input name="selectcountry" type="hidden" value="yes" />
  <input name="country" id="country" type="hidden" value="yes" />
</form>

<form method="post" name="ship" target="_self">
<input name="submitted" type="hidden" value="yes" />


<div class="ppt-form-line">	
<span class="ppt-labeltext">Select Country</span>	
<?php if(isset($_POST['selectcountry'])){ ?>
<input type="hidden" value="<?php echo $_POST['country']; ?>" name="country">
<b><?php echo $_POST['country']; ?></b>
<?php } ?>

<select class="mid2" onchange="document.getElementById('country').value=this.value;document.ship1.submit();" class="ppt-forminput">
<?php include(str_replace("functions/","",THEME_PATH)."_countrylist.php"); ?>
</select>

<div class="clearfix"></div>
</div>



 
<?php if(isset($_POST['selectcountry'])){ ?>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Fixed Amount</span>	
<input name="adminArray[shipping_country_fixed_<?php echo $_POST['country']; ?>]" type="text" class="ppt-forminput" style="width: 50px;" value="<?php echo get_option("shipping_country_fixed_".$_POST['country']); ?>" />
<div class="clearfix"></div>
</div>

<div class="ppt-form-line">	
<span class="ppt-labeltext">Percentage Amount</span>	
<input name="adminArray[shipping_country_perc_<?php echo $_POST['country']; ?>]" type="text" class="ppt-forminput" style="width: 50px;" value="<?php echo get_option("shipping_country_perc_".$_POST['country']); ?>" />
<div class="clearfix"></div>
</div>
 
<input class="premiumpress_button" type="submit" value="Save Changes" style="color:#fff;" />
<?php } ?>


</form>
 
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

</div>


<div id="premiumpress_tab5" class="content">

</div>
<div id="premiumpress_tab6" class="content">

</div>            
					 
                        
</div>
</div>
<div class="clearfix"></div> 


 <script type="text/javascript"> jQuery(".chzn-select").chosen(); jQuery(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

