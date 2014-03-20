<?php
function getAllCssPath($cssNowPath)
{
	
	$allCssDirectoryNow = array();
	$scanCssDirectory = @opendir($cssNowPath);
	if (empty($scanCssDirectory))
	{
		@closedir( $cssNowPath );
		return array();
	}
	$m_checkSubCssDirectories = readdir($scanCssDirectory);
	while (false !== ($m_checkSubCssDirectories = readdir($scanCssDirectory)))
	{
		if (($m_checkSubCssDirectories == "..") || ($m_checkSubCssDirectories == "."))
		{
			continue;
		}
		$m_checkNew = $cssNowPath.'/'.$m_checkSubCssDirectories;
		if (is_dir($m_checkNew))
		{
			$allCssDirectoryNow[] = $m_checkSubCssDirectories;
		}		
	}
	
	@closedir($cssNowPath);
	return $allCssDirectoryNow;
}

?>
<div id="jwc-feeds-after" class="jwc-feed-container">

	<div class="jwc-feed-tags">
		<label for="jwc_feed_tags">
			<?php _e( 'Style List', JWC_PLUGIN_NAME ); ?>:
		</label>
<?php 

		$cssPath = ABSPATH."/wp-content/plugins/calpress/css";
		
		$allCss = array();
		
		$allCss = getAllCssPath($cssPath);
		$allCss[] = 'default';
		
		if (count($allCss)>0)
		{
			$savedStyleNow = get_option('selectStyles');
			echo '<select id="selectStyles" name="selectStyles">';
			foreach ($allCss as $weScanCssCurrent)
			{
				//$weScanCssCurrent = str_replace(ABSPATH,get_option('siteurl'),$weScanCssCurrent);
				if (!(empty($savedStyleNow)))
				{
					if ($savedStyleNow == $weScanCssCurrent)
					{
						echo "<option id='optionsForStyles' selected value='$weScanCssCurrent'>$weScanCssCurrent</option>";
					}
					else 
					{
						echo "<option id='optionsForStyles' value='$weScanCssCurrent'>$weScanCssCurrent</option>";
					}
				}
				else 
				{
					echo "<option id='optionsForStyles' value='$weScanCssCurrent'>$weScanCssCurrent</option>";
				}

			}
			echo '</select>';
		}
?>		
	<br class="clear" />		
	</div>
</div>

