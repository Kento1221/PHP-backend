<?php


use App\Models\Record;
use App\Services\RecordService;
use App\Models\SQLiteDatabaseConnection;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';
include_once '../../../app/services/RecordService.php';
include_once '../../../config.php';

if(!RecordService::validateRecordIdInput($_GET)){
    return;
}

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Record::TABLE_NAME);
$deleted = Record::destroy($connection, $_GET['id']);

if($deleted){
    echo json_encode(['message' => 'Record deleted successfully!']);
}
else{
    header($_SERVER["SERVER_PROTOCOL"] . " 409 Conflict");
    echo json_encode(['message' => 'Record deleting failed!']);
}

$db->close();