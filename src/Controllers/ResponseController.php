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
     * @return void
     */
    public function setContentType(string $type): void
    {
        $this->contentType = $type;
    }

    /**
     * @description Set Status Code
     * @param int $code #Mandatory
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @description Json Response
     * @param array|string $data #Mandatory
     * @param int $status_code #Mandatory
     * @return void
     */
    public function jsonResponse(array|string $data, int $status_code): void
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
     * @return void
     */
    private function setHeaders(): void
    {
        header("HTTP/1.1 " . $this->code . " " . $this->getStatusMessage($this->code));
        header("Content-Type:" . $this->contentType);
    }

}
