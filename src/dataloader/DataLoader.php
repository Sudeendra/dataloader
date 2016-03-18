<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 6:49 PM
 */

namespace src\dataloader;

use dataloader\datasource\MySQLDataService;
use src\dataloader\parser\CSVFileParser;

class DataLoader
{
    const PREFERENCES_FILE = 'resources\preferences.yaml';

    public function loadDataFromFile($filePath, $dataSourceType, $importType, $entity) {
        //$preferences = yaml_parse_file(self::PREFERENCES_FILE);
        // TODO: should ideally come from the yaml file.
        $preferences = array();
        $preferences["loader_data_source"] = $dataSourceType;
        $preferences["loader_import_type"] = $importType;
        $preferences["import_chunk_size"] = 1000;
        $preferences["import_yield_secs"] = 1;

        // TODO: This should come from a dataservice factory
        $dataLoader = new MySQLDataService();
        $schemaColInfo = $dataLoader->getSchemaColumns($entity);

        // TODO: This should come from a fileparser factory
        $parser = new CSVFileParser($filePath);
        $headers = $parser->parseHeader();

        // validate we only have valid columns in the csv file.
        $invalidColumns = array_diff($headers, array_keys($schemaColInfo));
        if (!empty($invalidColumns)) {
            echo "headers do not match schema\n";
            return false;
        }

        // Handle system date fields
        $hasCreatedAt = false;
        $hasUpdatedAt = false;

        if (array_key_exists('created_at', $schemaColInfo)) {
            $headers[] = 'created_at';
            $hasCreatedAt = true;
        }
        if (array_key_exists('updated_at', $schemaColInfo)) {
            $headers[] = 'updated_at';
            $hasUpdatedAt = true;
        }

        $columns = implode(",",$headers);

        $i = 0;
        $err = false;
        $values = array();
        $cnt = 0;

        while (($data = $parser->parseRow()) !== FALSE) {

            // In case of error, we need the original datain the same format, so keep buffering.
            // Note, we will clear this for each batch so not holding a large data.
            //self::$batchOrigData[$cnt] = $csv_data;

            // Apply the conversion by datatype.
            $convertedVals = array();
            foreach ($data as $idx => $csvRowVal) {
                if ($schemaColInfo[$headers[$idx]] == 'int') {
                    $convertedVals[] = $csvRowVal;
                }
                else if ($schemaColInfo[$headers[$idx]] == 'datetime') {
                    $convertedVals[] = (!empty($csvRowVal) && $csvRowVal != 'NULL') ? "'".date('Y-m-d H:i:s', strtotime($csvRowVal))."'" : 'NULL';
                }
                else {
                    //$csvRowVal = mysql_escape_string($csvRowVal);
                    $convertedVals[] = "'$csvRowVal'";
                }
            }

            // Include these dates only if they are in the schema for the table.
            if ($hasCreatedAt) {
                $convertedVals[] = 'now()';
            }
            if($hasUpdatedAt) {
                $convertedVals[] = 'now()';
            }

            // Construct the values string.
            $values[]     = "(".implode(",",$convertedVals).")";

            $i++;
            $cnt++;
            // For every batch keep inserting the data.
            if (($i%$preferences["import_chunk_size"]) === 0) {

                $dataLoader->loadData($columns, $values);

                sleep($preferences['import_yield_secs']);

                // reset the batch values.
                $values = array();
                //self::$batchOrigData = array();
                $cnt = 0;
            }
        }

        // if no error, insert the last values batch
        if (!empty($values)) {
            $dataLoader->loadData($columns, $values);
        }

        $parser->freeResource();

    }

}