<?php
/**
 * 
 * Api exception collection
 * 
 * @author Gergely Viktor Asztalos <agv@rsh.hu>
 * 
 */

class RestServiceException extends \Exception {
	
	/**
	 * Convert exception to JSON string
	 * @return string exception details as JSON string
	 */
	function __toString() {
		$data = array(
			"error" => array(
			   "message" => $this->message,
			   "type" => get_class($this),
			   "code" => $this->code
			)
		 );
		return json_encode($data,JSON_PRETTY_PRINT);
	}
	
}

?>