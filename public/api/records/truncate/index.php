<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../../app/models/Record.php';
include_once '../../../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    return http_response_code(200);
}

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Record::TABLE_NAME);
$deleted = $db->truncateTable(Record::TABLE_NAME);

if($deleted){
    echo json_encode(['message' => 'Record table truncated']);
}
else{
    header($_SERVER["SERVER_PROTOCOL"] . " 409 Conflict");
    echo json_encode(['message' => 'Record table truncate failed']);
}

$db->close();