<?php

/**
 * Cacti device (or host) handler
 * 
 * This provide access to device datas
 * 
 * @author Gergely Viktor Asztalos <agv@rsh.hu>
 */

namespace handlers;

class devices extends \handlers\base {

	public $allowDirect = true;
	
	/**
	 * How to interpret the URI route this handler.
	 * In HTTP request:
	 *     "ID"			-> METHOD /api_url/device/4
	 *     Work with selected device (device is is 4).
	 * 
	 *     "ID/METHOD"	-> METHOD /api_url/device/4/method_from_this_class
	 *     Select a device and pass control to a method from this class
	 * 
	 *     "ID/HANDLER"	-> METHOD /api_url/device/4/child_handler
	 *     Select a device and pass control to an another handler
	 * 
	 *     "HANDLER"	-> METHOD /api_url/device/child_handler
	 *     Pass control to an another handler
	 * 
	 * @var array list of how to use handler
	 */
	public $extend = array(
		"ID",
		"ID/METHOD",
		"ID/HANDLER",
		"HANDLER"
	);

	/**
	 * Get all devices (or hosts) in array by GET method.
	 * In HTTP request: GET /api_uri/device
	 *     
	 */
	function _GET() {
		
		$fields = $this->_filterFields($this->_GET_fields());

		$offsetMax = db_fetch_cell("SELECT count(*) FROM host");

		$limit = $_REQUEST["limit"] * 1 == 0 ? 20 : $_REQUEST["limit"] * 1;
		$offset = $_REQUEST["offset"] * 1 == 0 ? 0 : $_REQUEST["offset"] * 1;

		$response["data"] = db_fetch_assoc("SELECT " . implode(",", $fields) . " FROM host LIMIT " . $offset . "," . $limit);

		$url = (new \classes\url())->fromContext();

		$response["paging"]["next"] = (string) $url->replica()->addString("limit=" . $limit . "&offset=" . ($offset + $limit));
		if ($offset > 0)
			$response["paging"]["previous"] = (string) $url->replica()->addString("limit=" . $limit . "&offset=" . (($offset - $limit) < 0 ? 0 : $offset - $limit));

		$tmp = json_encode($response, JSON_PRETTY_PRINT);
		$tmp = str_replace("\\/", "/", $tmp);
		echo $tmp;
	}

	/**
	 * Get a device by id. 
	 * In HTTP request: GET /api_uri/device/4
	 * Id will be 4.
	 */
	function _GET_ID() {
		$fields = $this->_filterFields($this->_GET_fields());

		$sql_query = "SELECT " . implode(",", $fields) . " FROM host WHERE id = '" . (\RestService::$routeAssoc["devices.ID"]) . "'";

		$hosts = db_fetch_row($sql_query);
		echo json_encode($hosts, JSON_PRETTY_PRINT);
	}

	/**
	 * Return an array with host table name of fields
	 * @return array an array with database fields
	 */
	function _GET_fields() {
		$sql_query = "SELECT COLUMN_NAME AS field
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = 'host'
			AND table_schema = DATABASE()";
		$columns = db_fetch_assoc($sql_query);

		foreach ($columns as $column) {
			$fields[] = strtolower($column["field"]);
		}
		return $fields;
	}

	/**
	 * Create a new device (or host)
	 * In HTTP request: POST /api_url/devices
	 * @global array $fields_host_edit provided by Cacti
	 */
	function _POST() {
		global $fields_host_edit;


		$availability_method = isset($_REQUEST["availability_method"]) ? $_REQUEST["availability_method"] : read_config_option("availability_method");
		$ping_method = isset($_REQUEST["ping_method"]) ? $_REQUEST["ping_method"] : read_config_option("ping_method");
		$ping_port = isset($_REQUEST["ping_port"]) ? $_REQUEST["ping_port"] : read_config_option("ping_port");
		$ping_timeout = isset($_REQUEST["ping_timeout"]) ? $_REQUEST["ping_timeout"] : read_config_option("ping_timeout");
		$ping_retries = isset($_REQUEST["ping_retries"]) ? $_REQUEST["ping_retries"] : read_config_option("ping_retries");
		$snmp_version = isset($_REQUEST["snmp_ver"]) ? $_REQUEST["snmp_ver"] : read_config_option("snmp_ver");
		$snmp_community = isset($_REQUEST["snmp_community"]) ? $_REQUEST["snmp_community"] : read_config_option("snmp_community");
		$snmp_username = isset($_REQUEST["snmp_username"]) ? $_REQUEST["snmp_username"] : read_config_option("snmp_username");
		$snmp_password = isset($_REQUEST["snmp_password"]) ? $_REQUEST["snmp_password"] : read_config_option("snmp_password");
		$snmp_auth_protocol = isset($_REQUEST["snmp_auth_protocol"]) ? $_REQUEST["snmp_auth_protocol"] : read_config_option("snmp_auth_protocol");
		$snmp_priv_passphrase = isset($_REQUEST["snmp_priv_passphrase"]) ? $_REQUEST["snmp_priv_passphrase"] : read_config_option("snmp_priv_passphrase");
		$snmp_timeout = isset($_REQUEST["snmp_priv_protocol"]) ? $_REQUEST["snmp_priv_protocol"] : read_config_option("snmp_priv_protocol");
		$snmp_port = isset($_REQUEST["snmp_port"]) ? $_REQUEST["snmp_port"] : read_config_option("snmp_port");
		$snmp_timeout = isset($_REQUEST["snmp_timeout"]) ? $_REQUEST["snmp_timeout"] : read_config_option("snmp_timeout");
		$max_get_size = isset($_REQUEST["max_get_size"]) ? $_REQUEST["max_get_size"] : read_config_option("max_get_size");

		$host_template_id = $_REQUEST["host_template_id"];
		$hostname = $_REQUEST["hostname"];
		$description = $_REQUEST["description"];
		$device_threads = $fields_host_edit["device_threads"]["default"];

		$id = null;

		$response["id"] = api_device_save($id, $host_template_id, $description, $hostname, $snmp_community, $snmp_version, $snmp_username, $snmp_password, $snmp_port, $snmp_timeout, $disabled, $availability_method, $ping_method, $ping_port, $ping_timeout, $ping_retries, $notes, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $max_oids, $device_threads);

		$response["test"] = $_SESSION["sess_error_fields"];

		$tmp = json_encode($response, JSON_PRETTY_PRINT);
		$tmp = str_replace("\\/", "/", $tmp);
		echo $tmp;
	}

	/**
	 * Update a device
	 * In HTTP request: PUT /api_url/devices
	 * @todo complete this function
	 */
	function _PUT() {
		
	}

	/**
	 * Delete a device (or host)
	 * In HTTP request: DELETE /api_url/devices/4
	 * In this case device id is 4, it will be delete
	 * @todo complete this function
	 */
	function _DELETE_ID() {
		
	}

	/**
	 * Find whether the $id is valid graph template id
	 * @param integer $id graph template id
	 * @return boolean true, if $id is a graph template
	 */
	static function isDevice($id) {
		$tmp = db_fetch_assoc("SELECT *
				FROM host WHERE id = '" . addslashes($id) . "'");
		return count($tmp) ? true : false;
	}

}

?>