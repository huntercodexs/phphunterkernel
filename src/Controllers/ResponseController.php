<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Abstractions\StatusCodeAbstract;

class ResponseController extends StatusCodeAbstract
{
    private string $contentType = "application/json; charset=utf-8";
    private string $data = "";
    private int $code = 200;

    public function __construct()
    {
    }

    public function setStatusCode($code)
    {
        $this->code = $code;
    }

    public function jsonResponse($data)
    {
        $this->setHeaders();
        if (is_array($data)) {
            echo json_encode($data);
        } else {
            echo $data;
        }
        exit();
    }

    private function setHeaders()
    {
        header("HTTP/1.1 " . $this->code . " " . $this->getStatusMessage($this->code));
        header("Content-Type:" . $this->contentType);
    }

}
