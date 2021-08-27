<?php

namespace PhpHunter\Kernel\Abstractions;

abstract class RequestAbstract extends ParametersAbstract
{
    public function headerParams(): array
    {
        return parent::headerParams();
    }

    public function getParams(): array
    {
        return parent::getParams();
    }

    public function getRequestParams(): array
    {
        return parent::getRequestParams();
    }

    public function getMethodRequest(): string
    {
        return parent::getMethodRequest();
    }

    public function getAction(): string
    {
        return parent::getAction();
    }

    public function getTask(): string
    {
        return parent::getTask();
    }

    public function getTitle(): string
    {
        return parent::getTitle();
    }

    public function getCompleted(): string
    {
        return parent::getCompleted();
    }

    public function getToken(): string
    {
        return parent::getToken();
    }
}
