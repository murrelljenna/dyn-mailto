<?php

$wp_user = wp_get_current_user()->data;
$blog_info = get_bloginfo('description');

		ob_start();
		var_dump($blog_info);
		$contents = ob_get_contents();
		ob_end_clean();
		error_log($contents);
require_once dirname(__FILE__) . "/field_helpers.php";

return array(
	'user_id' => $wp_user->ID,
	'user_login' => $wp_user->user_login,
	'user_name' => $wp_user->user_nicename,
	'user_email' => $wp_user->user_email,
	'user_url' => $wp_user->user_url,
	'user_registered' => $wp_user->user_registered,
	'user_status' => $wp_user->user_status,
	'user_ip' => Field_Helpers::get_user_ip(),
	'site_name' => get_bloginfo('name'),
	'site_description' => get_bloginfo('description'),
	'site_url' => get_bloginfo('url'),
	'admin_email' => get_bloginfo('admin_email'),
);

?>
