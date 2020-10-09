<?php

$wp_user = wp_get_current_user()->data;

return array(
    'user_id' => $wp_user->ID,
    'user_login' => $wp_user->user_login,
    'user_name' => $wp_user->user_nicename,
    'user_email' => $wp_user->user_email,
    'user_url' => $wp_user->user_url,
    'user_registered' => $wp_user->user_registered,
    'user_status' => $wp_user->user_status,
);

?>
