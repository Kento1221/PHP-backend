<?php

namespace App\Models;

use App\Config;
use Exception;
use PDO;
use PDOException;

class SQLiteDatabaseConnection
{
    private $conn;

    /**
     * Connect to the sqlite file database;
     * @return PDO
     */
    public function connect()
    {
        if ($this->conn == null)
            try {
                $this->conn = new \PDO("sqlite:" . Config::DATABASE_FILE_PATH);
            } catch (PDOException $exception) {
                throw new PDOException("Connection Error: {$exception->getMessage()}");
            }

        return $this->conn;
    }

    /**
     * Close the connection to the sqlite file database;
     */
    public function close()
    {
        $this->conn = null;
    }

    /**
     * Create a new table for records.
     */
    public function createRecordsTable()
    {
        $query = 'CREATE TABLE IF NOT EXISTS records (
            id INTEGER PRIMARY KEY,
            customer_id INTEGER NOT NULL,
            call_date DATETIME NOT NULL,
            call_duration INTEGER NOT NULL,
            number_called varchar(12) NOT NULL,
            customer_ip varchar(15) NOT NULL
        );';

        $result = $this->conn->prepare($query);
        if (!$result->execute())
            throw new PDOException('Table `records` not created.');
    }

    /**
     * Create a new table for records.
     */
    public function createGeonamesTable()
    {
        $query = 'CREATE TABLE IF NOT EXISTS geonames (
            id INTEGER PRIMARY KEY,
            geoname_id INTEGER NULL,
            country_iso varchar(2) NOT NULL,
            continent varchar(2) NOT NULL,
            phone_code varchar(8) NULL
        );';

        $result = $this->conn->prepare($query);
        if (!$result->execute())
            throw new PDOException('Table `geonames` not created.');
    }

    /**
     * Create a new table using csv data. If no table name is given the file name is used to create table.
     * Options available: delimiter, table, fields.
     *
     * @param PDO $pdo
     * @param string $csv_path
     * @param array $options
     * @return array
     */
    public function createTableFromCsvFile(&$pdo, $csv_path, $options = array())
    {
        extract($options);

        if (($csv_handle = fopen($csv_path, "r")) === FALSE)
            throw new Exception('Cannot open CSV file');

        if (!isset($delimiter))
            $delimiter = ',';

        if (!isset($table))
            $table = preg_replace("/[^A-Z0-9]/i", '', basename($csv_path));

        if (!isset($fields)) {
            $fields = array_map(function ($field) {
                return strtolower(preg_replace("/[^A-Z0-9]/i", '', $field));
            }, fgetcsv($csv_handle, 0, $delimiter));
        }

        $create_fields_str = join(', ', array_map(function ($field) {
            return "$field TEXT NULL";
        }, $fields));

        $pdo->beginTransaction();

        $create_table_sql = "CREATE TABLE IF NOT EXISTS $table ($create_fields_str)";
        $pdo->exec($create_table_sql);

        $insert_fields_str = join(', ', $fields);
        $insert_values_str = join(', ', array_fill(0, count($fields), '?'));
        $insert_sql = "INSERT INTO $table ($insert_fields_str) VALUES ($insert_values_str)";
        $insert_sth = $pdo->prepare($insert_sql);

        $inserted_rows = 0;
        while (($data = fgetcsv($csv_handle, 0, $delimiter)) !== FALSE) {
            $insert_sth->execute($data);
            $inserted_rows++;
        }

        $pdo->commit();

        fclose($csv_handle);

        return array(
            'table' => $table,
            'fields' => $fields,
            'insert' => $insert_sth,
            'inserted_rows' => $inserted_rows
        );

    }

    public function checkIfTableExists($table_name)
    {
        $query = 'SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'' . $table_name . '\';';

        $result = $this->conn->prepare($query);
        $result->execute();

        if ($result->rowCount() == 0)
            switch ($table_name) {
                case 'geonames':
                    $this->createGeonamesTable();
                    break;
                case 'records':
                    $this->createRecordsTable();
                    break;
            }
    }
}