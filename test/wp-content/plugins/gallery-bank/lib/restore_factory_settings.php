<?php
global $wpdb,$current_user,$user_role_permission;
$role = $wpdb->prefix . "capabilities";
$current_user->role = array_keys($current_user->$role);
$role = $current_user->role[0];
	switch($role)
	{
		case "administrator":
			$user_role_permission = "manage_options";
		break;
		case "editor":
			$user_role_permission = "publish_pages";
		break;
		case "author":
			$user_role_permission = "publish_posts";
		break;
	}

if (!current_user_can($user_role_permission))
{
	return;
}
else
{
	$sql = "TRUNCATE TABLE " . gallery_bank_albums();
	$wpdb->query($sql);
	
	$sql = "TRUNCATE TABLE " . gallery_bank_pics();
	$wpdb->query($sql);
	
	include_once (GALLERY_BK_PLUGIN_DIR . "/lib/include_settings.php");
}

?>