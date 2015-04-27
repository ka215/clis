<?php

namespace CustomLoginSuite\Common;

$plugin_main_filepath = str_replace('lib/', 'clis.php', plugin_dir_path(__FILE__));

/**
 * Utility: Outputting the hook information to the javascript console at the time of each hook call.
 *
 * @param string $functon callback function name of hook
 * @param string $type 'Action' or 'Filter'
 * @param boolean $display Whether echo the javascript
 * @return void
 */
function console_hook_name( $function, $type, $display ) {
  $parse_path = explode("\\", $function);
  $hook_name = array_pop($parse_path);
  if ($display) {
    printf('<script>if(window.console&&typeof window.console.log==="function"){console.log("%s : %sHook (%s)");}</script>', str_replace('my_', '', $hook_name), $type, $hook_name);
  }
}

/**
 * Utility: Logger for this plugin
 *
 * @param string $message
 * @param integer $logging_type 0: php system logger, 1: mail to $distination, 3: overwriting file of $distination (default), 4: to SAPI handler
 * @param string $distination
 * @return boolean
 */
function logger( $message, $logging_type=3, $distination='' ) {
  if (empty($message) || '' === trim($message)) 
    return;
  
  if (!in_array(intval($logging_type), [ 0, 1, 3, 4 ])) 
    $logging_type = 3;
  
  $current_datetime = date('Y-m-d H:i:s', time());
  $message = preg_replace( '/(?:\n|\r|\r\n)/', ' ', trim($message) );
  $log_message = sprintf("[%s] %s\n", $current_datetime, $message);
  
  if (3 == intval($logging_type)) 
    $distination = empty($message) || '' == trim($distination) ? str_replace('lib/', 'debug.log', plugin_dir_path(__FILE__)) : $distination;
  
  return error_log( $log_message, $logging_type, $distination );
}

/**
 * Utility: Action hook is fired at the time this plugin has activated
 */
function plugin_activate() {
  $message = sprintf(__('Function called: %s; %s', CLIS), __FUNCTION__, __('Custom Login Suite plugin has activated.', CLIS));
  logger( $message );
  
  // as you fun
}
register_activation_hook( $plugin_main_filepath, __NAMESPACE__ . '\\plugin_activate' );

/**
 * Utility: Action hook is fired at the time this plugin was deactivation
 */
function plugin_deactivation() {
  $message = sprintf(__('Function called: %s; %s', CLIS), __FUNCTION__, __('Custom Login Suite plugin has been deactivation.', CLIS));
  logger( $message );
  
  // as you fun
}
register_deactivation_hook( $plugin_main_filepath, __NAMESPACE__ . '\\plugin_deactivation' );

/**
 * Utility: Action hook is fired at the time this plugin was deactivation
 */
function plugin_uninstall() {
  if ( !current_user_can( 'activate_plagins' ) ) 
    return;
  check_admin_referer( 'bulk-plugins' );
  
  if ( __FILE__ != WP_UNINSTALL_PLUGIN ) 
    return;
  
  $message = sprintf(__('Function called: %s; %s', CLIS), __FUNCTION__, __('Custom Login Suite plugin uninstall now.', CLIS));
  logger( $message, 3, 'C:\xampp\htdocs\wp.ka2.org\wp-content\plugins\uninstall.log' );
  die();
/*
$option_name = defined('CLIS') ? CLIS : 'custom-login-suite';
$nmp_options = get_option($option_name);
if (isset($nmp_options['uninstall_options'])) {
	$is_delete = $nmp_options['uninstall_options'];
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
*/
}
register_uninstall_hook( $plugin_main_filepath, __NAMESPACE__ . '\\plugin_uninstall' );

unset($plugin_main_filepath);