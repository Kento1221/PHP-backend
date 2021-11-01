<?php

use App\Models\Customer;
use App\Models\SQLiteDatabaseConnection;
use App\Services\CustomerService;

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../../app/models/SQLiteDatabaseConnection.php';
include_once '../../../../app/services/CustomerService.php';
include_once '../../../../app/models/Record.php';
include_once '../../../../app/models/Customer.php';
include_once '../../../../app/models/Geoname.php';
include_once '../../../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    return http_response_code(200);
}
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

