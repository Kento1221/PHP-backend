<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;
use App\Services\ImportService;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../config.php';
include_once '../../../app/services/ImportService.php';
include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';

if (!ImportService::validateFileInput($_FILES, 'records')) {
    return;
}

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Record::TABLE_NAME);

$data_array = ImportService::getDataArrayFromCsvFile($_FILES['records']['tmp_name'], 5);
if (!ImportService::checkIfDataArrayExists($data_array)) {
    return;
}
Record::storeMany($connection, $data_array);
$db->close();