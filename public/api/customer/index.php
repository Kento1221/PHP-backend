<?php

use App\Models\Customer;
use App\Models\Geoname;
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

// "info": "The Bulk Lookup Endpoint is not supported on the current subscription plan" :(
for ($i = 0; $i < count($customer_call_details_array); $i++) {

    $row = $customer_call_details_array[$i];
    $continent = CustomerService::getContinentFromIp($row->customer_ip);
    if (!$continent) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 409 Conflict');
        echo json_encode(['message' => 'Something went wrong. Could not get continent_code from IPStack API.']);
        return;
    }
    $phone_codes = Geoname::getPhoneCodesByCotinent($connection, $continent);

    $row->is_continental = false;
    foreach ($phone_codes as $code) {
        if ($code != "" && substr($row->number_called, 0, strlen($code)) === $code) {
            $row->is_continental = true;
            break;
        }
    }
}


foreach ($customer_call_details_array as $record) {
    if ($record->is_continental) {
        $customer->all_continental_calls++;
        $customer->sum_continental_duration += $record->call_duration;
    }
    $customer->all_calls++;
    $customer->sum_duration += $row->call_duration;
}
echo json_encode($customer->getDetailsArray());
$db->close();

