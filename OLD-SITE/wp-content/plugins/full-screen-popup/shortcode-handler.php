<?php

$page_option=get_option('xyz_fsp_page_option');
if($page_option==3)
{

add_shortcode( 'xyz_fsp_default_code', 'fsp_lightbox_display' );
}

?>