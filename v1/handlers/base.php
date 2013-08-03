<?php

/**
 * @author Gergely Viktor Asztalos <agv@rsh.hu>
 */

namespace handlers;

class base {

	public $parentHandler;
	public $service;

	function __construct() {
		$this->parentHandler = \RestService::$currentHandler;

		try {
			$this->walk();
		} catch (Exception $e) {
			echo $e;
		}
	}

	/**
	 * Walk on route URI
	 * @return type
	 * @throws \RestServiceException
	 */
	function walk() {
		if (count(\RestService::$route) > \RestService::$routeLevel) {
			foreach ($this->extend as $extStr) {
				$currentRouteLevel = \RestService::$routeLevel;
				$extArray = explode("/", $extStr);

				$tmpAssoc = array();

				foreach ($extArray as $extId => $ext) {
					\RestService::$routeValue = \RestService::$route[$currentRouteLevel];
					if (method_exists($this, "_is_" . $ext) && $this->{"_is_" . $ext}()) {
						$tmpAssoc[str_replace("handlers\\", "", get_class($this)) . "." . implode(".", array_slice($extArray, count($extArray) - $extId - 1))] = \RestService::$routeValue;
						if (method_exists($this, "_pass_" . $ext)) {
							\RestService::$routeAssoc = array_merge(\RestService::$routeAssoc, (array) $tmpAssoc);
							$currentRouteLevel++;
							\RestService::$routeLevel = $currentRouteLevel;
							$this->{"_pass_" . $ext}();
							return;
						}
					} else {
						continue 2;
					}

					if (count(\RestService::$route) == (\RestService::$routeLevel + 1)) {

						$classMethod = "_" . \RestService::$method . "_" . implode("_", $extArray);
						if (method_exists($this, $classMethod)) {
							\RestService::$routeAssoc = array_merge(\RestService::$routeAssoc, (array) $tmpAssoc);
							$this->$classMethod();
							return;
						} else {
							throw new \RestServiceException("Bad request 2", 400);
						}
					}
				}
			}
		} else {

			$classMethod = "_" . \RestService::$method;
			if (method_exists($this, $classMethod)) {
				$this->$classMethod();
			} else {
				throw new \RestServiceException("Bad request", 400);
			}
		}
	}

	/**
	 * ID type validation
	 * @return boolean
	 */
	function _is_ID() {
		return (\RestService::$routeValue * 1 == \RestService::$routeValue && \RestService::$routeValue > 0);
	}

	/**
	 * @todo Implement METHOD type
	 */
	function _is_METHOD() {
		
	}

	/**
	 * HANDLER type validation
	 * @return boolean
	 */
	function _is_HANDLER() {
		return class_exists("handlers\\" . \RestService::$routeValue, true);
	}

	/**
	 * Handle filter request from $_REQUEST. In response only filter fields delivered, all other fields aren't send.
	 * @param type $aFields
	 * @return array filtered fields
	 * @throws \RestServiceException
	 */
	function _filterFields($aFields) {
		$rFields = explode(",", $_REQUEST["fields"]);
		if (!isset($_REQUEST["fields"]) || count($rFields) == 0)
			return $aFields;
		if (count(array_diff($rFields, $aFields)) > 0) {
			throw new \RestServiceException("Bad request", 400);
		} else {
			return $rFields;
		}
	}

	/**
	 * If handler type is valid, this funtion pass to next class on the route. 
	 * @return \handlers\* 
	 */
	function _pass_HANDLER() {
		\RestService::$currentHandler = end(explode("\\", get_class($this)));
		$handlerClass = "handlers\\" . \RestService::$routeValue;
		return new $handlerClass;
	}

}

?>