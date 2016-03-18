<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 7:12 PM
 */

namespace src\dataloader;


interface IFileParser
{
    public function isSecuredAndValid();
    public function parseRow();
}