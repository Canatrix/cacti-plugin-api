<?php

/**
 * 
 * @author Gergely Viktor Asztalos <agv@rsh.hu>
 */
class RestService extends handlers\base {

	public static $route;

	/**
	 * Current level of URI request processing.
	 * @var integer URI request tag level
	 */
	public static $routeLevel = 0;

	/**
	 *
	 * @var string Current route value 
	 */
	public static $routeValue;
	public static $method;
	public static $routeAssoc = array();
	public static $format = "json";
	public static $formatMark = 0;
	public static $currentHandler = "";
	private $supportedFormats = array("json");
	private $supportedMethods;
	public $extend = array(
		"HANDLER"
	);

	/**
	 * Constructor of RestService. Main initialization of service.
	 * @throws \RestServiceException
	 */
	public function __construct() {
		try {
			header('Content-Type: application/json; charset=utf-8');

			if (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_USER"])) {
				$user = explode("x", $_SERVER["PHP_AUTH_USER"]);
				if (self::auth($_SERVER["PHP_AUTH_USER"], $user[0], $user[1])) {
					throw new \RestServiceException("Forbidden", 403);
				};
			} else {
				throw new \RestServiceException("Forbidden", 403);
			}

			$pi = pathinfo($_SERVER["PATH_INFO"]);
			$pi = $pi["dirname"] . ($pi["dirname"] == "/" ? "" : "/") . $pi["filename"];

			self::$route = $pi == "/" ? false : explode("/", $pi);

			self::$method = strtoupper($_SERVER["REQUEST_METHOD"]);
			array_shift(\RestService::$route);
			if (!self::$route) {
				throw new \RestServiceException("Bad request", 400);
			}
			$this->walk();
		} catch (Exception $e) {
			header($_SERVER["SERVER_PROTOCOL"] . " " . $e->getCode() . " " . $e->getMessage());
			echo $e;
		}
	}

	protected function getFullUrl() {
		$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$location = $_SERVER['PATH_INFO'];
		if ($_SERVER['QUERY_STRING']) {
			$location = substr($location, 0, strrpos($location, $_SERVER['QUERY_STRING']) - 1);
		}
		return $protocol . '://' . $_SERVER['HTTP_HOST'] . $location;
	}

	function auth($api_key, $user_id = false, $api_key_id = false) {
		$result = db_fetch_assoc("SELECT * FROM api_key WHERE api_key = '" . addslashes($api_key) . "'" . ($user_id ? " AND user_id = '" . addslashes($user_id) . "'" : "") . ($api_key_id ? " AND api_key_id = '" . addslashes($api_key_id) . "'" : ""));
		if (count($result) == 1) {
			return true;
		} else {
			return false;
		}
	}

}

?>