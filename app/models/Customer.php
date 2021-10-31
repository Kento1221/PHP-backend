<?php

namespace App\Models;

use App\Services\CustomerService;
use PDO;

class Customer
{
    //todo: change to private
    public $data = [];
    private int $id;
    public $all_calls = 0;
    public $all_continental_calls = 0;
    public $sum_duration = 0;
    public $sum_continental_duration = 0;

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
     * Get array of customer's call details.
     * @return array
     * <p>array(<br>customer_ip, <br>all_calls, <br>all_continental_calls, <br>sum_duration, <br>sum_continental_duration<br>)</p>
     */
    public function getDetailsArray()
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