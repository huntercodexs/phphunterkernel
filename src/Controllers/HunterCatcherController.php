<?php

namespace PhpHunter\Kernel\Controllers;

use Exception;
use Throwable;

class HunterCatcherController extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @description Hunter Api Catcher
     * @param array $data #Mandatory
     * @param int $code #Mandatory
     * @param bool $die #Optional
     * @return void
     */
    public static function hunterApiCatcher(array $data, int $code = 500, bool $die = false): void
    {
        $env_config = new SettingsController();
        $appConfig = $env_config->getEnvSettings();

        $response = new ResponseController();

        if ($appConfig()['debug'] == true) {
            $response->jsonResponse($data, $code);
        } else {
            $response->jsonResponse([], $code);
        }
        if ($die) die;
    }

    /**
     * @description Hunter Catcher
     * @param string $data #Mandatory
     * @param int $code #Mandatory
     * @param bool $die #Optional
     * @return void
     */
    public static function hunterCatcher(string $data, int $code = 500, bool $die = false): void
    {
        HunterCatcherController::treatCatcher($data, $code);
        if ($die) die;
    }

    /**
     * @description Extract Trace
     * @param array $data #Mandatory
     * @return array
     */
    protected static function extractTrace(array $data = []): array
    {
        if (count($data) == 0) {
            $data = debug_backtrace();
        }

        $trace = [];
        for ($k = 0; $k < count($data); $k++) {
            $trace["file"] = $data[$k]['file'];
            $trace["line"] = $data[$k]['line'];
            $trace["function"] = $data[$k]['function'];
            $trace["class"] = $data[$k]['class'];
            $trace["type"] = $data[$k]['type'];
        }
        return array_merge($data, $trace);
    }

    /**
     * @description Treat Catcher
     * @param string $data #Mandatory
     * @param int $code #Mandatory
     * @return void
     */
    private static function treatCatcher(string $data, int $code): void
    {
        $css = './vendor/huntercodexs/phphunterkernel/src/Assets/css/phphunterkernel.css';
        $html = './vendor/huntercodexs/phphunterkernel/src/Assets/templates/catcher.hunter.html';

        header("Link: <{$css}>; rel=stylesheet; as=file", false);

        $fo = fopen($html, "r");
        while (!feof($fo)) {
            $render_line = fgets($fo, 4096);

            if (preg_match('/{{{!default_message!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                echo str_replace('{{{!default_message!}}}', 'An error occurred while processing the information', $render_line);
            } elseif (preg_match('/{{{!error_code!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                echo str_replace('{{{!error_code!}}}', $code, $render_line);
            } elseif (preg_match('/{{{!tracer!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                echo "<pre>";
                debug_print_backtrace();
                echo "</pre>";
                echo "<br />";
            } elseif (preg_match('/{{{!location!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                echo get_called_class()."<br />";
            } elseif (preg_match('/{{{!details!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                $extract_trace = HunterCatcherController::extractTrace();
                echo "<p>
                    An error occurred in the file: <strong>{$extract_trace['file']}</strong>, line: ${extract_trace['line']}<br />
                    during call was made to the file:  <strong>{$extract_trace[0]['file']}</strong>, line {$extract_trace[0]['line']}<br />
                    where the class: <strong>{$extract_trace['class']}</strong><br />
                    has a failure in the method: <strong>{$extract_trace['function']}</strong><br />
                    <span>
                        and as resulting in the following message: <strong>{$data}</strong><br />
                    </span></p><br />";

                echo "<strong>";
                debug_print_backtrace();
                echo "</strong>";

            } elseif (preg_match('/{{{!message!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                echo $data."<br />";
            } elseif (preg_match('/{{{!structure!}}}/', $render_line, $t, PREG_OFFSET_CAPTURE)) {
                var_dump('<pre>', debug_backtrace(), '</pre>') . "<br />";
            } else {
                echo $render_line;
            }
        }
        fclose($fo);
    }
}
