<?php

namespace App\Models;

use App\Interfaces\Database;
use PDO;

class Record
{
    private const TABLE_NAME = 'records';

    public $customerId;
    public $callDate;
    public $callDuration;
    public $numberCalled;
    public $customerIp;

    public function __construct(
        $customerId,
        $callDate,
        $callDuration,
        $numberCalled,
        $customerIp)
    {
        $this->customerId = $customerId;
        $this->callDate = $callDate;
        $this->callDuration = $callDuration;
        $this->numberCalled = $numberCalled;
        $this->customerIp = $customerIp;
    }

    /** Returns a lost of all records
     * @param PDO $connection
     * @return array
     */
    public static function index($connection)
    {
        //Should be paginated if the database grows larger
        $query = "SELECT * FROM " . self::TABLE_NAME . ";";
        $result = $connection->prepare($query);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_CLASS);
    }

    /** Creates a new record in the database
     * @param PDO $connection
     * @return bool
     */
    public function store($connection)
    {
        $query = 'INSERT INTO '
            . $this::TABLE_NAME . ' (
            customer_id, 
            call_date, 
            call_duration, 
            number_called, 
            customer_ip) 
            VALUES (
            :customer_id, 
            :call_date,
            :call_duration,
            :number_called,
            :customer_ip
            );';

        $result = $connection->prepare($query);

        //Should be sanitized before entering into db in the future
        $result->bindParam(':customer_id', $this->customerId);
        $result->bindParam(':call_date', $this->callDate);
        $result->bindParam(':call_duration', $this->callDuration);
        $result->bindParam(':number_called', $this->numberCalled);
        $result->bindParam(':customer_ip', $this->customerIp);

        return $result->execute();
    }

    /** Creates a new record in the database
     * @param Database $database
     * @param array $data
     * @return array
     */
    public static function storeMany($database, $data)
    {
        $query = "INSERT INTO "
            . self::TABLE_NAME .
            " (customer_id, call_date, call_duration, number_called, customer_ip) VALUES ";

        $data_string = '';

        foreach ($data['data'] as $row) {
            $data_string .= "({$row[0]}, '{$row[1]}', {$row[2]}, '{$row[3]}', '{$row[4]}'),";
        }
        $data_string = substr($data_string, 0, -1);
        $data_string .= ';';
        $query .= $data_string;
        $conn = $database->connect();
        $result = $conn->prepare($query);
        if (!$result) {
            echo json_encode($conn->errorInfo());
            return false;
        }
        return $result->execute();
    }

    /** Returns a record by provided id
     * @param PDO $connection
     * @param int $id
     * @return array
     */
    public
    static function show($connection, $id)
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id;";
        $result = $connection->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_CLASS);
    }
}