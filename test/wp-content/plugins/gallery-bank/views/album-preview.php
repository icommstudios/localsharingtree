<?php
global $wpdb;
include GALLERY_BK_PLUGIN_DIR . "/views/includes_common_before.php";
?>
<!--suppress ALL -->
<form id="preview_album" class="layout-form" method="post">
	<div class="fluid-layout">
		<div class="layout-span12">
			<ul class="breadcrumb">
				<li>
					<i class="icon-home"></i>
					<a href="admin.php?page=gallery_bank"><?php _e("Gallery Bank", gallery_bank); ?></a>
					<span class="divider">/</span>
			 		<a href="#"><?php _e("Album Preview", gallery_bank); ?></a>
				</li>
			</ul>
			<div class="widget-layout">
				<div class="widget-layout-title">
					<h4>
						<i class="icon-plus"></i>
						<?php _e("Album Preview", gallery_bank); ?>
					</h4>
				</div>
				<div class="widget-layout-body">
					<a class="btn btn-inverse" href="admin.php?page=gallery_bank"><?php _e("Back to Albums", gallery_bank); ?></a>
					<div class="separator-doubled"></div>
					<div class="fluid-layout">
						<div class="layout-span12">
							<div class="widget-layout">
								<div class="widget-layout-title">
									<h4><?php echo stripcslashes(htmlspecialchars_decode($album)); ?></h4>
								</div>
								<div class="layout-control-group">
									<ul class="breadcrumb">
										<li>
											<label class="layout-control-label">
												<strong>
													<?php _e("Images in Row", gallery_bank); ?> :
												</strong>
											</label>
											<select id="ux_ddl_ImagesRow" class="layout-span3" style="margin-left: 16px;"
												onchange="select_imges_in_row();">
												<option id="" value=""><?php _e("Please Choose", gallery_bank); ?></option>
													<?php
													for ($i = 1; $i <= 10; $i++):
													?>
														<option <?php if ($i == $img_in_row) echo "selected=\"selected\"" ?>
														value="<?php echo $i ?>"><?php echo $i; ?></option>
													<?php
													endfor;
												?>
											</select>
										</li>
									</ul>
								</div>
								<div class="widget-layout-body">
									<div id="gallery_bank_container" class="gallery_images">
										<?php
										for ($flag = 0;$flag < count($pics);$flag++)
										{
                                            $image_title = $image_desc_setting == 1 && $pics[$flag]->title != "" ? "<h5>" . html_entity_decode(stripcslashes(htmlspecialchars($pics[$flag]->title))). "</h5>" : "";
                                            $image_description = $image_desc_setting == 1 && $pics[$flag]->description != ""  ? "<p>" . html_entity_decode(stripcslashes(htmlspecialchars($pics[$flag]->description))) ."</p>" : "";
                                            if ($pics[$flag]->url == "" || $pics[$flag]->url == "undefined" || $pics[$flag]->url == "http://")
											{
												if ($pics[$flag]->video == 1)
												{
													?>
													<a rel="prettyPhoto[gallery]" href="<?php echo stripcslashes($pics[$flag]->pic_name); ?>"
													data-title="<?php echo $image_title . $image_description; ?>" id="ux_img_div">
													<?php
												}
												else
												{
													?>
													<a rel="prettyPhoto[gallery]"
														href="<?php echo stripcslashes(GALLERY_BK_THUMB_URL . $pics[$flag]->thumbnail_url); ?>"
														data-title="<?php echo $image_title . $image_description; ?>" id="ux_img_div">
													<?php
												}
											}
											else
											{
												?>
												<a href="<?php echo $pics[$flag]->url; ?>" id="ux_img_div" target="_blank"
												data-title="<?php echo $image_title; ?>">
												<?php
											}
											?>
												<div class="imgLiquidFill dynamic_css">
													<?php
													if ($pics[$flag]->video == 1) {
														?>
														<img imageid="<?php echo $pics[$flag]->pic_id; ?>" id="ux_gb_img" type="video"
	                                                    src="<?php echo stripcslashes($video_thumb_url); ?>"/>
		                                            <?php
		                                            } else {
		                                                ?>
		                                                <img imageid="<?php echo $pics[$flag]->pic_id; ?>"
		                                                    id="ux_gb_img" type="image" src="<?php echo stripcslashes(GALLERY_BK_THUMB_SMALL_URL . $pics[$flag]->thumbnail_url); ?>"/>
		                                            <?php
		                                            }
		                                            ?>
												</div>
											</a>
	                                      <?php
										}
                                    	?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

<?php
include GALLERY_BK_PLUGIN_DIR . "/views/includes_common_after.php";
?>
<script type="text/javascript">
    function select_imges_in_row() {
        var row = jQuery("#ux_ddl_ImagesRow").val();
        window.location.href = "<?php echo site_url();?>/wp-admin/admin.php?page=album_preview&album_id=<?php echo $album_id;?>&row=" + row;
    }
</script>
	