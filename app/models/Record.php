<?php

namespace App\Models;

use PDO;

class Record
{
    public const TABLE_NAME = 'records';

    private $id = -1;
    private $customerId;
    private $callDate;
    private $callDuration;
    private $numberCalled;
    private $customerIp;

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
    public static function getAll($connection)
    {
        //Should be paginated if the database grows larger
        $query = "SELECT * FROM " . self::TABLE_NAME . ";";
        $result = $connection->prepare($query);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_CLASS);
    }

    /**
     * Get array of all distinct Customer IDs
     * @param PDO $connection
     * @return array
     */
    public static function getAllCustomerIds($connection):array
    {
        $query = "SELECT DISTINCT customer_id FROM " . Record::TABLE_NAME . " ORDER BY customer_id ASC;";
        $result = $connection->prepare($query);
        $result->execute();
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Creates a new record in the `records` table
     * @param PDO $connection
     * @return bool
     */
    public function store($connection): bool
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
        $result = $result->execute();
        $this->id = $connection->lastInsertId();

        return $result;
    }

    /** Inserts many records to `records` table.
     * @param PDO $connection
     * @param array $data
     * Format of array(customer_id, call_date, call_duration, number_called, customer_ip)
     * @return bool
     */
    public static function storeMany($connection, $data): bool
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
        $result = $connection->prepare($query);
        if (!$result) {
            echo json_encode($connection->errorInfo());
            return false;
        }
        return $result->execute();
    }

    /** Returns a record of provided id.
     * @param PDO $connection
     * @param int $id
     * @return array
     */
    public static function show($connection, $id): array
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id;";
        $result = $connection->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_CLASS);
    }

    /** Deletes a record with provided id from the table records
     * @param PDO $connection
     * @param int $id
     * @return bool
     */
    public static function destroy($connection, $id): bool
    {
        $query = "DELETE FROM " . self::TABLE_NAME . " WHERE id = :id;";
        $result = $connection->prepare($query);
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }

    /**
     * Returns an array with Record fields. If the record has not been added to the database the id field equals -1.
     * @return array
     */
    public function getRecordDataArray()
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'call_date' => $this->callDate,
            'call_duration' => $this->callDuration,
            'number_called' => $this->numberCalled,
            'customer_ip' => $this->customerIp
        ];
    }
}