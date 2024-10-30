<?php
/*
Plugin Name: MSLS Grouping
Plugin URI: http://asumaru.com/business/wp-plugins/asm-msls-grouping/
Description: You can multilingualize by 'Multisite Language Switcher' with grouping the sites of the plural same languages.
Author: Asumaru Corp.
Version: 0.2.1
Author URI: http://asumaru.com/
Created: 2017.01.01
Text Domain: asm-msls-grouping
Domain Path: /languages/
*/

/**
 * Global variable. Orignal text domain.
 *
 * @since 0.1
**/
$asm_textdomain = 'asm-msls-grouping';

global $asumaru_registed_plugins;
$asumaru_registing_plugin = basename(__FILE__);

if(isset($asumaru_registed_plugins) && is_array($asumaru_registed_plugins)){
	if(in_array($asumaru_registing_plugin,$asumaru_registed_plugins)){
		return;
	}
}
$asumaru_registed_plugins[] = $asumaru_registing_plugin;
$asumaru_registing_plugin_path = dirname(__FILE__) . '/inc/' . $asumaru_registing_plugin;
if(file_exists($asumaru_registing_plugin_path)){
	include_once($asumaru_registing_plugin_path);
}
?>