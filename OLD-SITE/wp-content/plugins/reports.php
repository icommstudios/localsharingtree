<?php
/*
Plugin Name: Organization Reports 

*/
?>
<?php
add_action('admin_menu', 'add_master_summary_report');

function add_master_summary_report()  
{
   add_menu_page('Master Summary', 'Master Summary', 'manage_options', 'functions','global_summary_report');  
}  

function global_summary_report() {
?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>Master Summary Report</h2>
        <table class="widefat page fixed" width="100%" cellpadding="3" cellspacing="3">
            <thead>
                <tr>
                    <th><a href ="#"><?php _e('User Id') ?></a></th>
                    <th><a href ="#"><?php _e('User Name') ?></a></th>
                    <th><a href ="#"><?php _e('Email') ?></a></th>
                    <th><?php _e('Deal Id') ?></th>
                    <th><?php _e('Price of Deal') ?></th>
                    <th><?php _e('Donation') ?></th>
                    <th><?php _e('Amount of Donation') ?></th>
                    <th><?php _e('Date Paid') ?></th>
                    <th><?php _e('Total of the date') ?></th>
                </tr>
            </thead>
            <tbody id = "the-list" data-wp-lists="list:user">
            <?php
            global $wpdb;

            $query = "SELECT cus_id, cus_name, order_email, order_id, order_total, order_currencycode, order_date, payment_data, (order_total * 0.1) as donation_amount FROM ".$wpdb->prefix."orderdata GROUP BY order_id";

            $results = mysql_query($query) or die(mysql_error().' on line: '.__LINE__);

            $i = 1;
            $total_donated_amount = 0;
            $currency = '';
            
            while ($order = mysql_fetch_object($results)) {
                    //echo "<pre>";    print_r($order);   echo "</pre>";  echo "<br/>";
                if($order->payment_data) {
                    $data = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $order->payment_data);

                    preg_match('/jabber=(.*?)payment_method/', $data,$hits);

                    $organization_all = explode('**', $hits[1]);
                    $organization = $organization_all[6];
                    if ($organization == '') {
                        $all_organization[] = 'XYZ';
                        //continue;
                    } else {
                        $all_organization[] = $organization;
                    }

                }
                
                if ($i%2==0) {
                    echo "<tr>";
                } else {
                    echo "<tr class ='alternate'>";
                }                
                echo "<td>" . $order->cus_id . "</td>";
                echo "<td>" . $order->cus_name . "</td>";
                echo "<td>" . $order->order_email . "</td>";
                echo "<td>" . $order->order_id . "</td>";
                echo "<td>" . $order->order_total . ' ' . $order->order_currencycode . "</td>";
                if ($organization != '') {
                    echo "<td>" . $organization . "</td>";
                } else {
                    echo "<td>" . 'XYZ' . "</td>";
                }
                echo "<td>" . $order->donation_amount. ' ' . $order->order_currencycode . "</td>";
                echo "<td>" . $order->order_date . "</td>";
                echo "<td>" . '&nbsp;'.  "</td>";
                echo "</tr>";
	            $i++;
	            
	            // Code for calulate the final total of the donated ammount
	            $total_donated_amount += $order->donation_amount;
	            $currency = $order->order_currencycode;
	            
            }   // End While loop
                        
            ?>
            
            <tr class ='alternate'>
                <td><b>Total</b></td>
                <td colspan="7">&nbsp;</td>
                <td><b><?php echo $total_donated_amount . ' ' . $currency; ?></b></td>
            </tr>
           </tbody>
        </table>
    </div>  
<?php  
}  
?>
