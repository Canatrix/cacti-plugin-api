<?php

function plugin_api_install() {
	api_plugin_register_hook(
			'api', 'user_admin_tab', 'api_user_admin_tab', 'includes/tab.php');

	api_plugin_register_hook(
			'api', 'user_admin_run_action', 'api_user_admin_run_action', 'includes/tab.php');

	api_plugin_register_hook(
			'api', 'user_admin_action', 'api_user_admin_action', 'includes/tab.php');


	//api_plugin_hook_function('user_admin_action', get_request_var_request("action"))

	$data = array();
	$data['columns'][] = array('name' => 'api_key_id', 'type' => 'int(11)', 'NULL' => false, 'auto_increment' => true);
	$data['columns'][] = array('name' => 'user_id', 'type' => 'int(11)', 'NULL' => true);
	$data['columns'][] = array('name' => 'api_key', 'type' => 'varchar(100)', 'NULL' => true);

	$data['primary'] = 'api_key_id';
//	$data['keys'][] = array('name' => 'host_id', 'columns' => 'host_id');

	$data['type'] = 'MyISAM';
	$data['comment'] = 'API keys';
	api_plugin_db_table_create('api', 'api_key', $data);

	api_plugin_register_realm('api', 'v1/apiv1.php', 'Rest Api Access', 1);
}

function plugin_api_uninstall() {
	// Do any extra Uninstall stuff here
}

function plugin_api_check_config() {

	return true;
}

function plugin_api_upgrade() {

	return false;
}

function plugin_api_version() {
	return array(
		'name' => 'api',
		'version' => '0.1',
		'longname' => 'RestAPI for Cacti',
		'author' => 'Gergely Viktor Asztalos',
		'email' => 'canatrix@canatrix.com'//
	);
}

function api_version() {
	return plugin_api_version();
}

function api_check_upgrade() {
	
}

?>