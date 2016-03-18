<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 6:48 PM
 */

namespace src\dataloader;


interface IDataService
{

    public function getConnection();

    public function loadData($columns, $schemaColInfo);

}