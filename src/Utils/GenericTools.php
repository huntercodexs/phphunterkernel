<?php

namespace PhpHunter\Kernel\Utils;

use PhpHunter\Kernel\Controllers\DumperController;

class GenericTools extends DumperController
{
    /**
     * @description Local Logger
     * @param string $filename #Mandatory
     * @param string $data #Mandatory
     * @param bool $append #Mandatory
     * @return bool
    */
    public static function localLogger(string $filename, string $data, bool $append = true): bool
    {
        $log_dir = $_SERVER['DOCUMENT_ROOT']."/.log/";
        $date = date('YmdH');
        $log_date = date('Y/m/d H:i:s');
        $filepath = $log_dir.$filename."-".$date.".log";

        if (!is_dir($log_dir)) {
            if (!mkdir($log_dir)) {
                DumperController::smartDumper("Error on create dir $log_dir", true);
                return false;
            }

            if (!chmod($log_dir, 0777)) {
                DumperController::smartDumper("Error on change permission to $log_dir", true);
                return false;
            }
        }

        if (!file_exists($filepath)) {

            if (!$fh = fopen($filepath, "w+")) {
                DumperController::smartDumper("Error on create file $filepath", true);
                return false;
            }

            if (!fwrite($fh, 'Create at: '.$date.PHP_EOL.PHP_EOL)) {
                DumperController::smartDumper("Error on write file $filepath", true);
                return false;
            }

            fclose($fh);

            chmod($filepath, 0777);
        }

        switch ($append) {
            case true:
                if (!file_put_contents($filepath, $log_date." : ".print_r($data, true).PHP_EOL, FILE_APPEND)) {
                    DumperController::smartDumper("Error[1] on put contents on file $filepath", true);
                    return false;
                }
                break;
            default:
                if (!file_put_contents($filepath, $log_date." : ".print_r($data, true).PHP_EOL)) {
                    DumperController::smartDumper("Error[2] on put contents on file $filepath", true);
                    return false;
                }
        }

        return true;
    }

    /**
     * @description To Array
     * @param string $str #Mandatory
     * @param string $separator #Optional
     * @return array
     */
    public static function toArray(string $str, string $separator = ","): array
    {
        $a = array();

        if (strstr($str, $separator)) {
            $str = preg_replace('/[\[\]\"\']/i', '', $str);
            $t = explode($separator, $str);
            for ($i = 0; $i < count($t); $i++)
            {
                array_push($a, trim($t[$i]));
            }
        }

        return $a;
    }

}
