<?php

namespace jmurrell\DynMailto;

/**
 * Plugin that enables allows templating mailto links on page load.
 * php version 7.2.24

 * Plugin Name: Dynamic Mailto
 * Plugin URI: https://github.com/murrelljenna/dyn-mailto
 * Description: Enables templating mailto links on page load.
 * Version: 1.0
 *
 * @author:  Jenna Murrell
 * @license: GPLv3
 */
defined('WPINC') || die;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dyn-mailto-widget.php';

!defined('DYN_MAILTO_PLUGIN_DIR') && define('DYN_MAILTO_PLUGIN_DIR', dirname(__FILE__));

/* Register widget */

/**
 * Register dyn-mailto-widget.
 *
 * @return void
 */
function register_dyn_mailto_widget() 
{
    register_widget('jmurrell\DynMailto\Widget');
}

add_action('widgets_init', 'jmurrell\DynMailto\register_dyn_mailto_widget');

/* Register documentation page */

/**
 * Renders documentation.
 *
 * @return void
 */
function render_docs() 
{
    echo '<div class="wrap">';

    // To do: render markdown documentation from here.
    wp_enqueue_style('dyn-mailto-docs', plugins_url('dyn-mailto/css/docs.css'));
    readfile(DYN_MAILTO_PLUGIN_DIR . "/templates/docs.html");

    echo '</div>';
}

add_action(
    'admin_menu', function () {
        add_submenu_page(
            null,
            __('Documentation', 'textdomain'),
            __('Documentation', 'textdomain'),
            'manage_options',
            'dyn-mailto-documentation',
            'jmurrell\DynMailto\render_docs'
        );
    } 
);

add_filter(
    'plugin_action_links_'.plugin_basename(__FILE__), function ( $links ) {
        $links[] = '<a href="' .
        admin_url('/?page=dyn-mailto-documentation') .
        '">' . __('Documentation') . '</a>';
        return $links;
    }
);
