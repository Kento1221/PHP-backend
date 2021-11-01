<?php

use App\Models\Geoname;
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
include_once '../../../../app/models/Geoname.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    return http_response_code(200);
}
if (!ImportService::validateFileInput($_FILES, 'geonames')) {
    return;
}

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Geoname::TABLE_NAME);


$data_array = ImportService::getDataArrayFromCsvFile($_FILES['geonames']['tmp_name'], 4, ';');
if (!ImportService::checkIfDataArrayExists($data_array)) {
    return;
}
Geoname::storeMany($connection, $data_array);
$db->close();