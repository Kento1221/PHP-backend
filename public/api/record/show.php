<?php

use App\Models\Geoname;
use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;
use App\Services\RecordService;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';
include_once '../../../app/services/RecordService.php';
include_once '../../../app/models/Geoname.php';
include_once '../../../config.php';

if (!RecordService::validateRecordIdInput($_GET)) {
    return;
}
$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$record = Record::show($connection, $_GET['id']);
echo json_encode($record);
$db->close();

