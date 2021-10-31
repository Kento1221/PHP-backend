<?php

namespace App\Services;

class ImportService
{
    /**
     * Check if file has been sent in the POST request.
     * @param $post_array
     * @param $filename
     * @return bool
     */
    public static function validateFileInput($post_array, $filename): bool
    {
        if (!isset($post_array[$filename])) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 422 Unprocessable Entity');
            echo json_encode(['message' => 'No `' . $filename . '` file type field found.']);
            return false;
        }
        return true;
    }

    /**
     * Get array of entities from a csv file.
     * @param string $csvFilePath
     * @param int $colNumber
     * @param string $delimiter
     * @@return array|bool
     */
    public static function getDataArrayFromCsvFile($csvFilePath, $colNumber = 0, $delimiter = ',')
    {
        $row = 1;
        $data_array = [];
        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            while ($data = fgetcsv($handle, 0, $delimiter)) {
                $num = count($data);
                $row++;
                for ($c = 0; $c < $num; $c++) {
                    $data_array[] = $data[$c];
                }
            }
            fclose($handle);
            $data_array = array_chunk($data_array, $colNumber);
            return ['number_of_rows' => $row, 'data' => $data_array];
        }
        return false;
    }

    /**
     * Check whether the data array has been correctly created. If not return false.
     * @param $data_array
     * @return bool
     */
    public static function checkIfDataArrayExists($data_array): bool
    {
        if (!$data_array) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 409 Conflict');
            echo json_encode(['message' => 'Getting data from the file failed.']);
            return false;
        }
        return true;
    }
}