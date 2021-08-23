<?php

namespace PhpHunter\Controllers;

use PhpHunter\Abstractions\StatusCodeAbstract;

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
        echo $data;
        exit();
    }

    private function setHeaders()
    {
        header("HTTP/1.1 " . $this->code . " " . $this->getStatusMessage($this->code));
        header("Content-Type:" . $this->contentType);
    }

}
