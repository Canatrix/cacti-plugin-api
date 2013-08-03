<?
$CWD = getcwd();

// Simulate empty PHP_SELF
$_SERVER["PHP_SELF"] = "";
include("../../../include/global.php");

include_once("../../../lib/html_utility.php");
include_once("../../../lib/api_device.php");
include_once("../../../lib/api_automation_tools.php");
include_once("../../../lib/template.php");

include_once("./classes/service.php");
include_once("./classes/exceptions.php");

function __autoload($class) {
	$class = str_replace('\\', '/', $class) . '.php';
	if(!file_exists($class)) throw new \RestServiceException("Bad request", 400);
	require_once($class);
}

new RestService();

?>