<?php

namespace App\Services;

use App\Config;
use App\Models\Record;
use App\Models\SQLiteDatabaseConnection;
use PDO;

class RecordService
{
    /**
     * Validate store method POST request fields. Return false and echo error if not successful.
     * @param $post_array
     * @return bool
     */
    public static function validateStoreInput($post_array): bool
    {
        $missingVariables = [];

        foreach (Config::DATABASE_RECORD_TABLE_FIELDS as $variableName) {
            if (!isset($post_array[$variableName])) {
                $missingVariables[] = 'Missing property: ' . $variableName;
            }
        }
        if (!empty($missingVariables)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 422 Unprocessable Entity");
            echo json_encode($missingVariables);
            return false;
        }

        if(!intval($post_array['customer_id'])) {
            header($_SERVER["SERVER_PROTOCOL"] . " 422 Unprocessable Entity");
            echo json_encode(['message' => 'Parameter type error: customer id should be an integer']);
            return false;
        }
        return true;
    }

    /**
     * Check whether the id provided is correct and exists in `records` table.
     * @param array $post_array
     * @return bool
     */
    public static function validateRecordIdInput($post_array): bool
    {
        if (!isset($post_array['id'])) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 422 Unprocessable Entity');
            echo json_encode(['message' => 'Missing Parameter: id(int)']);
            return false;
        }

        $db = new SQLiteDatabaseConnection();
        $conn = $db->connect();

        $result = $conn->prepare('SELECT * FROM ' . Record::TABLE_NAME . ' WHERE id = :id;');
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
}