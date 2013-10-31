<?php


$xyz_tinymce=get_option("xyz_tinymce");
$xyz_credit_link=get_option('xyz_credit_link');
?>
<h2>Basic Settings</h2>
<form method="post" >

<table  class="widefat" style="width:98%">

<tr valign="top" id="xyz_dbx">

<td scope="row" colspan="1" width="50%"><label for="xyz_tinymce">Enable tiny MCE filter to prevent auto removal of &lt br &gt and &lt p &gt tags ?</label>	</td>

<td><select name="xyz_tinymce" id="xyz_tinymce" >

<option value ="1" <?php if($xyz_tinymce=='1') echo 'selected'; ?> >Yes </option>

<option value ="0" <?php if($xyz_tinymce=='0') echo 'selected'; ?> >No </option>
</select>
</td>

</tr>

<tr valign="top" id="xyz_dbx">

<td scope="row" colspan="1"><label for="xyz_credit_link">Enable credit link to author ?</label>	</td><td><select name="xyz_credit_link" id="xyz_lbx_credit_link" >

<option value ="lbx" <?php if($xyz_credit_link=='lbx') echo 'selected'; ?> >Yes </option>

<option value ="<?php echo $xyz_credit_link!='lbx'?$xyz_credit_link:0;?>" <?php if($xyz_credit_link!='lbx') echo 'selected'; ?> >No </option>
</select>
</td></tr>


<tr>
<td scope="row"> </td>
<td>
<input type="submit" value=" Update Settings " /></td>
</tr>


</table></form>
<?php 










































?>