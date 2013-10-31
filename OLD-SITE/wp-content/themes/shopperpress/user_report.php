<?php
/*
Template Name: Current user report
*/
?>
<?php if ( !is_user_logged_in() ){
    $url = $GLOBALS['bloginfo_url'].'/wp-login.php';
    header('Location: '.$url);
    exit;
}
?>
<style type ="text/css">
#sidebar-left {
    display: none;
}
#submenubar #s {
    height: 28px !important;
}
.itembox h1 {
    width: 138.5% !important;
    font-weight: bold;
    letter-spacing: 0.3px;
}

.entry {
    width: 143%;
}
.table_report {
    width: 100%;
    border: 2px solid #28BDFF;
}
th {
    text-align: center !important;
    border: 1px solid #28BDFF !important;
    padding: 14px !important;
    font-weight: bold !important;
}
.clo {
    text-align: center !important;
    border: 1px solid #28BDFF !important;
    padding: 15px !important;
}
</style>
<?php get_header(); ?>
<div class="itembox">

        <?php
        
        $user_as_organization = 0;
        global $wpdb;
        $query = "SELECT id FROM all_organizations WHERE  org_name ='" . $userdata->user_login . "' GROUP BY id";

        $results = mysql_fetch_array(mysql_query($query));

        if (!empty($results)) {
            $user_as_organization = 1;
        }

        if ($user_as_organization == 1) { ?>
        <!-- =========  Code of Reports only for Orgainizations ===================-->

            <h1 class="title">Donation Summary</h1>
            <div class="itemboxinner article">
            
                <div class="entry">
                
	            <table border ="1" class = "table_report">
	                <tr>
      	                <th>USER ID</th>
                        <th>USER NAME</th>
                        <th>EMAIL</th>
                        <th>DEAL ID</th>
                        <th>PRICE OF DEAL</th>
                        <th>DONATION</th>
                        <th>AMOUNT OF DONATION</th>
                        <th>DATE PAID</th>
                        <th>TOTAL TO DATE</th>
	                </tr>
                <?php 
                    $query = "SELECT cus_id, cus_name, order_email, order_id, order_total, order_currencycode, order_date, payment_data, (order_total * 0.1) as donation_amount FROM ".$wpdb->prefix."orderdata GROUP BY order_id";

                    $results = mysql_query($query, $wpdb->dbh) or die(mysql_error().' on line: '.__LINE__);
                    
	                $total_dontaion = 0;
	                $currency = '';
                    while ($order = mysql_fetch_object($results)) {

	                    $data = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $order->payment_data);
	                    preg_match('/jabber=(.*?)payment_method/', $data,$hits);
	                    $org_all = explode('**', $hits[1]);
	                    
	                    if ((array_key_exists('6', $org_all) && $org_all[6]!= '') && (trim($org_all[6]) == $userdata->user_login)) {
	                        echo "<tr>";
	                        echo "<td class ='clo'>" . $order->cus_id . "</td>";
	                        echo "<td class ='clo' >" . $order->cus_name . "</td>";
	                        echo "<td class ='clo' >" . $order->order_email . "</td>";
	                        echo "<td class ='clo' >" . $order->order_id . "</td>";
	                        echo "<td class ='clo' >" . $order->order_total . ' ' . $order->order_currencycode . "</td>";
                            echo "<td class ='clo' >" . trim($org_all[6]) . "</td>";
	                        echo "<td class ='clo' >" . $order->donation_amount. ' ' . $order->order_currencycode . "</td>";
	                        echo "<td class ='clo' >" . $order->order_date . "</td>";
	                        echo "<td class ='clo'>" . '&nbsp;'.  "</td>";
	                        echo "</tr>";

	                        $total_dontaion += $order->donation_amount;
	                        $currency = $order->order_currencycode;
                        
	                    } else {

	                        continue;
	                    }
                    }
                ?>
                    <tr>
                        <td class ='clo'>Total</td>
                        <td class ='clo' colspan="7">&nbsp;</td>
                        <td class ='clo'><?php if($total_dontaion != '') { echo $total_dontaion . ' ' . $currency; } else { ?> &nbsp;<? } ?></td>
                    </tr>
	            </table>

                </div>
            </div>

        <? } else { ?>
            
            <!-- ========= Code of Reports only for normal users ===================-->
            <h1 class="title"><?php the_title(); ?></h1>
            
            <div class="itemboxinner article">
            
            <div class="entry">
            <?php
	        $currency_symbol = get_option("currency_symbol");
	        $order   = array("\r\n", "\n", "\r");
	        $replace = '<br />'; 
	        //$SQL = "SELECT * FROM ".$wpdb->prefix."orderdata LEFT JOIN $wpdb->users ON ($wpdb->users.ID = ".$wpdb->prefix."orderdata.cus_id)  WHERE ".$wpdb->prefix."orderdata.cus_id = '".$userdata->ID."' GROUP BY order_id";
	
	        $SQL = "SELECT o.cus_id, o.cus_name, o.order_email, o.order_id, o.order_total, o.order_currencycode, o.payment_data, o.order_date, (o.order_total * 0.1) as donation_amount FROM ".$wpdb->prefix."orderdata o LEFT JOIN $wpdb->users u ON (u.ID = o.cus_id)  WHERE o.cus_id = '".$userdata->ID."' GROUP BY order_id";
	
	        $posts = mysql_query($SQL, $wpdb->dbh) or die(mysql_error().' on line: '.__LINE__);
	        ?>
	        <table border ="1" class = "table_report">
	            <tr>
	            <th>USER ID</th>
                    <th>USER NAME</th>
                    <th>EMAIL</th>
                    <th>DEAL ID</th>
                    <th>PRICE OF DEAL</th>
                    <th>DONATION</th>
                    <th>AMOUNT OF DONATION</th>
                    <th>DATE PAID</th>
                    <th>TOTAL TO DATE</th>
	            </tr>

	        <?php
            $all_organization = array();
	        $total_dontaion = 0;
	        $currency = '';
	        while ($order = mysql_fetch_object($posts)) {

	            if($order->payment_data) {
	                $data = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $order->payment_data);

	                preg_match('/jabber=(.*?)payment_method/', $data,$hits);
	                $organization_all = explode('**', $hits[1]);
			        $organization = $organization_all[6];

			        if ($organization == '') {
			            $all_organization[] = 'XYZ';
			        } else {
			            $all_organization[] = $organization;
			        }
                    
	            }

	            echo "<tr>";
	            echo "<td class ='clo'>" . $order->cus_id . "</td>";
	            echo "<td class ='clo' >" . $order->cus_name . "</td>";
	            echo "<td class ='clo' >" . $order->order_email . "</td>";
	            echo "<td class ='clo' >" . $order->order_id . "</td>";
	            echo "<td class ='clo' >" . $order->order_total . ' ' . $order->order_currencycode . "</td>";
	            if ($organization != '') {
	                echo "<td class ='clo' >" . $organization . "</td>";
	            } else {
	                echo "<td class ='clo' >" . 'XYZ' . "</td>";
	            }
	            echo "<td class ='clo' >" . $order->donation_amount. ' ' . $order->order_currencycode . "</td>";
	            echo "<td class ='clo' >" . $order->order_date . "</td>";
	            echo "<td class ='clo'>" . '&nbsp;'.  "</td>";
	            echo "</tr>";
	            $total_dontaion += $order->donation_amount;
	            $currency = $order->order_currencycode;

	        } ?>
                <tr>
                    <td class ='clo'>Total</td>
                    <td class ='clo' colspan="7">&nbsp;</td>
                    <td class ='clo'><?php echo $total_dontaion . ' ' . $currency; ?></td>
                </tr>
	        </table>
	
            </div>

	        </div>

        <div class="clearfix"></div>
        
        
      <?  } ?>


<!-- ====================== Code for add the Donation summary table  =================================-->
<!--
<?php  //print_r($all_organization);
        $donation_summary[] = array();  ?>
        <h1 class="title">DONATION SUMMARY REPORT</h1>
        <div class="itemboxinner article">
         <div class="entry">

    <?php
        $query = "SELECT cus_id, cus_name, order_email, order_id, order_total, order_currencycode, order_date, payment_data, (order_total * 0.1) as donation_amount FROM ".$wpdb->prefix."orderdata GROUP BY order_id";

        $results = mysql_query($query, $wpdb->dbh) or die(mysql_error().' on line: '.__LINE__);
        
        while ($order = mysql_fetch_object($results)) {

	        $data = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $order->payment_data);

	        preg_match('/jabber=(.*?)payment_method/', $data,$hits);
	        $org_all = explode('**', $hits[1]);

	        if (array_key_exists('6', $org_all)) {
	            foreach ($all_organization as $org ) {
	            
	                if ($org == 'XYZ') {
	                    continue;
	                } else {
	                
	                    if ($org == $org_all[6]) {
	                        
	                        $donation_summary[] = array (
	                            'cus_id'        => $order->cus_id,
	                            'usename'       => $order->cus_name,
	                            'email'         => $order->order_email,
	                            'order_id'      => $order->order_id,
	                            'price'         => $order->order_total . ' ' . $order->order_currencycode,
	                            'organization'  => $org_all[6],
	                            'donation_amt'  => $order->donation_amount. ' ' . $order->order_currencycode,
	                            'date_donation' => $order->order_date
	                        );

	                    }
	                }

	            }

	        } else {
	            continue;
	        }
            //print_r($org_all);
        }
        
        $dna_sm = array_shift($donation_summary);
        //echo "<pre>";   print_r($donation_summary);     echo "</pre>";
        ?>

	<table border ="1" class = "table_report">
	    <tr>
	        <th>USER ID</th>
            <th>USER NAME</th>
            <th>EMAIL</th>
            <th>DEAL ID</th>
            <th>PRICE OF DEAL</th>
            <th>DONATION</th>
            <th>AMOUNT OF DONATION</th>
            <th>DATE PAID</th>
            <th>TOTAL TO DATE</th>
	    </tr>

        <?php   $total_donated_amount = 0;  
        foreach($donation_summary as $ds) {
	        echo "<tr>";
	        echo "<td class ='clo'>" . $ds['cus_id'] . "</td>";
	        echo "<td class ='clo' >" . $ds['usename'] . "</td>";
	        echo "<td class ='clo' >" . $ds['email'] . "</td>";
	        echo "<td class ='clo' >" . $ds['order_id'] . "</td>";
	        echo "<td class ='clo' >" . $ds['price']. "</td>";
            echo "<td class ='clo' >" . $ds['organization'] . "</td>";
	        echo "<td class ='clo' >" . $ds['donation_amt'] . "</td>";
	        echo "<td class ='clo' >" . $ds['date_donation'] . "</td>";
	        echo "<td class ='clo'>" . '&nbsp;'.  "</td>";
	        echo "</tr>";
	        
	        $total_donated_amount += $ds['donation_amt'];

        } ?>
        <tr>
            <td class ='clo'>Total</td>
            <td class ='clo' colspan="7">&nbsp;</td>
            <td class ='clo'><?php echo $total_donated_amount . ' ' . $currency; ?></td>
        </tr>
      </table>
    </div>
    </div>
-->
 <!-- ====================== End Code for add the Donation summary table  =================================-->

</div>
<?php
    get_footer();
?>
