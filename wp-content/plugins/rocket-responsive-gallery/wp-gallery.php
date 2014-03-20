<?php
/**
Plugin Name: Rocket Gallery Plugin
Plugin URI: http://rocketplugins.com/wordpress-gallery-plugin/
Description: WP Gallery is a responsive gallery plugin with focus on ease of use and best experience for visitors with any device.
Version: 1.0
Author: Muneeb
Author URI: http://rocketplugins.com/wordpress-gallery-plugin/
License: GPLv2 or later
Copyright: 2012 Muneeb ur Rehman http://muneeb.me
**/

/**
This plugin uses some of the code and an image from the acf(Advanced Custom Fields) plugin for Gallery Custom Post Type UI and also inspiration for great plugin design under GPL license. Thanks You Elliot Condon
Copyright: Elliot Condon
**/

require plugin_dir_path( __FILE__ ) . 'config.php';

require GALLERY_PLUGIN_INCLUDE_DIRECTORY . 'functions.php';

wpgp_load_plugin();

wpgp_loaded();