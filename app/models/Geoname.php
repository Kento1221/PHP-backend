<?php

namespace App\Models;

use PDO;

class Geoname
{
    public const TABLE_NAME = 'geonames';

    /** Imports data from array into the database
     * @param PDO $connection
     * @param array $data
     * Format of array(geoname_id, country, continent, phone_code)
     * @return bool
     */
    public static function storeMany($connection, $data): bool
    {
        $query = "INSERT INTO "
            . self::TABLE_NAME .
            " (geoname_id, country_iso, continent, phone_code) VALUES ";

        $data_string = '';

        foreach ($data['data'] as $row) {
            $row[3] = preg_replace("/[^0-9]/", "", $row[3]);
            $data_string .= "({$row[0]}, '{$row[1]}', '{$row[2]}', '{$row[3]}'),";
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

    /**
     * @param PDO $connection
     * @param string $continent
     * @return array
     */
    public static function getPhoneCodesByCotinent($connection, $continent): array
    {
        $query = "SELECT phone_code FROM "
            . self::TABLE_NAME .
            " WHERE continent = :continent;";
        $result = $connection->prepare($query);
        $result->bindParam(':continent', $continent);
        $result->execute();

        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

}