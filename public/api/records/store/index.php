<?php

use App\Config;
use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;
use App\Services\RecordService;

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../../app/models/Record.php';
include_once '../../../../app/services/RecordService.php';
include_once '../../../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    return http_response_code(200);
}
if(!RecordService::validateStoreInput($_POST)){
    return;
}
$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Record::TABLE_NAME);
//needs format validation in the future
$record = new Record(
    $_POST['customer_id'],
    $_POST['call_date'],
    $_POST['call_duration'],
    $_POST['number_called'],
    $_POST['customer_ip']
);

$result = $record->store($connection);

if ($result) {
    echo json_encode($record->getRecordDataArray());
}
else {
    header($_SERVER["SERVER_PROTOCOL"] . " 409 Conflict");
    echo json_encode(['message' => 'Could not store the record.']);
}
$db->close();
return http_response_code(201);


