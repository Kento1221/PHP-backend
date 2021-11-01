<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;
use App\Services\ImportService;

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../../config.php';
include_once '../../../../app/services/ImportService.php';
include_once '../../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../../app/models/Record.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    return http_response_code(200);
}
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