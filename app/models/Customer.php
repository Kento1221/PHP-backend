<?php

namespace App\Models;

use App\Services\CustomerService;
use PDO;

class Customer
{
    private int $id;
    private $all_calls = 0;
    private $all_continental_calls = 0;
    private $sum_duration = 0;
    private $sum_continental_duration = 0;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get all rows of number_called in relation to customer_ip from records table.
     * @param PDO $connection
     * @return array|false
     */
    public function getArrayOfCustomerCallDetails($connection)
    {
        $query = "SELECT id, customer_ip, number_called, call_duration FROM " . Record::TABLE_NAME . " WHERE customer_id = :id;";
        $result = $connection->prepare($query);
        $result->bindParam(':id', $this->id);
        $result->execute();
        $rows = $result->fetchAll(PDO::FETCH_CLASS);

        if (empty($rows)) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            echo json_encode(['message' => 'No customer found']);
            return false;
        }
        return $rows;
    }

    /**
     * Calculate customer's (intercontinental and continental) number of calls and sum of call durations.
     * @param $connection
     * @param $customer_call_details_array
     */
    public function calculateCustomerCallDetails($connection, $customer_call_details_array): void
    {
        CustomerService::markContinentalCalls($connection, $customer_call_details_array);

        $this->all_calls = count($customer_call_details_array);
        foreach ($customer_call_details_array as $record) {
            if ($record->is_continental) {
                $this->all_continental_calls++;
                $this->sum_continental_duration += $record->call_duration;
            }
            $this->sum_duration += $record->call_duration;
        }
    }

    /**
     * Get array of customer's call details.
     * @return array
     * <p>array(<br>customer_id, <br>all_calls, <br>all_continental_calls, <br>sum_duration, <br>sum_continental_duration<br>)</p>
     */
    public function getDetailsArray(): array
    {
        return array(
            'customer_id' => $this->id,
            'all_calls' => $this->all_calls,
            'all_continental_calls' => $this->all_continental_calls,
            'sum_duration' => $this->sum_duration,
            'sum_continental_duration' => $this->sum_continental_duration,
        );
    }
}