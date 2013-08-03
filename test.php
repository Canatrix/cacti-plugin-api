<?php
/*
<html>
	<head>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script src="http://jpillora.com/jquery.rest/dist/jquery.rest.min.js"></script>
	</head>
	<body>
		
	</body>
</html>
 */
header("Content-type: text/plain");
include './classes/client.php';
/*$c = new clientREST();


//echo $c->execRequest('http://192.168.56.101/cacti/plugins/api/devices','post','hello=world&test=true');
//echo $c->execRequest('http://192.168.56.101/cacti/plugins/api/devices','get','hello=world&test=true');
//print_r($res);
echo $c->execRequest('http://192.168.56.101/cacti/plugins/api/devices','put','hello=world&test=true');
echo $c->execRequest('http://192.168.56.101/cacti/plugins/api/devices','post','hello=world&test=true');
echo $c->execRequest('http://192.168.56.101/cacti/plugins/api/devices','delete','hello=world&test=true');
*/
$api = new RestClient(array(
    'base_url' => "http://192.168.56.101/cacti/plugins/api", 
    'format' => "json"
));

try {
	$add["host_id"] = 789;
	$add["graph_template_id"] = 43;
	$result = $api->post("graphs", $add );
	echo "Response:".$result->response;
} catch (Exception $exc) {
	echo $exc->getTraceAsString();
}
?>