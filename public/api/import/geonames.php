<?php

use App\Models\SQLiteDatabaseConnection;

include_once '../../../config.php';
include_once '../../../app/interfaces/Database.php';
include_once '../../../app/models/SQLiteDatabaseConnection.php';

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();

$result = $connection->prepare('SELECT * FROM records');
$result->execute();

echo $result->columnCount();