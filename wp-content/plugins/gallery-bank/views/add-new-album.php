<?php
	global $wpdb;
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
			div.pp_default .pp_top .pp_middle {
		    background-color: #ffffff;
		    }
			.pp_pic_holder.pp_default {
			    background-color: #ffffff;
		    }
		    div.pp_default .pp_content_container .pp_left {
		        background-color: #ffffff;
		        padding-left: 16px;
		    }
		
		    div.pp_default .pp_content_container .pp_right {
		        background-color: #ffffff;
		        padding-right: 13px;
		    }
		
		    div.pp_default .pp_bottom .pp_middle {
		        background-color: #ffffff;
		    }
		
		    div.pp_default .pp_content, div.light_rounded .pp_content {
		        background-color: #ffffff;
		    }
		
		    .pp_details {
		        background-color: #ffffff;
		    }
		
		    .ppt {
		        display: none !important;
		    }
	    </style>
	<?php
	}
?>
<form id="add_new_album" class="layout-form">
    <div class="fluid-layout">
        <div class="layout-span12">
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="admin.php?page=gallery_bank"><?php _e("Gallery Bank", gallery_bank); ?></a>
                    <span class="divider">/</span>
                    <a href="#"><?php _e("Add New Album", gallery_bank); ?></a>
                </li>
            </ul>
            <div class="widget-layout">
                <div class="widget-layout-title">
                    <h4>
                        <i class="icon-plus"></i>
                        <?php _e("Add New Album", gallery_bank); ?>
                    </h4>
                </div>
                <div class="widget-layout-body">
                    <a class="btn btn-inverse" href="admin.php?page=gallery_bank">
                        <?php _e("Back to Albums", gallery_bank); ?>
                    </a>
                    <button type="submit" id="ux_save_album" class="btn btn-info" style="float:right">
                        <?php _e("Save and Submit Changes", gallery_bank); ?>
                    </button>
                    <div class="separator-doubled"></div>
                    <div id="album_success_message" class="message green" style="display: none;">
						<span>
							<strong>
                                <?php _e("Album Published. Kindly wait for the redirect to happen.", gallery_bank); ?>
                            </strong>
						</span>
                    </div>
                    <a rel="prettyPhoto[gallery]" href="<?php echo GALLERY_BK_PLUGIN_URL . "/assets/images/how-to-setup-short-code.png";?>">How to setup Short-Codes for Gallery Bank into your WordPress Page/Post?</a>
                    <div class="fluid-layout">
                        <div class="layout-span6">
                            <div class="widget-layout">
                                <div class="widget-layout-title">
                                    <h4><?php _e("Album Details", gallery_bank); ?></h4>
                                </div>
                                <div class="widget-layout-body">
                                    <div class="layout-control-group">
                                        <label class="layout-control-label">
                                            <?php _e("Album Title", gallery_bank); ?> :
                                        </label>

                                        <div class="layout-controls">
                                            <input type="text" name="ux_title" class="layout-span12" value=""
                                                   id="ux_title"
                                                   placeholder="<?php _e("Enter your Album Title", gallery_bank); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-layout-body">
                                    <div class="layout-control-group">
                                        <label class="layout-control-label"><?php _e("Description", gallery_bank); ?>
                                            :</label>
                                    </div>
                                    <div class="layout-control-group">
                                        <?php
                                        wp_editor("", $id = "ux_description", $media_buttons = true, $tab_index = 1);
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
                                <div class="widget-layout-body" id="image_uploader">
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
                                <div class="widget-layout-body" id="video_uploader">
                                    <div class="layout-control-group">
                                        <label class="layout-control-label"><?php _e("Video Url", gallery_bank); ?>
                                            :</label>

                                        <div class="layout-controls">
                                            <input type="text" name="ux_txt_video_url" class="layout-span12" value=""
                                                   id="ux_txt_video_url"
                                                   placeholder="<?php _e("Enter your Video Url", gallery_bank); ?>"/>
                                        </div>
                                    </div>
                                    <div class="layout-control-group">
                                        <div class="layout-controls">
                                            <button type="button" onclick="insertVideoToDataTable();"
                                            style="float:right"
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
                                    <table class="table table-striped " id="data-table-album">
                                        <thead>
	                                        <tr>
	                                            <th style="width:10%">
	                                                <input type="checkbox" id="grp_select_items" name="grp_select_items"
	                                                   style="vertical-align:middle;"/>
	                                                <button type="button" onclick="deleteSelectedImages();"
	                                                    style="vertical-align:middle;"
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="separator-doubled"></div>
                    <button type="submit" id="ux_save_album" class="btn btn-info"
                            style="float:right; margin-top: 20px;"><?php _e("Save and Submit Changes", gallery_bank); ?></button>
                    <a class="btn btn-inverse" href="admin.php?page=gallery_bank"
                       style="margin-top: 20px;"><?php _e("Back to Albums", gallery_bank); ?></a>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
//Global Declaration
var url = "<?php echo GALLERY_BK_PLUGIN_URL  ?>";
var image_width = <?php echo $thumbnails_width; ?>;
var image_height = <?php echo $thumbnails_height; ?>;
var cover_width = <?php echo $cover_thumbnail_width; ?>;
var cover_height = <?php echo $cover_thumbnail_height; ?>;
jQuery("a[rel^=\"prettyPhoto\"]").prettyPhoto
({
	animation_speed: 1000, 
	slideshow: 4000, 
	autoplay_slideshow: false,
	opacity: 0.80, 
	show_title: false,
	allow_resize: true
});
oTable = jQuery("#data-table-album").dataTable
({
    "bJQueryUI": false,
    "bAutoWidth": true,
    "sPaginationType": "full_numbers",
    "sDom": '<"datatable-header"fl>t<"datatable-footer"ip>',
    "oLanguage": {
        "sLengthMenu": "<span>Show entries:</span> _MENU_"
    }
});

jQuery("#add_new_album").validate
({
    submitHandler: function () {
        var uxDescription = jQuery("#wp-ux_description-wrap").hasClass("tmce-active") ?
            encodeURIComponent(tinyMCE.get("ux_description").getContent())
            : encodeURIComponent(jQuery("#ux_description").val());
        var album_name = encodeURIComponent(jQuery("#ux_title").val());
        jQuery.post(ajaxurl, "album_name=" + album_name + "&uxDescription=" + uxDescription +
            "&param=add_new_album&action=add_new_album_library", function (data) {
            jQuery("#album_success_message").css("display", "block");
            jQuery("body,html").animate({
                scrollTop: jQuery("body,html").position().top}, "slow");
            var album_id = jQuery.trim(data);
            var count = 0;
            if (oTable.fnGetNodes().length > 0) {
                jQuery.each(oTable.fnGetNodes(), function (index, value) {
                    var controlType = jQuery(value.cells[1]).find("img").attr("type");
                    var image_name = encodeURIComponent(jQuery(value.cells[1]).find("a").attr("title"));
                    var img_gb_path = jQuery(value.cells[1]).find("img").attr("imgpath");
                    var isAlbumCoverSet = jQuery(value.cells[1]).find("input:radio").attr("checked");
                    var title = jQuery(value.cells[2]).find("input:text").eq(0).val();
                    var description = jQuery(value.cells[2]).find("textarea").eq(0).val();
                    var tags = jQuery(value.cells[3]).find("input:text").eq(0).val();
                    var urlRedirect = "";
                    if (controlType == "image") {
                        urlRedirect = jQuery(value.cells[4]).find("input:text").eq(0).val();
                    }
                    else {
                        urlRedirect = "http://";
                    }
                    jQuery.post(ajaxurl, "album_id=" + album_id + "&controlType=" + controlType + "&imagename=" + image_name +
                        "&img_gb_path=" + img_gb_path + "&isAlbumCoverSet=" + isAlbumCoverSet + "&title=" + encodeURIComponent(title) +
                        "&description=" + encodeURIComponent(description) + "&tags=" + encodeURIComponent(tags) + "&urlRedirect=" + urlRedirect +
                        "&cover_height=" + cover_height + "&cover_width=" + cover_width +
                        "&param=add_pic&action=add_new_album_library", function () {
                        count++;
                        if (count == parseInt(oTable.fnGetNodes().length)) {
                            setTimeout(function () {
                                jQuery("#album_success_message").css("display", "none");
                                window.location.href = "admin.php?page=gallery_bank";
                            }, 2000);
                        }
                    });
                });
            }
            else {
                setTimeout(function () {
                    jQuery("#album_success_message").css("display", "none");
                    window.location.href = "admin.php?page=gallery_bank";
                }, 2000);
            }
        });
    }
});
jQuery("#image_uploader").pluploadQueue
({
    runtimes: "html5,flash,silverlight,html4",
    url: url + "/upload.php",
    chunk_size: '1mb',
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
        FileUploaded: function (up, file,info) {

            var oTable = jQuery("#data-table-album").dataTable();
            jQuery.post(ajaxurl, "img_path=" + file.target_name + "&img_name=" + file.name + "&image_width=" + image_width + "&image_height=" + image_height + "&param=add_new_dynamic_row_for_image&action=add_new_album_library", function (data) {
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
    var r = confirm("<?php _e("Are you sure you want to delete this Image?", gallery_bank );?>");
    if (r == true) {
        var row = jQuery(control).closest("tr");
        var oTable = jQuery("#data-table-album").dataTable();
        oTable.fnDeleteRow(row[0]);
        select_radio();
    }
}
function insertVideoToDataTable()
{
	alert("<?php _e( "This feature is only available in Paid Premium Version!", gallery_bank ); ?>");
}
jQuery("#grp_select_items").click(function () {
    var oTable = jQuery("#data-table-album").dataTable();
    var checkProp = jQuery("#grp_select_items").prop("checked");
    jQuery("input:checkbox", oTable.fnGetNodes()).each(function () {
        if (!checkProp) {
            jQuery(this).removeAttr("checked");
        } else {
            jQuery(this).attr("checked", "checked");
        }
    });
});
function deleteSelectedImages() {
    alert("<?php _e( "This feature is only available in Paid Premium Version!", gallery_bank ); ?>");
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