<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../config.php';
include_once '../../../app/services/CsvHelper.php';
include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();

if (!isset($_FILES['records'])) {
    echo json_encode(['message' => 'no file sent']);
    return;
}
$data_array = CsvHelper::getDataArrayFromCsvFile($_FILES['records']['tmp_name'], 5);
Record::storeMany($db, $data_array);