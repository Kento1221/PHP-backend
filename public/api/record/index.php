<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';
include_once '../../../config.php';

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$db->checkIfTableExists(Record::TABLE_NAME);
echo json_encode(Record::getAll($connection));
$db->close();