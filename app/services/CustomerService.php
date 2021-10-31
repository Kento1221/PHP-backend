<?php

namespace App\Services;

use App\Config;
use App\Models\Geoname;
use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;
use PDO;

class CustomerService
{

    //todo: could be generalized for many models
    /**
     * Validate id field from POST request.
     * @param array $post_array
     * @return bool
     */
    public static function validateCustomerIdInput($post_array): bool
    {
        if (!isset($post_array['id'])) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 422 Unprocessable Entity');
            echo json_encode(['message' => 'Missing Parameter: id(int)']);
            return false;
        }

        $db = new SQLiteDatabaseConnection();
        $conn = $db->connect();

        $result = $conn->prepare('SELECT count(1) FROM ' . Record::TABLE_NAME . ' WHERE customer_id = :id;');
        $result->bindParam(':id', $post_array['id'], PDO::PARAM_INT);
        $result->execute();
        $rows = $result->fetchAll(PDO::FETCH_COLUMN);
        $db->close();
        if (!empty($rows)) {
            return true;
        }

        header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        echo json_encode(['message' => 'No record found']);
        return false;
    }

    /**
     * Requests IPStack API for the continent_code of the ip address and returns it.
     * @param string $customer_ip
     * @return string|bool
     * <p>string - string of continent_code</p>
     * <p>bool - false if failed</p>
     */
    public static function getContinentFromIp($customer_ip)
    {
        $url_for_api_request = "http://api.ipstack.com/"
            . $customer_ip
            . "?access_key=" .
            Config::IP_STACK_ACCESS_KEY
            . "&fields=continent_code";

        $curl = curl_init();
        $options = [
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url_for_api_request,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ];
        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), 1);
        curl_close($curl);
        if (!$result) {
            return false;
        }

        return $result['continent_code'];
    }

    /**
     * Mark call records whether they are continental or not with boolean value assigned to is_continental object property.
     * @param PDO $connection
     * @param array $customer_call_details_array
     */
    public static function markContinentalCalls($connection, &$customer_call_details_array): void
    {
        // "info": "The Bulk Lookup Endpoint is not supported on the current subscription plan"
        // so I had to do it separately inside a loop :(
        for ($i = 0; $i < count($customer_call_details_array); $i++) {
            $row = $customer_call_details_array[$i];
            $continent = CustomerService::getContinentFromIp($row->customer_ip);
            if (is_bool($continent)) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 409 Conflict');
                echo json_encode(['message' => 'Something went wrong. Could not get continent_code from IPStack API.']);
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
    }
}