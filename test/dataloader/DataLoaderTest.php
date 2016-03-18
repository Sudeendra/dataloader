<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 7:40 PM
 */

namespace test\dataloader;

use src\dataloader\DataLoader;
use src\dataloader\FileType;

//require '../src/dataloader/DataLoader.php';

//require 'C://Users/sramesh/Downloads/phpunit-5.2.12.phar/phpunit/Util/Blacklist.php';

class DataLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadDataFromFile() {
        $dataLoader = new DataLoader();
        $dataLoader->loadDataFromFile('xyz', 'mysql', FileType::CSV, 'Persons');
    }
}
