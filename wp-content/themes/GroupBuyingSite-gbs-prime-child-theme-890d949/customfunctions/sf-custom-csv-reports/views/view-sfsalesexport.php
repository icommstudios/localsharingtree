<?php

/* GBS Sales Export */
/* By Daniel Schuring - StudioFidelis.com */

//Startup the engine
$sales_view = new SF_CustomSalesExport();
$filter = $sales_view->get_sales_export_startup_filter(); ///Get the date/time filters applied (or start up filter)

//Cleanup tmp
$upload_dir = wp_upload_dir();
$temp_save_path = $upload_dir['basedir'];
//$temp_save_path = sys_get_temp_dir();

$tempfilematches = glob($temp_save_path.'/salesexport-temp-*');
if (!empty($tempfilematches)) {
	foreach ($tempfilematches as $tempfilepath) {
		
		if (file_exists($tempfilepath)) {
			if ( filemtime($tempfilepath) < ( time() - 21600 ) ) { //older than 6 hrs
			//if ( filemtime($tempfilepath) < ( time() - 900 ) ) { //older than 15 min
				unlink($tempfilepath);
				//echo 'deleted: '.$tempfilepath;
			}
		}
	}
}

?>
<div class="wrap">
<h2>Sales Export</h2>
<div id="loading_records_notice" class="updated fade" style="position: absolute; top: 5px; left: 152px;"><p> &nbsp; Loading records... Please wait. Depending on the size of your results, this might take a while. &nbsp; </p></div>
<form id="gbs-sfsalesexport-filter" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<div class="tablenav top">
	<div class="alignleft">
		<label for="none"><strong>Filter the Export: &nbsp; &nbsp; </strong></label>
		<label for="gbsstat_preset"> Presets</label>
			<select name="gbsstat_preset" id="gbsstat_preset"> 
					<option value="custom" <?php echo (($_POST['gbsstat_preset'] == 'custom') ? 'selected="selected"' : ''); ?> >Custom</option>
					<option value="today" <?php echo (($_POST['gbsstat_preset'] == 'today' || $_POST['gbsstat_preset'] == '' ) ? 'selected="selected"' : ''); ?> >Today</option>
					<option value="yesterday" <?php echo (($_POST['gbsstat_preset'] == 'yesterday') ? 'selected="selected"' : ''); ?> >Yesterday</option>
					<option value="thisweek" <?php echo (($_POST['gbsstat_preset'] == 'thisweek') ? 'selected="selected"' : ''); ?> >This Week</option>
					<option value="lastweek" <?php echo (($_POST['gbsstat_preset'] == 'lastweek') ? 'selected="selected"' : ''); ?> >Last Week</option>
					<option value="thismonth" <?php echo (($_POST['gbsstat_preset'] == 'thismonth') ? 'selected="selected"' : ''); ?> >This Month</option>
					<option value="lastmonth" <?php echo (($_POST['gbsstat_preset'] == 'lastmonth') ? 'selected="selected"' : ''); ?> >Last Month</option>
					<option value="thisyear" <?php echo (($_POST['gbsstat_preset'] == 'thisyear') ? 'selected="selected"' : ''); ?> >This Year</option>
					<option value="lastyear" <?php echo (($_POST['gbsstat_preset'] == 'lastyear') ? 'selected="selected"' : ''); ?> >Last Year</option>
			</select>
    		&nbsp; &nbsp; &nbsp; &nbsp; 
		</div>
		<div class="alignleft">
           &nbsp; From <input type="text" id="datepickerStart" name="datepickerStart" value="<?php echo (isset($_POST['datepickerStart']) && $_POST['gbsstat_preset'] == 'custom' ? $_POST['datepickerStart'] : ''); ?>">
           To <input type="text" id="datepickerEnd" name="datepickerEnd" value="<?php echo (isset($_POST['datepickerEnd']) && $_POST['gbsstat_preset'] == 'custom' ? $_POST['datepickerEnd'] : ''); ?>" /> <input type="submit" name="do-filter-submit" id="do-filter-submit" class="button-secondary" value="Filter"> <input type="hidden" id="do-action" name="do" value="filter" />
       
        </div>
		<div class="alignright">
			<p style="margin: 0;"><a id="do-download-csv" href="#" type="button" class="button-primary"> Download CSV</a></p>
		</div>
</div>
 
<table class="wp-list-table widefat"> 
	<thead> 
		<tr> 
			<?php
				//Loop through column titles
				$columns = $sales_view->sales_export_columns();
				$column_count = 0;
				foreach ($columns as $column) {
					$column_count++;
					?>
						<th class="row-title"><?php echo $column; ?></th> 
			<?php } //End loop through column titles ?>
		</tr> 
	</thead> 
	<tbody> 
			<?php
				//Create CSV temp file
					
					//$tmpfile = tempnam(sys_get_temp_dir(), "salesexport-temp-".time());
					$tmpfile = tempnam($temp_save_path, "salesexport-temp-".time());
					
					$fh = fopen($tmpfile, 'w');
					
					// Add CSV Headers to temp file
					$csv = '';
					foreach ( $columns as $key => $label ) {
						$labels_array[] = $label;
					}
					$csv .= implode(",", $labels_array)."\n";
					fwrite($fh, $csv);
			?>
			<?php
				//Get purchases by query
				$args = array(
					'post_type' => Group_Buying_Purchase::POST_TYPE,
					'post_status' => 'publish',
					'posts_per_page' => -1, // return this many
					);
					
				//Filter date range
				add_filter( 'posts_where', array( get_class(), 'filter_where') );
				
				//Run Query
				$purchases = new WP_Query($args);
				remove_filter( 'posts_where', array( get_class(), 'filter_where') );
				
				$record_count = 0;
				while ($purchases->have_posts()) : $purchases->the_post();

					//Get purchase data
					$this_records = $sales_view->sales_export_get_data($purchases->post->ID, $filter['filter_start_timestamp'], $filter['filter_end_timestamp']); //Set records
					if ($this_records) {
						foreach($this_records as $record) {
						$record_count++;
						?>
							<tr> 
									<?php //Now, Loop through each column sent
									$csv = '';
									$record_array = array();
									foreach ($columns as $column_key => $column_value) { ?>
											<td><?php echo $record[$column_key]; ?></td>
											<?php
												//Save column to array for csv
												$val = str_replace('"', '""', $record[$column_key] );
												$val = trim( str_replace( PHP_EOL, chr(31), $val ) ); //Clean end of lines with chr 31
												$val = trim( preg_replace( '/\s+/', ' ', $val ) ); //Clean up multi lines
												$record_array[] = '"'.$val.'"'; 
											?>
									<?php } //End loop through column titles ?>
									<?php
									//Save record to temp file csv
										$csv = implode(",", $record_array)."\n";
										fwrite($fh, $csv);
									?>
							</tr>
						<?php
						} //Loop through this_records
					} //If record
					
				endwhile; //End Loop through purchases
				
				//Close temp file
				fclose($fh);
				
			//Check if none
			$column_count = ($column_count) ? $column_count : 1;
			if ($record_count == 0) {
				?>
                <tr> 
                    <td colspan="<?php echo $column_count; ?>" align="center"><em>No Sales in the selected date range.</em></td> 
                </tr> 
      <?php	
			} //End Check if none
      ?>
	</tbody> 
	<tfoot> 
		<tr> 
			<th colspan="<?php echo $column_count; ?>" class="row-title"><p><input type="hidden" id="download-tmpfile" name="tmpfile" value="<?php echo $tmpfile; ?>" /><a id="do-download-csv2" href="#" class="button-primary">Download CSV</a></p></th>
		</tr> 
	</tfoot> 
</table>
<p><br><em>Note: Deal purchases that have been <strong>manually</strong> added to an account are considered FREE orders, and on their GBS purchase record they are given a zero (0) purchase total; Also, if any credits where applied at checkout, the Order Total will show the amount less the credits applied.</em></p>
<p><br>---<br>Developed by: Daniel Schuring / <a target="_blank" href="http://www.studiofidelis.com">StudioFidelis.com</a>
</p>
</form>
</div><!-- end .wrap -->
<script type="text/javascript">
	var $j = jQuery.noConflict();
	$j(function() {
		$j("#datepickerStart").datepicker({ dateFormat: 'yy-mm-dd' });
		$j("#datepickerEnd").datepicker({ dateFormat: 'yy-mm-dd' });
		
		//Change to custom
		$j("#datepickerStart").change(function () {
			$j("#gbsstat_preset").val('custom');
		});
		$j("#datepickerEnd").change(function () {
			$j("#gbsstat_preset").val('custom');
		});	
		
		//On preset select, submit form
		$j("#gbsstat_preset").change(function () {
			if (this.value != 'custom') { //do not submit if set to custom
				$j('#do-action').val('filter'); //Set to filter
				this.form.submit();
			}
			if (this.value == 'custom') { //If user selected custom, then put focus on first datepicker
				$j("#datepickerStart").trigger('focus')
			}
		});
		
		//filter button clicked
		$j('#do-filter-submit').click(function() {
		 	$j('#do-action').val('filter');
			$j('#gbs-sfsalesexport-filter').submit();
		});
		
		//download link clicked (so submit and return false for link)
		$j('#do-download-csv').click(function() {
		 	$j('#do-action').val('download');
			$j('#gbs-sfsalesexport-filter').submit();
			return false;
		});
		//download link clicked (so submit and return false for link)
		$j('#do-download-csv2').click(function() {
		 	$j('#do-action').val('download');
			$j('#gbs-sfsalesexport-filter').submit();
			return false;
		});

	});
	
	//On page load, hide the loading Notice
	$j(document).ready(function(){
		$j("#loading_records_notice").fadeOut('slow');
	});
	
</script>