<?php

/**
 * PHP Version 7
 * Defines static class for retrieving template fields.
 */

namespace jmurrell\DynMailto;

require_once dirname(__FILE__) . "/field_helpers.php";

class Field_Loader
{
    private static $_fields;

    public static function init_static_fields() 
    {
        $wp_user = wp_get_current_user()->data;

        /* All the fields that will be available to the user when writing the template */

        self::$_fields = array(

        'user_id' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->ID : '', 'category' => 'User'),
        'user_login' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->user_login : '', 'category' => 'User'),
        'user_name' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->user_nicename : '', 'category' => 'User'),
        'user_email' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->user_email : '', 'category' => 'User'),
        'user_url' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->user_url : '', 'category' => 'User'),
        'user_registered' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->user_registered : '', 'category' => 'User'),
        'user_status' => array('value' => ($wp_user instanceof WP_User) ? $wp_user->user_status : '', 'category' => 'User'),
        'user_ip' => array('value' => Field_Helpers::get_user_ip(), 'category' => 'User'),

        /* General site info */

        'site_name' => array('value' => get_bloginfo('name'), 'category' => 'Site'),
        'site_description' => array('value' => get_bloginfo('description'), 'category' => 'Site'),
        'site_url' => array('value' => get_bloginfo('url'), 'category' => 'Site'),
        'admin_email' => array('value' => get_bloginfo('admin_email'), 'category' => 'Site'),

        /* Location info. get_user_location() makes an API call */

        'location_country' => array('value' => Field_Helpers::get_user_location()['country_name'], 'category' => 'Location'),
        'location_country_code' => array('value' => Field_Helpers::get_user_location()['country_code'], 'category' => 'Location'),
        'location_region' => array('value' => Field_Helpers::get_user_location()['region_name'], 'category' => 'Location'),
        'location_region_code' => array('value' => Field_Helpers::get_user_location()['region_code'], 'category' => 'Location'),
        'location_city' => array('value' => Field_Helpers::get_user_location()['city'], 'category' => 'Location'),
        'location_time_zone' => array('value' => Field_Helpers::get_user_location()['time_zone'], 'category' => 'Location'),

        /* Date info */

        'date_month' => array('value' => getdate()['month'], 'category' => 'Date'),
        'date_month_no' => array('value' => strval(getdate()['mon']), 'category' => 'Date'),
        'date_year' => array('value' => getdate()['year'], 'category' => 'Date'),
        'date_weekday' => array('value' => getdate()['weekday'], 'category' => 'Date'),
        'date_yearday' => array('value' => getdate()['yday'], 'category' => 'Date'),
        'date_hours' => array('value' => getdate()['hours'], 'category' => 'Date'),
        'date_minutes' => array('value' => getdate()['minutes'], 'category' => 'Date'),
        'date_seconds' => array('value' => getdate()['seconds'], 'category' => 'Date'),
        );
    }

    /**
     * Gets and formats $_fields for use by twig template on widget load.
     * 
     * @return array
     */
    public static function get_template_fields() 
    {
        $ret_fields = array();

        array_walk(
            self::$_fields, function ($v, $k) use (&$ret_fields) {
                $ret_fields[$k] = $v['value'];
            }
        );

        return $ret_fields;
    }

    /**
     * Gets and formats $_fields for use by javascript autocompletion on widget form.
     * 
     * @return array
     */
    public static function get_autocomplete_fields() 
    {
        $ret_fields = array();

        array_walk(
            self::$_fields, function ($v, $k) use (&$ret_fields) {
                $ret_fields[] = array('label' => $k, 'category' => $v['category']);
            }
        );
        return $ret_fields;
    }
}

Field_Loader::init_static_fields();

?>
