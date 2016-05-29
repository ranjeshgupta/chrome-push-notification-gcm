<?php
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

$gcm_json  = file_get_contents("GcmJson.txt");

echo $gcm_json;

?>