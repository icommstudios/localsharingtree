<?php
global $wpdb;

$album_id = intval($_REQUEST["album_id"]);
$album = $wpdb->get_row
(
    $wpdb->prepare
    (
        "SELECT * FROM " . gallery_bank_albums() . " where album_id = %d",
        $album_id
    )
);
$pics = $wpdb->get_results
(
    $wpdb->prepare
    (
        "SELECT * FROM " . gallery_bank_pics() . " WHERE album_id = %d order by sorting_order asc ",
        $album_id
    )
);

$album_css = $wpdb->get_results
(
    $wpdb->prepare
    (
        "SELECT * FROM " . gallery_bank_settings(), ""
    )
);
if (count($album_css) != 0) {
    $setting_keys = array();
    for ($flag = 0; $flag < count($album_css); $flag++) {
        array_push($setting_keys, $album_css[$flag]->setting_key);
    }

    $index = array_search("thumbnails_width", $setting_keys);
    $thumbnails_width = $album_css[$index]->setting_value;

    $index = array_search("thumbnails_height", $setting_keys);
    $thumbnails_height = $album_css[$index]->setting_value;

    $index = array_search("thumbnails_opacity", $setting_keys);
    $thumbnails_opacity = $album_css[$index]->setting_value;

    $index = array_search("thumbnails_border_size", $setting_keys);
    $thumbnails_border_size = $album_css[$index]->setting_value;

    $index = array_search("thumbnails_border_radius", $setting_keys);
    $thumbnails_border_radius = $album_css[$index]->setting_value;

    $index = array_search("thumbnails_border_color", $setting_keys);
    $thumbnails_border_color = $album_css[$index]->setting_value;

    $index = array_search("cover_thumbnail_width", $setting_keys);
    $cover_thumbnail_width = $album_css[$index]->setting_value;

    $index = array_search("cover_thumbnail_height", $setting_keys);
    $cover_thumbnail_height = $album_css[$index]->setting_value;

    $video_thumb_name = $album_css[$index]->setting_value;
	$video_url = $video_thumb_name == "video.jpg" ? GALLERY_BK_PLUGIN_URL . '/assets/images/video.jpg' : GALLERY_BK_THUMB_URL.$video_thumb_name ;

    ?>
    <!--suppress ALL -->
    <style type="text/css">
        .dynamic_css {
            border: <?php echo $thumbnails_border_size;?>px solid <?php echo $thumbnails_border_color;?>;
            border-radius: <?php echo $thumbnails_border_radius;?>px;
            -moz-border-radius: <?php echo $thumbnails_border_radius; ?>px;
            -webkit-border-radius: <?php echo $thumbnails_border_radius;?>px;
            -khtml-border-radius: <?php echo $thumbnails_border_radius;?>px;
            -o-border-radius: <?php echo $thumbnails_border_radius;?>px;
            opacity: <?php echo $thumbnails_opacity;?>;
            -moz-opacity: <?php echo $thumbnails_opacity; ?>;
            -khtml-opacity: <?php echo $thumbnails_opacity; ?>;
        }
    </style>

    <form id="edit_album" class="layout-form">
		<div class="fluid-layout">
			<div class="layout-span12">
				<ul class="breadcrumb">
					<li>
						<i class="icon-home"></i>
						<a href="admin.php?page=gallery_bank"><?php _e("Gallery Bank", gallery_bank); ?></a>
						<span class="divider">/</span>
						<a href="#"><?php _e("Edit Album", gallery_bank); ?></a>
					</li>
				</ul>
				<div class="widget-layout">
					<div class="widget-layout-title">
						<h4>
							<i class="icon-plus"></i>
							<?php _e("Edit Album", gallery_bank); ?>
						</h4>
					</div>
					<div class="widget-layout-body">
						<a class="btn btn-inverse" href="admin.php?page=gallery_bank"><?php _e("Back to Albums", gallery_bank); ?></a>
						<button type="submit" class="btn btn-info" style="float:right"><?php _e("Update Album", gallery_bank); ?></button>
						<div class="separator-doubled"></div>
						<div id="update_album_success_message" class="message green" style="display: none;">
							<span>
								<strong><?php _e("Album Updated. Kindly wait for the redirect to happen.", gallery_bank); ?></strong>
							</span>
						</div>
						<div class="fluid-layout">
							<div class="layout-span6">
								<div class="widget-layout">
									<div class="widget-layout-title">
										<h4><?php _e("Album Details", gallery_bank); ?></h4>
									</div>
					                <div class="widget-layout-body">
					                    <div class="layout-control-group">
					                        <label class="layout-control-label"><?php _e("Album Title", gallery_bank); ?> :</label>
					                        <div class="layout-controls">
					                            <input type="text" name="ux_edit_title" class="layout-span12"
				                                   value="<?php echo stripcslashes(htmlspecialchars_decode($album->album_name)); ?>"
				                                   id="ux_edit_title"
				                                   placeholder="<?php _e("Enter your Album Title", gallery_bank); ?>"/>
					                        </div>
					                    </div>
					                    <input type="hidden" id="ux_hidden_album_id" value="<?php echo $album_id; ?>"/>
					                </div>
					                <div class="widget-layout-body">
					                    <div class="layout-control-group">
					                        <label class="layout-control-label"><?php _e("Description", gallery_bank); ?> :</label>
					                    </div>
					                    <div class="layout-control-group">
					                        <?php
					                        $ux_content = stripslashes(htmlspecialchars_decode($album->description));
					                        wp_editor($ux_content, $id = "ux_edit_description", $media_buttons = true, $tab_index = 1);
					                        ?>
					                    </div>
					                </div>
					            </div>
					        </div>
					        <div class="layout-span6">
					            <div class="widget-layout">
					                <div class="widget-layout-title">
					                    <h4><?php _e("Upload Images", gallery_bank); ?></h4>
					                </div>
					                <div class="widget-layout-body" id="edit_image_uploader">
					                    <p><?php _e("Your browser doesn\"t have Flash, Silverlight or HTML5 support.", gallery_bank) ?></p>
					                </div>
					            </div>
					        </div>
					        <div class="layout-span6">
					            <div class="widget-layout">
					                <div class="widget-layout-title">
					                    <h4><?php _e("Upload Videos", gallery_bank); ?>
					                    	<i class="widget_premium_feature"><?php _e(" (Available in Premium Versions)", gallery_bank); ?></i>
					                    </h4>
					                </div>
					                <div class="widget-layout-body" id="edit_video_uploader">
					                    <div class="layout-control-group">
					                        <label class="layout-control-label"><?php _e("Video Url", gallery_bank); ?> :</label>
					                        <div class="layout-controls">
					                            <input type="text" name="ux_edit_txt_video_url" class="layout-span12" value=""
				                                   id="ux_edit_txt_video_url"
				                                   placeholder="<?php _e("Enter your Video Url", gallery_bank); ?>"/>
					                        </div>
					                    </div>
					                    <div class="layout-control-group">
					                        <div class="layout-controls">
					                            <button type="button" onclick="insertVideoToDataTable();" style="float:right"
					                                    class="btn btn-info"><?php _e("Upload Video", gallery_bank); ?></button>
					                        </div>
					                    </div>
					                </div>
					            </div>
					        </div>
						</div>
						<div class="fluid-layout">
							<div class="layout-span12">
								<div class="widget-layout">
									<div class="widget-layout-title">
										<h4><?php _e("Your Gallery Bank Album", gallery_bank); ?></h4>
									</div>
									<div class="widget-layout-body">
										<table class="table table-striped " id="data-table-edit-album">
											<thead>
												<tr>
						                            <th style="width:11%">
						                                <input type="checkbox" id="grp_select_items" name="grp_select_items" style="vertical-align:middle;"/>
						                                <button type="button" onclick="deleteSelectedImages();" style="vertical-align:middle;"
						                                        class="btn btn-inverse"><?php _e("Delete", gallery_bank); ?></button>
						                            </th>
						                            <th style="width:15%">
						                                <?php _e("Thumbnail", gallery_bank); ?>
						                            </th>
						                            <th style="width:25%">
						                                <?php _e("Title & Description", gallery_bank); ?>
						                            </th>
						                            <th style="width:20%">
						                                <?php _e("Tags (comma separated list)", gallery_bank); ?>
						                                <i class="widget_premium_feature"><?php _e(" (Available in Premium Versions)", gallery_bank); ?></i>
						                            </th>
						                            <th style="width:25%">
						                                <?php _e("Url to Redirect on click of an Image", gallery_bank); ?>
						                            </th>
						                            <th style="width:5%"></th>
						                        </tr>
											</thead>
                        					<tbody>
												<?php
												for ($flag = 0; $flag < count($pics); $flag++) {
													?>
													<tr>
														<?php
														if ($pics[$flag]->video == 1) {
														?>
															<td>
																<input type="checkbox" id="ux_grp_select_items" name="ux_grp_select_items"
																value="<?php echo $pics[$flag]->pic_id; ?>" control="edit"/>
															</td>
															<td>
																<a href="javascript:void(0);" title="<?php echo $pics[$flag]->pic_name; ?>">
																	<img imageid="<?php echo $pics[$flag]->pic_id; ?>" type="video"
																	imgpath="<?php echo $pics[$flag]->pic_name; ?>"
																	src="<?php echo stripcslashes($video_url); ?>" id="ux_gb_img"
																	name="ux_gb_img" width="<?php echo $thumbnails_width; ?>px;"
																	class="edit dynamic_css" picId="<?php echo $pics[$flag]->pic_id; ?>"/>
																</a><br/>
																<?php $dateFormat = date("F j, Y", strtotime($pics[$flag]->date)); ?>
																<label><strong>Video</strong></label><br/><label><?php echo $dateFormat; ?></label>
															</td>
															<td>
																<input placeholder="<?php _e("Enter your Title", gallery_bank) ?>"
																	class="layout-span12 " type="text"
																	name="ux_edit_video_title_<?php echo $pics[$flag]->pic_id; ?>"
																	id="ux_edit_video_title_<?php echo $pics[$flag]->pic_id; ?>"
																	value="<?php echo html_entity_decode(stripcslashes(htmlspecialchars($pics[$flag]->title))); ?>"/>
																<textarea placeholder=" <?php _e("Enter your Description ", gallery_bank) ?>"
																	style="margin-top:20px" rows="5" class="layout-span12"
																	name="ux_txt_desc_<?php echo $pics[$flag]->pic_id; ?>"
																	id="ux_txt_desc_<?php echo $pics[$flag]->pic_id; ?>"><?php echo html_entity_decode(stripcslashes(htmlspecialchars($pics[$flag]->description))); ?></textarea>
															</td>
															<td>
																<input placeholder="<?php _e("Enter your Tags", gallery_bank) ?>"
																class="layout-span12"  type="text" readonly="readonly"
																name="ux_edit_txt_tags_<?php echo $pics[$flag]->pic_id; ?>"
																id="ux_edit_txt_tags_<?php echo $pics[$flag]->pic_id; ?>" onkeypress="return preventDot(event);"
																value="" />
															</td>
															<td>
															</td>
															<td>
																<a class="btn hovertip " id="ux_btn_delete" style="cursor: pointer;"
																data-original-title="<?php _e("Delete Video", gallery_bank) ?>"
																onclick="deleteImage(this);" type="edit"
																controlId="<?php echo $pics[$flag]->pic_id; ?>">
																	<i class="icon-trash"></i>
																</a>
															</td>
														<?php
														} else {
														?>
															<td>
																<input type="checkbox" id="ux_grp_select_items" name="ux_grp_select_items"
																value="<?php echo $pics[$flag]->pic_id; ?>" control="edit"/>
															</td>
															<td>
																<a href="javascript:void(0);" title="<?php echo $pics[$flag]->pic_name; ?>">
																	<img type="image" imgpath="<?php echo $pics[$flag]->thumbnail_url; ?>"
																		src="<?php echo stripcslashes(GALLERY_BK_THUMB_SMALL_URL . $pics[$flag]->thumbnail_url); ?>"
																		id="ux_gb_img" imageid="<?php echo $pics[$flag]->pic_id; ?>"
																		name="ux_gb_img" class="edit dynamic_css"
																		width="<?php echo $thumbnails_width ?>"/>
																</a>
																<br/>
						                                        <?php $dateFormat = date("F j, Y", strtotime($pics[$flag]->date)); ?>
						                                        <label><strong><?php echo $pics[$flag]->pic_name; ?></strong></label><br/><label><?php echo $dateFormat; ?></label><br/>
						                                        <?php
						                                        if ($pics[$flag]->album_cover == 1) {
						                                            ?>
						                                            <input type="radio" style="cursor: pointer;" checked="checked"
					                                                   id="ux_edit_rdl_cover_<?php echo $pics[$flag]->pic_id; ?>"
					                                                   name="ux_album_cover"/>
						                                            <label><?php _e(" Set as Album Cover", gallery_bank) ?></label>
						                                        <?php
						                                        } else {
						                                            ?>
						                                            <input type="radio" style="cursor: pointer;"
					                                                   id="ux_edit_rdl_cover_<?php echo $pics[$flag]->pic_id; ?>"
					                                                   name="ux_album_cover"/>
						                                            <label><?php _e(" Set as Album Cover", gallery_bank) ?></label>
						                                        <?php
						                                        }
						                                        ?>
						                                    </td>
						                                    <td>
						                                        <input placeholder="<?php _e("Enter your Title", gallery_bank) ?>"
					                                               class="layout-span12 edit" type="text"
					                                               name="ux_edit_img_title_<?php echo $pics[$flag]->pic_id; ?>"
					                                               id="ux_edit_img_title_<?php echo $pics[$flag]->pic_id; ?>"
					                                               value="<?php echo html_entity_decode(stripcslashes(htmlspecialchars($pics[$flag]->title))); ?>"/>
						                                        <textarea placeholder="<?php _e("Enter your Description ", gallery_bank) ?>"
				                                                   style="margin-top:20px" rows="5" class="layout-span12 edit"
				                                                   name="ux_edit_txt_desc_<?php echo $pics[$flag]->pic_id; ?>"
				                                                   id="ux_edit_txt_desc_<?php echo $pics[$flag]->pic_id; ?>"><?php echo html_entity_decode(stripcslashes(htmlspecialchars($pics[$flag]->description))); ?></textarea>
						                                    </td>
						                                    <td>
						                                        <input placeholder="<?php _e("Enter your Tags", gallery_bank) ?>"
					                                               class="layout-span12 edit" type="text" onkeypress="return preventDot(event);"
					                                               name="ux_edit_txt_tags_<?php echo $pics[$flag]->pic_id; ?>"
					                                               id="ux_edit_txt_tags_<?php echo $pics[$flag]->pic_id; ?>" readonly="readonly"
					                                               value=""/>
						                                    </td>
						                                    <td>
						                                        <?php
						                                        if ($pics[$flag]->url == "" || $pics[$flag]->url == "undefined") {
						                                            $domain = "http://";
						                                        } else {
						                                            $domain = str_replace("http://http://", "http://", $pics[$flag]->url);
						                                        }
						                                        ?>
						                                        <input value="<?php echo $domain; ?>" type="text"
					                                               id="ux_edit_txt_url_<?php echo $pics[$flag]->pic_id; ?>"
					                                               name="ux_edit_txt_url_<?php echo $pics[$flag]->pic_id; ?>"
					                                               class="layout-span12 edit"/>
						                                    </td>
						                                    <td>
						                                        <a class="btn hovertip" id="ux_btn_delete" style="cursor: pointer;"
						                                           data-original-title="<?php _e("Delete Image", gallery_bank) ?>"
						                                           onclick="deleteImage(this);" type="edit"
						                                           controlId="<?php echo $pics[$flag]->pic_id; ?>">
						                                            <i class="icon-trash"></i>
						                                        </a>
						                                    </td>
							                            <?php
							                            }
							                            ?>
							                        </tr>
						                        <?php
						                        }
                       						 ?>
				                        	</tbody>
					                    </table>
					                </div>
					            </div>
					        </div>
						</div>
    					<div class="separator-doubled"></div>
						<button type="submit" class="btn btn-info" style="float:right; margin-top: 20px;"><?php _e("Update Album", gallery_bank); ?></button>
						<a class="btn btn-inverse" href="admin.php?page=gallery_bank" style="margin-top: 20px;"><?php _e("Back to Albums", gallery_bank); ?></a>
    				</div>
    			</div>
			</div>
		</div>
    </form>
    <script type="text/javascript">

    jQuery(".hovertip").tooltip();
    var url = "<?php echo GALLERY_BK_PLUGIN_URL  ?>";
    var image_width = <?php echo $thumbnails_width; ?>;
    var image_height = <?php echo $thumbnails_height; ?>;
    var cover_width = <?php echo $cover_thumbnail_width; ?>;
    var cover_height = <?php echo $cover_thumbnail_height; ?>;
    var delete_array = [];

    oTable = jQuery("#data-table-edit-album").dataTable
    ({
        "bJQueryUI": false,
        "bAutoWidth": true,
        "sPaginationType": "full_numbers",
        "sDom": '<"datatable-header"fl>t<"datatable-footer"ip>',
        "oLanguage": {
            "sLengthMenu": "<span>Show entries:</span> _MENU_"
        }
    });
    jQuery("#edit_album").validate
    ({
        submitHandler: function () {
            jQuery("#update_album_success_message").css("display", "block");
            jQuery("body,html").animate({
                scrollTop: jQuery("body,html").position().top}, "slow");
            var albumid = jQuery("#ux_hidden_album_id").val();
            if (delete_array.length > 0)
            {
                jQuery.post(ajaxurl,"delete_array=" +  encodeURIComponent(delete_array) + "&albumid=" + albumid + "&param=delete_pic&action=add_new_album_library", function ()
                {
                });
            }

            var uxEditDescription = "";

            if (!jQuery("#wp-ux_edit_description-wrap").hasClass("tmce-active")) {
                uxEditDescription = encodeURIComponent(jQuery("#ux_edit_description").val());
            } else {
                uxEditDescription = encodeURIComponent(tinyMCE.get("ux_edit_description").getContent());
            }

            var edit_album_name = encodeURIComponent(jQuery("#ux_edit_title").val());
            jQuery.post(ajaxurl, "albumid=" + albumid + "&edit_album_name=" + edit_album_name + "&uxEditDescription=" + uxEditDescription + "&param=update_album&action=add_new_album_library", function () {
                var count = 0;
                jQuery.each(oTable.fnGetNodes(), function (index, value) {
                    var controlClass = jQuery(value.cells[1]).find("img").attr("class");
                    var controlType = "";
                    var img_gb_path = "";
                    var isAlbumCoverSet = "";
                    var title = "";
                    var description = "";
                    var tags = "";
                    var urlRedirect = "";
                    var picId = "";

                    if (controlClass == "edit dynamic_css") {
                        controlType = jQuery(value.cells[1]).find("img").attr("type");
                        picId = jQuery(value.cells[1]).find("img").attr("imageId");
                        img_gb_path = jQuery(value.cells[1]).find("img").attr("imgpath");
                        isAlbumCoverSet = jQuery(value.cells[1]).find("input:radio").attr("checked");
                        title = jQuery(value.cells[2]).find("input:text").eq(0).val();
                        description = jQuery(value.cells[2]).find("textarea").eq(0).val();
                        tags = jQuery(value.cells[3]).find("input:text").eq(0).val();
                        urlRedirect = jQuery(value.cells[4]).find("input:text").eq(0).val();
                        jQuery.post(ajaxurl, "picId=" + picId + "&controlType=" + controlType + "&title=" + encodeURIComponent(title) +
                            "&isAlbumCoverSet=" + isAlbumCoverSet + "&img_gb_path=" + img_gb_path + "&description=" + encodeURIComponent(description) +
                            "&tags=" + encodeURIComponent(tags) + "&urlRedirect=" + urlRedirect + "&cover_width=" + cover_width +
                            "&cover_height=" + cover_height + "&param=update_pic&action=add_new_album_library", function () {
                            count++;
                            if (count == parseInt(oTable.fnGetNodes().length))
                                setTimeout(function () {
                                    jQuery("#update_album_success_message").css("display", "none");
                                    window.location.href = "admin.php?page=gallery_bank";
                                }, 3000);
                        });
                    }
                    else {
                        controlType = jQuery(value.cells[1]).find("img").attr("type");
                        var image_name = encodeURIComponent(jQuery(value.cells[1]).find("a").attr("title"));
                        img_gb_path = jQuery(value.cells[1]).find("img").attr("imgpath");
                        isAlbumCoverSet = jQuery(value.cells[1]).find("input:radio").attr("checked");
                        title = jQuery(value.cells[2]).find("input:text").eq(0).val();
                        description = jQuery(value.cells[2]).find("textarea").eq(0).val();
                        tags = jQuery(value.cells[3]).find("input:text").eq(0).val();
                        urlRedirect = jQuery(value.cells[4]).find("input:text").eq(0).val();
                        jQuery.post(ajaxurl, "album_id=" + albumid + "&controlType=" + controlType + "&imagename=" + image_name +
                            "&img_gb_path=" + img_gb_path + "&isAlbumCoverSet=" + isAlbumCoverSet + "&title=" + encodeURIComponent(title) +
                            "&description=" + encodeURIComponent(description) + "&tags=" + encodeURIComponent(tags) + "&urlRedirect=" + urlRedirect +
                            "&cover_height=" + cover_height + "&cover_width=" + cover_width +
                            "&param=add_pic&action=add_new_album_library", function () {
                            count++;
                            if (count == parseInt(oTable.fnGetNodes().length))
                                setTimeout(function () {
                                    jQuery("#update_album_success_message").css("display", "none");
                                    window.location.href = "admin.php?page=gallery_bank";
                                }, 3000);
                        });
                    }
                });
                if (count == parseInt(oTable.fnGetNodes().length)) {
                    setTimeout(function () {
                        jQuery("#update_album_success_message").css("display", "none");
                        window.location.href = "admin.php?page=gallery_bank";
                    }, 3000);
                }
            });
        }
    });
    jQuery("#edit_image_uploader").pluploadQueue
    ({
        runtimes: "html5,flash,silverlight,html4",
        url: url + "/upload.php",
        chunk_size: "1mb",
        filters: {
            max_file_size: "100mb",
            mime_types: [
                {title: "Image files", extensions: "jpg,jpeg,gif,png"}
            ]
        },
        rename: true,
        sortable: true,
        dragdrop: true,
        unique_names: true,
        max_file_count: 20,
        views: {
            list: true,
            thumbs: true, // Show thumbs
            active: "thumbs"
        },
        flash_swf_url: url + "/assets/Moxie.swf",
        silverlight_xap_url: url + "/assets/Moxie.xap",
        init: {
            FileUploaded: function (up, file) {
                var oTable = jQuery("#data-table-edit-album").dataTable();

                jQuery.post(ajaxurl, "img_path=" + file.target_name + "&img_name=" + file.name + "&image_width=" + image_width +
                    "&image_height=" + image_height +
                    "&param=add_new_dynamic_row_for_image&action=add_new_album_library", function (data) {
                    var col1 = jQuery("<td></td>");
                    col1.append(jQuery.parseJSON(data)[0]);
                    var col2 = jQuery("<td></td>");
                    col2.append(jQuery.parseJSON(data)[1]);
                    var col3 = jQuery("<td></td>");
                    col3.append(jQuery.parseJSON(data)[2]);
                    var col4 = jQuery("<td></td>");
                    col4.append(jQuery.parseJSON(data)[3]);
                    var col5 = jQuery("<td></td>");
                    col5.append(jQuery.parseJSON(data)[4]);
                    var col6 = jQuery("<td></td>");
                    col6.append(jQuery.parseJSON(data)[5]);
                    oTable.fnAddData([col1.html(), col2.html(), col3.html(), col4.html(), col5.html(), col6.html()]);
                    select_radio();
                    jQuery(".hovertip").tooltip();
                });
            },
            UploadComplete: function () {
                jQuery(".plupload_buttons").css("display", "block");
                jQuery(".plupload_upload_status").css("display", "none");
            }
        }
    });
    function deleteImage(control) {
        var r = confirm("<?php _e("Are you sure you want to delete this Image?", gallery_bank)?>");
        if (r == true) {
            var row = jQuery(control).closest("tr");
            var type = jQuery(control).attr("type");
            var oTable = jQuery("#data-table-edit-album").dataTable();
            if (type == "edit")
            {
                var controlId = jQuery(control).attr("controlid");
                delete_array.push(controlId);
            }
            oTable.fnDeleteRow(row[0]);
            select_radio();
        }
    }
    function insertVideoToDataTable()
    {
       alert("<?php _e( "This feature is only available in Paid Premium Version!", gallery_bank ); ?>");
    }
    jQuery("#grp_select_items").click(function () {
        var oTable = jQuery("#data-table-edit-album").dataTable();
        var checkProp = jQuery("#grp_select_items").prop("checked");
        jQuery("input:checkbox", oTable.fnGetNodes()).each(function () {
            if (checkProp) {
                jQuery(this).attr("checked", "checked");
            }
            else {
                jQuery(this).removeAttr("checked");
            }
        });
    });
    function deleteSelectedImages()
    {
        alert("<?php _e("This feature is only available in Paid Premium Version!", gallery_bank)?>");
    }
    //This function is to select radio button of first image
    function select_radio() {
        if (!(jQuery("input[type=radio][name=ux_album_cover]:checked").size() > 0)) {
            jQuery(jQuery.unique(
                jQuery("INPUT:radio")
                    .map(function (i, e) {
                        return jQuery(e).attr("name")
                    }
                ).get()
            )).each(function (i, e) {
                    jQuery("INPUT:radio[name=\"" + e + "\"]:visible:first")
                        .attr("checked", "checked");
                });
        }
    }
    function preventDot(e)
	{
	    var key = e.charCode ? e.charCode : e.keyCode;
	    if (key == 46)
	    {
	        return false;
	    }    
	}
    </script>
<?php
}
?>