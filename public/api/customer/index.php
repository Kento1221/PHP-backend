<?php

use App\Models\Customer;
use App\Models\SQLiteDatabaseConnection;
use App\Services\CustomerService;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: application/json');

include_once '../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../app/services/CustomerService.php';
include_once '../../../app/models/Record.php';
include_once '../../../app/models/Customer.php';
include_once '../../../app/models/Geoname.php';
include_once '../../../config.php';

if (!CustomerService::validateCustomerIdInput($_GET)) {
    return;
}

$db = new SQLiteDatabaseConnection();
$connection = $db->connect();
$customer = new Customer($_GET['id']);

$customer_call_details_array = $customer->getArrayOfCustomerCallDetails($connection);
if (!$customer_call_details_array) return;

$customer->calculateCustomerCallDetails($connection, $customer_call_details_array);

echo json_encode($customer->getDetailsArray());
$db->close();

