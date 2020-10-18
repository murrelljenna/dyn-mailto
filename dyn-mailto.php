<?php
/**
 * Plugin that enables allows templating mailto links on page load.
 * php version 7.2.24

 * Plugin Name: Dynamic Mailto
 * Plugin URI: http://www.wpexplorer.com/create-widget-plugin-wordpress/
 * Description: Enables templating mailto links on page load.
 * Version: 1.0
 * @author: Jenna Murrell
 * @license: MIT
 */
defined('WPINC') || die;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dyn-mailto-widget.php';

function register_dyn_mailto_widget() 
{
	register_widget('Dyn_Mailto_Widget');
}
add_action('widgets_init', 'register_dyn_mailto_widget');
