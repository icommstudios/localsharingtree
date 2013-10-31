<?php
/*
Plugin Name: Products Reports
Plugin URI: rvtechnologies.co.in
Description: Allows to show the donation details according to the Products.
Author: Royal Tyagi
Version: 1.0
*/


add_action('admin_menu', 'product_donation_menu');

function product_donation_menu() {
    $allowed_group = 'manage_options';
    
    //Add the admin panel for product_donation
    add_menu_page('Product Summary', 'Product Summary', 'manage_options', 'product_summary','product_donation_report');
    wp_enqueue_script('form', plugins_url('/js/form.js',__FILE__) );
}
    
function product_donation_report() {

    if(isset($_POST['submit']) && $_POST['submit']!='') {
        $p_id = $_POST['product'];
        product_list($p_id);
        
        product_search($p_id);

    } else {
        product_list();
    }
}

// Function For show the product dropdown *********************************

function product_list( $id = Null) {

    global $wpdb;
    $query = "SELECT ID, post_title FROM " .$wpdb->prefix. "posts WHERE post_type = 'post' AND post_status = 'publish' ORDER BY post_title";

    $results = mysql_query($query) or die(mysql_error().' on line: '.__LINE__);

    $all_product = array();
    while ($product = mysql_fetch_object($results)) {
        $all_product[$product->ID] = $product->post_title;        
    } ?>
    
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>Product Donation Report</h2> 
        <form class="wrap" method="post" action="#" >
            <div id="linkadvanceddiv" class="postbox">
                <div style="float: left; width: 100%; clear: both;" class="inside">
                    <table cellpadding="5" cellspacing="5">
                        <tr>
                            <td><?php _e('Select Any Product'); ?></td>
                            <td>
                                <select name = "product" id = "product" style ="font-size:14px;">
                                    <option value ="">Select any product</option>
                                    <?php 
                                        foreach($all_product as $key => $val) { 
                                            if (($id != '') && ($id == $key)) { ?>
                                                <option value ="<?php echo $key; ?>" selected ="selected" style="color:#D65429;"><?php echo $val; ?></option>
                                            <?php } else { ?>
                                                <option value ="<?php echo $key; ?>"><?php echo $val; ?></option>
                                    <?php } }?>
                                </select>
                                <input type ="submit" name="submit" value = "search" id="submit" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </form>        
    </div>
    <?php if ($id == '') { ?>
        <!--<p><?php _e("For Donation please select any product !", 'product_summary');?></p>-->
   <?php } ?>     
<?
}
?>
<?php
// Function For show the product search *********************************

function product_search ($id) {
    if ($id == '') {
        return false;
    }
?>

    <div class="wrap">
        <table class="widefat page fixed" width="100%" cellpadding="3" cellspacing="3">
            <thead>
                <tr>
                    <th><?php _e('Product Id') ?></th>
                    <th><?php _e('User Name') ?></th>
                    <th><?php _e('Deal Id') ?></th>
                    <th><?php _e('Price of Deal') ?></th>
                    <th><?php _e('Organization') ?></th>
                    <th><?php _e('Amount of Donation') ?></th>
                    <th><?php _e('Date Paid') ?></th>
                    <th><?php _e('Total of the date') ?></th>
                </tr>
            </thead>
            <?php
            global $wpdb;

            $query = "SELECT cus_id, cus_name, order_email, order_id, order_total, order_currencycode, order_date, order_data, payment_data, (order_total * 0.1) as donation_amount FROM ".$wpdb->prefix."orderdata GROUP BY order_id";
            $results = mysql_query($query) or die(mysql_error().' on line: '.__LINE__);
            
            $currency = '';
			$result_list[] = array ();
            while ($order = mysql_fetch_object($results)) {
            
                    $data = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $order->payment_data);

                    preg_match('/Details:(.*?)-/', $data,$hits);

					$new_hits = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $hits[1]);
                    $p_id_dup = str_replace('-', '', $new_hits);
                    $p_id = str_replace(' ', '', $p_id_dup);

                    preg_match('/jabber=(.*?)payment_method/', $data,$hits_new);

                    $organization_all = explode('**', $hits_new[1]);
                    $organization = $organization_all[6];

                    if (trim($p_id) == $id) {
						$result_list[] = array (
							'p_id'	=> $p_id,
							'cus_name'	=> $order->cus_name,
							'order_id'	=> $order->order_id,
							'order_total'	=> $order->order_total . ' ' . $order->order_currencycode,
							'organization'	=> $organization,
							'donation_amount'	=> $order->donation_amount . ' ' . $order->order_currencycode,
							'order_date'	=> $order->order_date
						);
						$currency = $order->order_currencycode;
                    }
            }
            
			$i = 1;
            $total_donated_amount = 0;
			$k = 'count';

			$lol = array_shift($result_list);
			if (!empty($result_list)) {
				foreach ($result_list as $rst_list) {
					if ($i%2==0) {
						echo "<tr>";
					} else {
						echo "<tr class ='alternate'>";
					}
					echo "<td>" . $rst_list['p_id'] . "</td>";
					echo "<td>" . $rst_list['cus_name'] . "</td>";
					echo "<td>" . $rst_list['order_id'] . "</td>";
					echo "<td>" . $rst_list['order_total'] . "</td>";
					echo "<td>" . $rst_list['organization'] . "</td>";
					echo "<td>" . $rst_list['donation_amount'] . "</td>";
					echo "<td>" . $rst_list['order_date'] . "</td>";
					echo "<td>" . '&nbsp;' . "</td>";
					echo "<tr/>";
					$i++;

					// Code for calulate the final total of the donated ammount
					$total_donated_amount += $rst_list['donation_amount'];					
				} ?>
				    <tr class ='alternate'>
				        <td><b>Total</b></td>
				        <td colspan="6">&nbsp;</td>
				        <td><b><?php echo $total_donated_amount . ' ' . $currency; ?></b></td>
				    </tr>
			<?php
			} else {
					echo "<tr>";
					echo "<td colspan='8'><p>". 'No record found for this deal.' ."</p></td>";
					echo "<tr/>";					
			}
			//echo "<pre>";	print_r($result_list);	echo "<br/>";
            ?>
        </table>
     </div>       
<?php
}
?>
