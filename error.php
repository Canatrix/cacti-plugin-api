<?

include 'v1/classes/exceptions.php';

$e = new RestServiceException($_SERVER["REDIRECT_STATUS"], $_SERVER["REDIRECT_STATUS"]);
echo $e;


?>