<?php

namespace jmurrell\DynMailto;

/**
 * Plugin that enables allows templating mailto links on page load.
 * php version 7.2.24

 * Plugin Name: Dynamic Mailto
 * Plugin URI: http://www.wpexplorer.com/create-widget-plugin-wordpress/
 * Description: Enables templating mailto links on page load.
 * Version: 1.0
 *
 * @author:  Jenna Murrell
 * @license: MIT
 */
defined('WPINC') || die;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dyn-mailto-widget.php';

!defined('PLUGIN_DIR') && define('PLUGIN_DIR', dirname(__FILE__));

function register_dyn_mailto_widget() 
{
    register_widget('jmurrell\DynMailto\Widget');
}

add_action('widgets_init', 'jmurrell\DynMailto\register_dyn_mailto_widget');
