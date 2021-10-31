<?php

namespace App\Services;

class ImportService
{
    public static function validateFileInput($post_array, $filename)
    {
        if (!isset($post_array[$filename])) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 422 Unprocessable Entity');
            echo json_encode(['message' => 'No `'.$filename.'` file type field found.']);
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
}