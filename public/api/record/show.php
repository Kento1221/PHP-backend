<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';
include_once '../../../config.php';

if (!isset($_GET['id'])) {

    header($_SERVER["SERVER_PROTOCOL"] . " 422 Unprocessable Entity");
    echo json_encode(['message' => 'Parameter missing: id(int).']);

} else {

    $db = new SQLiteDatabaseConnection();
    $result = Record::show($db->connect(), $_GET['id']);
    if (empty($result))
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    else
        echo json_encode($result);

    $db->close();

}
