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
     * Connect to the sqlite file database and return an instance of PDO;
     * @return PDO
     */
    public function connect(): PDO
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
    public function close(): void
    {
        $this->conn = null;
    }

    /**
     * Create a new `records` table in the database.
     * @throws PDOException
     * @throws Exception
     */
    public function createRecordsTable(): void
    {
        if ($this->conn == null)
            throw new Exception("Open connection before using `truncateTable`");

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
     * Create a new `geonames` table in the database.
     * @throws PDOException
     * @throws Exception
     */
    public function createGeonamesTable(): void
    {
        if ($this->conn == null)
            throw new Exception("Open connection before using `truncateTable`");

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
     * Check if Table $table_name exists. If not, create a new one in the database.
     * @param $table_name
     */
    public function checkIfTableExists($table_name): void
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

    /**
     * Clear table of data
     * @param string $table_name
     * @return bool
     * @throws Exception
     */
    public function truncateTable(string $table_name): bool
    {
        if ($this->conn == null)
            throw new Exception("Open connection before using `truncateTable`");

        $query = "DELETE FROM " . $table_name . " WHERE 1;";
        $result = $this->conn->prepare($query);

        return $result->execute();
    }
}