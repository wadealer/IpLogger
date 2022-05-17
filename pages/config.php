<?php

form_security_validate('plugin_IpLogger_config');
access_ensure_global_level(config_get('manage_plugin_threshold'));

$g_project = helper_get_current_project();

function config_set_if_needed($p_name, $p_value, $all_proj = false) {
	global $g_project;

	if ($p_value != plugin_config_get($p_name)) {
		plugin_config_set($p_name, $p_value, NO_USER, $all_proj ? ALL_PROJECTS : $g_project);
	}
}

$t_redirect_url = plugin_page('config_page', true);
layout_page_header(null, $t_redirect_url);
layout_page_begin();

config_set_if_needed('logging_user_threshold', gpc_get_int('logging_user_threshold'), true);
config_set_if_needed('proxy_ips', gpc_get_string('proxy_ips'), true);

form_security_purge('plugin_IpLogger_config');

html_operation_successful($t_redirect_url);
layout_page_end();
