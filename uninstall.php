<?php
//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) 
	exit();

$option_name = defined('CLIS') ? CLIS : 'custom-login-suite';
$clis_options = get_option($option_name);
if (isset($clis_options['uninstall_options'])) {
	$is_delete = $clis_options['uninstall_options'];
} else {
	$is_delete = false;
}

if (!is_multisite()) {
	if ($is_delete) {
		delete_option($option_name);
		delete_option($option_name . '_previous_revision_backup');
	}
} else {
	global $wpdb;
	$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	$original_blog_id = get_current_blog_id();
	foreach ($blog_ids as $blog_id) {
		switch_to_blog($blog_id);
		if ($is_delete) {
			delete_option($option_name);
			delete_option($option_name . '_previous_revision_backup');
		}
	}
	switch_to_blog($original_blog_id);
	
	if ($is_delete) {
		delete_site_option($option_name);
		delete_site_option($option_name . '_previous_revision_backup');
	}
}