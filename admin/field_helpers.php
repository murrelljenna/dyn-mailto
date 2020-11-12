<?php

/**
 * PHP Version 7
 * Defines static helper class for retrieving template fields.
 */

namespace jmurrell\DynMailto;

class Field_Helpers
{
    /**
     * Get visitor IP address.
     * 
     * @return string
     */
    public static function get_user_ip() 
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP']) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return apply_filters('wpb_get_ip', $ip);
    }

    /**
     * Makes call to FreeGeoIp.app to retrieve associative array of geolocation info.
     *
     * @return array
     */
    public static function get_user_location()
    {
        include_once DYN_MAILTO_PLUGIN_DIR . ('/vendor/murrelljenna/freegeoip-php-wrapper/FreeGeoIp.php');

        $ip_info = new \FreeGeoIp('json');

        try {
            $response = $ip_info->fetch(self::get_user_IP());
        } catch (Exception $e) {
            return '';       
        }

        return json_decode($response, true);
    }
}

?>
