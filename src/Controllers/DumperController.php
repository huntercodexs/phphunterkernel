<?php

namespace PhpHunter\Controllers;

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
     */
    public static function dump(string $data)
    {
        var_dump('<pre>', $data, '</pre>');
    }

    /**
     * @description DD Simple
     * @param string $data #Mandatory
     */
    public static function dd(string $data)
    {
        var_dump('<pre>', $data, '</pre>');
        die;
    }

    /**
     * @description Dump Error
     * @param string $data #Mandatory
     */
    public static function dumpError(string|array $data)
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
     */
    public static function smartDumper(string $data, bool $die = false)
    {
        echo "<pre>";
        echo get_called_class()."<br />";
        var_dump(debug_print_backtrace())."<br />";
        var_dump(debug_backtrace())."<br />";
        echo "smartDumper say: {$data}"."<br />";
        echo "</pre>";
        if ($die) die;
    }
}
