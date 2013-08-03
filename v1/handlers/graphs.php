<?php
/**
 * Cacti graphs handler
 * 
 * @author Gergely Viktor Asztalos <agv@rsh.hu>
 */

	namespace handlers;

	class graphs  extends \handlers\base {
		
		public $allowDirect = true;
		public $allowFrom = array("devices");
		
		public $extend = array(
//			"ID",
//			"ID/METHOD",
			"HANDLER",
			"METHOD"
		);

		/**
		 * Test GET function
		 */
		function _GET(){
			$out->x = 1;
			echo json_encode($out,JSON_PRETTY_PRINT);
		}
		
		/**
		 * Create host graph 
		 *		POST /graphs
		 * 
		 * @return type
		 * @throws \RestServiceException
		 */
		function _POST(){
			$templateId = $_POST["graph_template_id"];
			$hostId = $_POST["host_id"];
			$out = new \stdClass();

			if (!\handlers\templates::isGraphTemplate($templateId)) {
				throw new \RestServiceException("Unknown graph-template-id (" . $templateId . ")", 400);				return;
			}
			
			if (!\handlers\devices::isDevice($hostId)) {
				throw new \RestServiceException("Unknown host_id (" . $hostId . ")", 400);				
				return;
			}
			
			$existsAlready = db_fetch_cell("SELECT id FROM graph_local WHERE graph_template_id=$templateId AND host_id=$hostId");

			if ((isset($existsAlready)) &&
				($existsAlready > 0) &&
				(!$force)) {
				$dataSourceId  = db_fetch_cell("SELECT
					data_template_rrd.local_data_id
					FROM graph_templates_item, data_template_rrd
					WHERE graph_templates_item.local_graph_id = " . $existsAlready . "
					AND graph_templates_item.task_item_id = data_template_rrd.id
					LIMIT 1");

				throw new \RestServiceException("Not Adding Graph - this graph already exists - graph-id: ($existsAlready) - data-source-id: ($dataSourceId)", 400);
				
				return;
			}else{
				$returnArray = create_complete_graph_from_template($templateId, $hostId, "", $values["cg"]);
				$dataSourceId = "";
			}

			if ($graphTitle != "") {
				db_execute("UPDATE graph_templates_graph
					SET title=\"$graphTitle\"
					WHERE local_graph_id=" . $returnArray["local_graph_id"]);

				update_graph_title_cache($returnArray["local_graph_id"]);
			}

			foreach($returnArray["local_data_id"] as $item) {
				push_out_host($hostId, $item);

				if (strlen($dataSourceId)) {
					$dataSourceId .= ", " . $item;
				}else{
					$dataSourceId = $item;
				}
			}

			/* add this graph template to the list of associated graph templates for this host */
			db_execute("replace into host_graph (host_id,graph_template_id) values (" . $hostId . "," . $templateId . ")");


			
			$out->result = "Graph Added - graph-id: (" . $returnArray["local_graph_id"] . ") - data-source-ids: ($dataSourceId)";
			$tmp = json_encode($out,JSON_PRETTY_PRINT);
			$tmp = str_replace("\\/","/",$tmp);
			echo $tmp;
		}
		

		

		

		
	}
?>
