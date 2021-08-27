<?php

namespace PhpHunter\Kernel\Controllers;

use JetBrains\PhpStorm\NoReturn;

class DumperController
{
    /**
     * @description Class Constructor
     */
    public function __construct()
    {
    }

    /**
     * @description Dump Simple
     * @param string $data #Mandatory
     * @return void
     */
    public static function dump(string $data): void
    {
        var_dump('<pre>', $data, '</pre>');
    }

    /**
     * @description DD Simple
     * @param string $data #Mandatory
     * @return void
     */
    public static function dd(string $data): void
    {
        var_dump('<pre>', $data, '</pre>');
        die;
    }

    /**
     * @description Dump Error
     * @param string $data #Mandatory
     * @return void
     */
    public static function dumpError(string $data): void
    {
        if (is_array($data)) {
            var_dump('<pre>', $data, '</pre>');
        } else {
            echo $data;
        }
        die;
    }

    /**
     * @description Smart Dump
     * @param string $data #Mandatory
     * @param bool $die #Optional
     * @return void
     */
    public static function smartDumper(string $data, bool $die = false): void
    {
        echo "smartDumper say: {$data}<br />";
        if ($die) die;
    }

}
