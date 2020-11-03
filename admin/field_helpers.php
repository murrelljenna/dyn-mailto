<?php

namespace jmurrell\DynMailto;

class Field_Helpers
{
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

    public static function get_user_location()
    {
        require_once PLUGIN_DIR . ('/providers/FreeGeoIp.php');

        $ip_info = new FreeGeoIp('json');

        try {
            $response = $ip_info->fetch(self::get_user_IP());
        } catch (Exception $e) {
            return '';       
        }

        return json_decode($response, true);
    }
}

?>
