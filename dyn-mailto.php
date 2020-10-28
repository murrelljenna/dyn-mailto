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

/* Register widget */

function register_dyn_mailto_widget() 
{
    register_widget('jmurrell\DynMailto\Widget');
}

add_action('widgets_init', 'jmurrell\DynMailto\register_dyn_mailto_widget');

/* Register documentation page */

function render_docs() {
    echo '<div class="wrap">';

    // To do: render markdown documentation from here.

    echo '</div>';
}

add_action( 'admin_menu', function() {
    add_submenu_page(
        null,
        __( 'Documentation', 'textdomain' ),
        __( 'Documentation', 'textdomain' ),
        'manage_options',
        'dyn-mailto-documentation',
        'jmurrell\DynMailto\render_docs'
    );
} );

add_filter('plugin_action_links_'.plugin_basename(__FILE__), function ( $links ) {
    $links[] = '<a href="' .
        admin_url( '/?page=dyn-mailto-documentation' ) .
        '">' . __('Documentation') . '</a>';
    return $links;
});
