<?php 
	if ( empty($columns) || empty($records)) {
		self::_e('No Data');
	} else {
		global $gb_report_pages;
		
		//Get report instance
		$report = Group_Buying_Reports::get_instance( $_GET['report'] );
	?>

<style type="text/css" media="screen">
	.report_nav_button.active .report_button { background-position: left -89px; color: #000; }
	.report_nav_button .report_button { background-position: left -30px; text-shadow: none; border: none; margin-right: 10px;}
</style>

<div style="border-bottom: 1px dotted #ccc; border-bottom: 1px dotted rgba(0,0,0,0.2); padding-bottom: 5px; margin-bottom: 10px;">
<h3>Total Donations: <?php echo gb_get_formatted_money(GBS_SF_Charity_Reports::get_charity_total_donations( $_GET['id'] )); ?></h3>
</div>

<div id="report_navigation clearfix">
  <?php
	
  	$date_range = array(1,7,30,60,90,120,365);
	foreach ($date_range as $range) {
		$active = ($range == $_GET['range']) ? 'active' : '' ;
		$button = '<span class="report_nav_button '.$active.'"><a class="report_button alt_button font_small" href="'.add_query_arg( array( 'report' => $_GET['report'], 'id' => $_GET['id'], 'range' => $range ), $report->get_url()).'">'.sprintf(self::__('Previous %s Days'),$range).'</a></span>';
		echo $button;
	}
   ?>
</div>
<div class="report">
<table>
	<thead>
		<tr>
		<?php foreach ( $columns as $key => $label ): ?>
			<th class="cart-<?php esc_attr_e($key); ?>" scope="col"><?php esc_html_e($label); ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $records as $record ): ?>
			<tr>
				<?php foreach ( $columns as $key => $label ): ?>
					<td class="cart-<?php esc_attr_e($key); ?>">
						<?php if ( isset($record[$key]) ) { echo $record[$key]; } ?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
<div id="report_navigation pagination clearfix">
  <?php
  	if ( $gb_report_pages > 1 ) {
  		$report = Group_Buying_Reports::get_instance( $_GET['report'] );
		$report_url = $report->get_url();
		$current_page = $_GET['showpage']-1;
		for ($i=0; $i < $gb_report_pages; $i++) {
			$page_num = (int)$i; $page_num++;
			$active = ( $i == $current_page || ($i == 0) && !isset($_GET['showpage'])) ? 'active' : '' ;
			$button = '<span class="report_nav_button '.$active.'"><a class="report_button button contrast_button" href="'.add_query_arg( array( 'report' => $_GET['report'], 'id' => $_GET['id'], 'showpage' => $i ), $report_url).'">'.$page_num.'</a></span> ';
			echo $button;
		}
  	}
   ?>
</div>
<?php
	}
	 ?>