<?php

namespace PhpHunter\Kernel\Controllers;

class InitServerController
{
    /**
     * @description Constructor Class
    */
    public function __construct($opts = [])
    {
        if (isset($opts['all']) && $opts['all'] == true) {
            $this->allowErrors();
            $this->allowCors();
            $this->allowMemory();
        } else {
            (isset($opts['error']) && $opts['error'] == true) ? $this->allowErrors() : $this->devNull();
            (isset($opts['cors']) && $opts['cors'] == true) ? $this->allowCors() : $this->devNull();
            (isset($opts['memory']) && $opts['memory'] == true) ? $this->allowMemory() : $this->devNull();
        }
    }

    /**
     * @description Dev Null
     * @return void
     */
    private function devNull(): void
    {
        /*Nothing here*/
    }

    /**
     * @description Allow Errors
     * @return void
     */
    private function allowErrors(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }

    /**
     * @description Allow Cors
     * @return void
     */
    private function allowCors(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
    }

    /**
     * @description Allow Memory
     * @return void
     */
    private function allowMemory(): void
    {
        ini_set("memory_limit",-1);
        ini_set('max_execution_time', 0);
    }
}

