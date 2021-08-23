<?php

namespace PhpHunter\Utils;

use PhpHunter\Controllers\DumperController;

class FileTools
{
    /**
     * @description Class Constructor
     */
    public function __construct()
    {
    }

    /**
     * @description Class Destructor
     */
    public function __destruct()
    {
    }

    /**
     * @description Check If Exists Files
     * @param string $path #Mandatory
     * @param string $filename #Mandatory
     * @param array $types #Mandatory
    */
    public static function checkIfExistsFilesByType(string $path, string $filename, array $types): bool
    {
        $path = preg_replace('/\/$/', '', $path);
        for ($h = 0; $h < count($types); $h++) {
            $type = preg_replace('/\./', '', $types[$h]);
            if (file_exists($path."/".$filename.".".$type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @description Check Readable File
     * @param string $file #Mandatory
     */
    public static function checkReadableFile(string $file = "")
    {
        if (!is_readable($file) || !is_file($file) || !file_exists($file)) {
            DumperController::smartDumper("Unabled to read file: {$file}", true);
        }
    }

}
