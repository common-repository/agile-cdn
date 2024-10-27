<?php

// Default constant
define( 'AGILEWING_WEBSITE' , 'https://www.agilecdn.cloud/');
define( 'AGILEWING_CDN', 'https://console.agilecdn.cloud/');
define( 'AGILEWING_CDN_DOMAIN', '.agilewingcdn.com');
define( 'AGILEWING_CDN_DOMAIN_DEV', '.agilewingcdn-demo.com');
// define( 'AGILEWING_CDN', 'https://console.agilewingcdn-demo.com/');
// define( 'AGILEWING_CDN_DOMAIN', '.agilewingcdn-demo.com');

// Init sidebar menu
add_action('admin_menu', 'agile_cdn_create_wp_menu');
function agile_cdn_create_wp_menu() {
    add_menu_page('AgileCDN', 'AgileCDN', 'administrator', 'agile-cdn', 'agile_main_page', '');
    add_action( 'admin_init', 'register_agile_cdn_settings' );
}

// Init setting
function register_agile_cdn_settings() {
    register_setting( 'agile-cdn-settings', 'agile_cdn_enabled' );
    register_setting( 'agile-cdn-settings', 'agile_cdn_url', 'agile_cdn_url_validate' );
    register_setting( 'agile-cdn-settings', 'agile_cdn_prefix' );
}

// Register admin scripts for custom fields
add_action( 'admin_enqueue_scripts', 'load_agilewing_style' );
function load_agilewing_style() {
    wp_enqueue_style( 'agilecdn_css', plugins_url('css/agilecdn.css', __FILE__), array(), '0.3' );
    wp_enqueue_script( 'agilecdn_js', plugins_url('js/agilecdn.js', __FILE__), array(), '0.3' );
    wp_enqueue_script( 'jquery-form' );
}

// Validate Input for Admin options
function agile_cdn_url_validate($data){
	if(filter_var($data, FILTER_VALIDATE_URL)) {
      $site_url = wp_parse_url($data);
      $site_url_path = (array_key_exists('path', $site_url) ? $site_url["path"] : null);
      return $site_url["scheme"] . '://' . $site_url["host"] . $site_url_path;
   	} else {
   		add_settings_error(
            'agile_cdn_url',
            'agile-cdn-notice',
            'You did not enter a valid URL for your site URL',
            'error');
   	}
}

// Validate admin options
function agile_cdn_is_enabled() {
    if(esc_attr( get_option('agile_cdn_enabled') ) != 'on') return false;
    if(empty(esc_attr(get_option('agile_cdn_url') ))) return false;
    if(empty(esc_attr(get_option('agile_cdn_prefix') ))) return false;
  
    return true;
}

// activated plugin
add_action("activated_plugin", "agile_cdn_activated_plugin_first");
function agile_cdn_activated_plugin_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}
