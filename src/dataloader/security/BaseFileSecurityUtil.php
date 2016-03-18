<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 7:15 PM
 */

namespace src\dataloader\security;


//use SplFileInfo;
use src\dataloader\FileType;

class BaseFileSecurityUtil
{
    public static function isSecuredFile($file) {
        $info = pathinfo($file);

        if (self::checkFileExtension($info->getExtension())) {
            return true;
        }
        return false;
    }

    private static function checkFileExtension(/*String*/ $fileName) {
        $fileType = new FileType();
        if (in_array($fileName, $fileType->getConstList(true))) {
            return true;
        }
        return false;
    }
}