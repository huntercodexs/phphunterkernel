<?php

namespace PhpHunter\Kernel\Utils;

use PhpHunter\Kernel\Controllers\HunterCatcherController;

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
     * @return bool
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
     * @return void
     */
    public static function checkReadableFile(string $file = ""): void
    {
        if (!is_readable($file) || !is_file($file) || !file_exists($file)) {
            HunterCatcherController::hunterCatcher("Unable to read file: {$file}", 500, true);
        }
    }

}
