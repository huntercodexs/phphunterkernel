<?php

namespace PhpHunter\Controllers;

class InitServerController
{
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

    private function devNull()
    {
        /*Nothing here*/
    }

    private function allowErrors()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }

    private function allowCors()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
    }

    private function allowMemory()
    {
        ini_set("memory_limit",-1);
        ini_set('max_execution_time', 0);
    }
}

