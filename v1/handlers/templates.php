<?php

namespace handlers;

class templates extends \handlers\base {

	/**
	 * @todo implemenet this variable
	 */
	public $allowDirect = true;

	/**
	 * How to interpret the URI route this handler.
	 * In HTTP request:
	 * 		"ID" -> METHOD /api_url/parent_handler/templates/4
	 * 		In this case the template id is 4.
	 * @var array list of how to use handler
	 */
	public $extend = array(
		"ID"
	);

	/**
	 * Return templates data
	 */
	function _GET() {

		switch ($this->parentHandler) {
			case"devices":
				$mainTable = "host_template";
				break;
			case"graphs":
				$mainTable = "graph_templates";
				break;
		}

		$fields = $this->_filterFields($this->_GET_fields());

		$offsetMax = db_fetch_cell("SELECT count(*) FROM {$mainTable}");

		$limit = $_REQUEST["limit"] * 1 == 0 ? 20 : $_REQUEST["limit"] * 1;
		$offset = $_REQUEST["offset"] * 1 == 0 ? 0 : $_REQUEST["offset"] * 1;

		$res = db_fetch_assoc("SELECT " . implode(",", $fields) . " FROM {$mainTable} LIMIT " . $offset . "," . $limit);
		
		foreach ($res as $row) {
			$input_fields = getInputFields($row["id"], $quietMode);
			$row["input_fields"] = $input_fields;
			$response["data"][] = $row;
		}

		$url = (new \classes\url())->fromContext();


		if ($offsetMax > $offset + $limit)
			$response["paging"]["next"] = (string) $url->replica()->addString("limit=" . $limit . "&offset=" . ($offset + $limit));
		if ($offset > 0)
			$response["paging"]["previous"] = (string) $url->replica()->addString("limit=" . $limit . "&offset=" . (($offset - $limit) < 0 ? 0 : $offset - $limit));

		$tmp = json_encode($response, JSON_PRETTY_PRINT);
		$tmp = str_replace("\\/", "/", $tmp);
		echo $tmp;
	}

	/**
	 * Return an array with template table name of fields, the exact name depends on the parent handler
	 * @return array an array with name of fields
	 */
	function _GET_fields() {
		switch ($this->parentHandler) {
			case"devices":
				$mainTable = "host_template";
				break;
			case"graphs":
				$mainTable = "graph_templates";
				break;
		}
		$sql_query = "SELECT COLUMN_NAME AS field
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = '{$mainTable}'
		AND table_schema = DATABASE()";
		$columns = db_fetch_assoc($sql_query);

		foreach ($columns as $column) {
			$fields[] = strtolower($column["field"]);
		}

		return $fields;
	}

	/**
	 * Find whether the $id is valid graph template id
	 * @param integer $id graph template id
	 * @return boolean true, if $id is a graph template
	 */
	static function isGraphTemplate($id) {
		$tmp = db_fetch_assoc("SELECT id, name
				FROM graph_templates WHERE id = '" . addslashes($id) . "'");

		return count($tmp) ? true : false;
	}

}

?>