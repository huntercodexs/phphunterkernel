<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Abstractions\StatusCodeAbstract;

class ResponseController extends StatusCodeAbstract
{
    private string $contentType = "application/json; charset=utf-8";
    private string $data = "";
    private int $code = 200;

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
    }

    /**
     * @description Set Content Type
     * @param string $type #Mandatory
     */
    public function setContentType($type)
    {
        $this->contentType = $type;
    }

    /**
     * @description Set Status Code
     * @param int $code #Mandatory
     */
    public function setStatusCode($code)
    {
        $this->code = $code;
    }

    /**
     * @description Json Response
     * @param array $data #Mandatory
     * @param int $status_code #Mandatory
     */
    public function jsonResponse($data, $status_code)
    {
        $this->setStatusCode($status_code);
        $this->setHeaders();
        if (is_array($data)) {
            echo json_encode($data);
        } else {
            echo $data;
        }
        exit();
    }

    /**
     * @description Set Headers
     */
    private function setHeaders()
    {
        header("HTTP/1.1 " . $this->code . " " . $this->getStatusMessage($this->code));
        header("Content-Type:" . $this->contentType);
    }

}
