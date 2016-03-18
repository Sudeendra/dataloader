<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 9:41 PM
 */

namespace dataloader\datasource;

use dataloader\logger\LoggerUtil;
use src\dataloader\IDataService;

//TODO: Exception handling

class MySQLDataService implements IDataService
{
    var $connection = null;
    var $entity = null;

    public function __construct($entity)
    {
        $this->getConnection();
        $entity = $entity;
    }

    public function getConnection()
    {
        if ($this->connection == null) {
            $this->connection = new \mysqli("localhost", "mysql", "XXXX", "data_loader");
        }
        $this->connection;
    }

    public function getSchemaColumns()
    {
        $schemaSql      = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name='".$this->entity."' and table_schema = '".self::$customerdb."'";
        $schemaColumns  = $this->execDBQuery($schemaSql);
        return !empty($schemaColumns);
    }


    public function loadData($columns, $values)
    {
        $conn = $this->getConnection();

        $insertQryTmpl = "INSERT INTO ".$this->entity." ($columns) VALUES ";

        $this->getConnection()->autocommit(FALSE);

        $res = $this->insertData($insertQryTmpl, $values);

        if (!$res) {
            $err = true;
            $this->insertRowByRowData($insertQryTmpl, $values);
        }

        // Just setting it back to the default value.
        $this->getConnection()->autocommit(TRUE);

        if ($err) {
            LoggerUtil::log("Failed to import file into table ".$this->entity, "error");
        }
        else if ($this->debug !== TRUE) {
            LoggerUtil::log("Successfully imported values into table ".$this->entity, "info");
        }
    }

    private function insertData($insertQryTmpl, $values, $singleRow = false)
    {
        if ($singleRow) {
            $valuesStr = $values;
        }
        else {
            $valuesStr = implode(",",$values);
        }

        $insertSql = $insertQryTmpl.$valuesStr;

        try {
            $res = $this->execDBQuery($insertSql, false);
        }
        catch (Exception $e) {
            LoggerUtil::log("got exception".$e->getMessage());
        }

        if ($res !== TRUE || self::$debug === TRUE) {
            $this->getConnection()->rollback();
            if ($this->debug !== TRUE) {
                LoggerUtil::log("Failed to insert values by the insert SQL ".$insertSql, "error");
            }
            else {
                LoggerUtil::log("Debug mode enabled, so rolled back the insert SQL ".$insertSql, "error");
            }
        }
        else {
            $this->getConnection()->commit();
            LoggerUtil::log("success to insert values by the insert SQL ".$insertSql, "debug");
        }

        return $res;

    }

    private function insertRowByRowData($insertQryTmpl, $values)
    {
        $errorRows = array();
        foreach ($values as $idx => $value) {
            $res = $this->insertData($insertQryTmpl, $value, true);
            if (!$res) {
                //$errorRows[] = self::$batchOrigData[$idx];
            }
        }

    }

    public function execDBQuery($sql, $isQuery = true)
    {
        $conn = $this->getConnection();

        $rows = array();
        //self::message($sql."\n", 'info');
        $results = $conn->query($sql);

        if (!$isQuery) {
            return $results;
        }

        if (!$results) {
            LoggerUtil::log($sql . "\n");
            LoggerUtil::log("\n" . $conn->errno . "  " . $conn->error . "\n");
            return $rows;
        }

        while ($row = $results->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
