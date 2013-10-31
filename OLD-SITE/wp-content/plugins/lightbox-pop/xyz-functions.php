<?php


if(!function_exists('xyz_lbx_plugin_get_version'))
{
	function xyz_lbx_plugin_get_version() 
	{
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( XYZ_LBX_PLUGIN_FILE ) ) );
				//print_r($plugin_folder);
		return $plugin_folder['lightbox-pop.php']['Version'];
	}
}



add_filter( 'plugin_row_meta','xyz_lbx_links',10,2);
function xyz_lbx_links($links, $file) 
{
	$base = plugin_basename(XYZ_LBX_PLUGIN_FILE);
	if ($file == $base) 
	{

		$links[] = '<a href="http://xyzscripts.com/support/" class="xyz_support" title="Support"></a>';
		$links[] = '<a href="http://twitter.com/xyzscripts" class="xyz_twitt" title="Follow us on twitter"></a>';
		$links[] = '<a href="https://www.facebook.com/xyzscripts" class="xyz_fbook" title="Facebook"></a>';
		$links[] = '<a href="https://plus.google.com/101215320403235276710/" class="xyz_gplus" title="+1"></a>';
	}
	return $links;
}

?>