<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Abstractions\ParametersAbstract;

class DotEnvController extends ParametersAbstract
{
    private string $rootPathProject;
    private string $handlerEnvFile;

    /**
     * @description Constructor Class
     */
    public function __construct(string $location = "")
    {
        parent::setParams();
        $this->rootPathProject = parent::getRootPath();
        $location = preg_replace('/^\/|\/$/', '', $location);
        $this->rootPathProject = preg_replace('/\/$/', '', $this->rootPathProject);
        $this->rootPathProject = $this->rootPathProject."/".$location."/";
        FileTools::checkReadableFile($this->rootPathProject.".env");
    }

    /**
     * @description Check Env Already Exists
     * @param string $env_name #Mandatory
     * @param string $env_value #Mandatory
     * @return bool
     */
    public static function checkEnvAlreadyExists(string $env_name, string $env_value): bool
    {
        if (array_key_exists($env_name, $_ENV)) {
            return true;
        }

        if (array_key_exists($env_name, $_SERVER)) {
            return true;
        }

        if (getenv($env_name) != false) {
            return true;
        }

        /*Overwrite Apache environment variable exists*/
        if (
            function_exists('apache_getenv') &&
            function_exists('apache_setenv') &&
            apache_getenv($env_name))
        {
            apache_setenv($env_name, $env_value);
        }

        /*Put Env - Force*/
        if (function_exists('putenv')) {
            putenv("$env_name=$env_value");
        }

        return false;
    }

    /**
     * @description Load Env Cached
     * @param string $location #Mandatory
     * @return void
     */
    public static function loadEnvCached(string $location): void
    {
        $get_root_path = new DotEnvController($location);

        if (!file_exists($get_root_path->rootPathProject . ".env")) {
            die("Missing: " . $get_root_path->rootPathProject . ".env");
        }

        if (!is_readable($get_root_path->rootPathProject . ".env")) {
            die("Unable to read the file: " . $get_root_path->rootPathProject . ".env");
        }

        $handler = fopen($get_root_path->rootPathProject . ".env", "r");

        while (!feof($handler)) {
            $env_line = fgets($handler, 4096);
            if (preg_match("/^[^#](.*) ?= ?(.*)/", $env_line, $env)) {
                $env_name = trim(preg_replace('/["\']/i', '', explode("=", $env[0])[0]));
                $env_value = trim(preg_replace('/["\']/i', '', explode("=", $env[0])[1]));

                /*No Overwrite*/
                if (self::checkEnvAlreadyExists($env_name, $env_value)) {
                    continue;
                }

                $_ENV[$env_name] = $env_value;
                $_SERVER[$env_name] = $env_value;
                $GLOBALS[$env_name] = $env_value;
            }
        }

        fclose($handler);
    }

    /**
     * @description Get Env Cached
     * @param string $env_name #Mandatory
     * @return string
     */
    public static function getEnvCached(string $env_name): string
    {
        return getenv($env_name);
    }

    /**
     * @description Get Env
     * @param string $location #Mandatory
     * @param string $var_name #Mandatory
     * @return string
     */
    public static function getEnv(string $location, string $var_name): string
    {
        $get_root_path = new DotEnvController($location);

        if (!file_exists($get_root_path->rootPathProject . ".env")) {
            die("Missing: " . $get_root_path->rootPathProject . ".env");
        }

        $env = "";

        $handler = fopen($get_root_path->rootPathProject . ".env", "r");

        while (!feof($handler)) {
            $env_line = fgets($handler, 4096);
            if (preg_match("/^(?!#){$var_name} ?= ?(.*)/", $env_line, $env)) {
                $tmp = preg_replace('/["\']/i', '', explode("=", $env[0])[1]);
                $env = trim($tmp);
                break;
            }
        }

        fclose($handler);

        if (is_array($env)) {
            $env = "";
        }

        return $env;
    }

    /**
     * @description DEBUG Env
     * @param string $location #Mandatory
     * @return void
    */
    public static function debugEnv(string $location): void
    {
        $get_root_path = new DotEnvController($location);

        if (!file_exists($get_root_path->rootPathProject . ".env")) {
            die("Missing: " . $get_root_path->rootPathProject . ".env");
        }

        $handler = fopen($get_root_path->rootPathProject . ".env", "r");

        while (!feof($handler)) {
            $env_line = fgets($handler, 4096);
            if (preg_match("/^(?!#)(.*) ?= ?(.*)/", $env_line, $env)) {
                echo $env_line."<br />".PHP_EOL;
            }
        }

        fclose($handler);
    }
}
