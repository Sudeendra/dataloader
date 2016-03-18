<?php
/**
 * Created by PhpStorm.
 * User: sramesh
 * Date: 3/17/2016
 * Time: 10:26 PM
 */

namespace dataloader\logger;


class LoggerUtil
{
    public static function log($msg, $level="info")
    {
        //$preferences = yaml_parse_file(self::PREFERENCES_FILE);
        // TODO: should ideally come from the yaml file.
        $preferences = array();
        $preferences["debug"] = true;

        if (($preferences["debug"] === true) || ($level != 'debug')) {
            printf("%s <%05d> [%5.5s] %s\n", strftime('%b %d %H:%M:%S'), getmypid(), $level, $msg);
        }
    }
}