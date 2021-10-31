<?php

use App\Models\Geoname;
use App\Models\SQLiteDatabaseConnection;
use App\Services\ImportService;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../config.php';
include_once '../../../app/services/ImportService.php';
include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Geoname.php';

if (!ImportService::validateFileInput($_FILES, 'geonames')) {
    return;
}

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Geoname::TABLE_NAME);


$data_array = ImportService::getDataArrayFromCsvFile($_FILES['geonames']['tmp_name'], 4, ';');
if (!$data_array) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 409 Conflict');
    echo json_encode(['message' => 'Getting data from the file failed.']);
    return;
}
Geoname::storeMany($connection, $data_array);
$db->close();