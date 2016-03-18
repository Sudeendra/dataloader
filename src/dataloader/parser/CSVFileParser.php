<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 7:16 PM
 */

namespace src\dataloader\parser;


use src\dataloader\IFileParser;
use src\dataloader\security\BaseFileSecurityUtil;
//TODO: Exception handling

class CSVFileParser implements IFileParser
{
    var $fileHandle = null;

    public function __construct($file)
    {
        $this->fileHandle = fopen($file, 'r');
    }

    public function isSecuredAndValid()
    {
        BaseFileSecurityUtil::isSecuredFile($this->fileHandle);
        // TODO: Implement isSecuredAndValid() method.
    }

    public function freeResource()
    {
        fclose($this->fileHandle);
    }


    public function parseHeader()
    {
        // read the header first from the csv file.
        // Headers are mandatory, otherwise we cannot read data from it.
        $headerinfo = trim(fgets($this->fileHandle));
        $headers    = array_map('strtolower',explode(",",$headerinfo));

        return $headers;
    }

    public function parseRow()
    {
        return fgetcsv($this->fileHandle, self::CSV_LINE_BUFFER_SIZE, ",");
    }
}