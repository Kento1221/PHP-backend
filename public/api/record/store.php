<?php

use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/models/Record.php';
include_once '../../../config.php';

$postVariables = array('customer_id', 'call_date', 'call_duration', 'number_called', 'customer_ip');
$missingVariables = [];
foreach ($postVariables as $variableName) {
    if (!isset($_POST[$variableName])) {
        $missingVariables[] = 'Missing property: ' . $variableName;
    }
}
if (!empty($missingVariables)) {
    header($_SERVER["SERVER_PROTOCOL"] . " 422 Unprocessable Entity");
    echo json_encode($missingVariables);
    return;
}
$db = new SQLiteDatabaseConnection();
//needs validation in the future
$record = new Record(
    $_POST['customer_id'],
    $_POST['call_date'],
    $_POST['call_duration'],
    $_POST['number_called'],
    $_POST['customer_ip']
);
$result = $record->store($db->connect());
if ($result)
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
else
    echo json_encode([
        'customer_id' => $record->customerId,
        'call_date' => $record->callDate,
        'call_duration' => $record->callDuration,
        'number_called' => $record->numberCalled,
        'customer_ip' => $record->customerIp
    ]);
$db->close();


