<?php
/**
 * 
 * Url manipulator class
 * 
 * @author Gergely Viktor Asztalos <agv@rsh.hu>
 */
namespace classes;

class url {

	private $scheme;
	private $private;
	private $host;
	private $port;
	private $user;
	private $pass;
	private $basePath;
	private $subPath;
	private $path;
	private $query;
	private $fragment;

	function __construct() {
		
	}

	function fromContext() {
		if ($_SERVER["HTTPS"] == "on") {
			$scheme = "https";
		} else {
			$scheme = "http";
		}
		$host = $_SERVER["SERVER_NAME"];
		$port = $_SERVER["SERVER_PORT"];
		$uri = $_SERVER["REQUEST_URI"];

		$url = $scheme . "://" . $host . ":" . $port . $uri;

		$url = parse_url($url);

		foreach ($url as $name => $value) {
			$this->{$name} = $value;
		}

		$this->basePath = dirname($_SERVER["SCRIPT_NAME"]);

		$this->basePath = dirname($_SERVER["SCRIPT_NAME"]);
		$this->subPath = dirname($_SERVER["REQUEST_URI"]);

		$this->subPath = preg_replace("/^" . addcslashes(preg_quote($this->basePath), "/") . "/", "", $this->path);

		parse_str($this->query, $this->query);
		return $this;
	}

	function replica() {
		$tmp = clone $this;
		return $tmp;
	}

	function __toString() {
		http_build_query($this->query);
		return $this->scheme . "://" . $this->host . ":" . $this->port . $this->basePath . $this->subPath . "?" . $this->queryString();
	}

	function addString($str) {
		parse_str($str, $tmp);
		$this->query = array_merge($this->query, $tmp);
		return $this;
	}

	function queryString() {
		$tmp = $this->_queryString();
		return implode("&", $tmp);
	}

	function _queryString($value = null, $key = null, $opt = array()) {
		static $path = array();
		static $tmp = array();

		if (isset($opt["on"]))
			array_push($path, count($path) ? "[" . ($opt["assoc"] ? $key : "") . "]" : $key);
		else {
			$value = $this->query;
			$path = array();
			$tmp = array();
		}

		if (is_array($value) || !isset($opt["on"])) {
			$opt["on"] = true;
			$opt["assoc"] = array_keys($value) !== range(0, count($value) - 1);
			array_walk($value, array($this, '_queryString'), $opt);
		} else {
			$tmp[] = implode("", $path) . "=" . $value;
		}

		if (isset($opt["on"]))
			array_pop($path);

		return $tmp;
	}

}


?>