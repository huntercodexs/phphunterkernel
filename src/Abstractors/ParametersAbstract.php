<?php

namespace PhpHunter\Abstractions;

abstract class ParametersAbstract extends StatusCodeAbstract
{
    protected string $rootPath;
    protected string $schemeProtocol;
    protected string $serverName;
    protected string $requestMethod;
    protected string $requestUri;
    protected array $requestHeaders;
    protected array $requestParams;

    protected string $id;
    protected string $email;
    protected string $name;
    protected string $createAt;
    protected string $updatedAt;
    protected string $action;
    protected string $task;
    protected string $title;
    protected string $completed;
    protected string $token;
    protected string $state;

    protected function setParams()
    {
        $this->requestHeaders = apache_request_headers();
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->rootPath = $_SERVER['DOCUMENT_ROOT'];
        $this->schemeProtocol = $_SERVER['REQUEST_SCHEME'];
        $this->serverName = $_SERVER['SERVER_NAME'];
        $this->requestParams = $_REQUEST;

        $this->id = $this->requestParams['id'] ?? "";
        $this->email = $this->requestParams['email'] ?? "";
        $this->name = $this->requestParams['name'] ?? "";
        $this->createAt = $this->requestParams['create_at'] ?? "";
        $this->updatedAt = $this->requestParams['updated_at'] ?? "";
        $this->action = $this->requestParams['action'] ?? "";
        $this->task = $this->requestParams['task'] ?? "";
        $this->title = $this->requestParams['title'] ?? "";
        $this->completed = $this->requestParams['completed'] ?? "";
        $this->token = $this->requestParams['token'] ?? "";
        $this->token = $this->requestParams['state'] ?? "";

        $this->requestParams = array_merge($this->requestParams, [
            "requestMethod" => $this->requestMethod,
            "requestMethod" => $this->requestMethod,
            "requestUri" => $this->requestUri,
            "rootPath" => $this->rootPath,
            "schemeProtocol" => $this->schemeProtocol,
            "serverName" => $this->serverName
        ]);
    }

    protected function headerParams(): array
    {
        return $this->requestHeaders;
    }

    protected function param($param): string
    {
        return $this->requestParams[$param] ?? "";
    }

    protected function getParams(): array
    {
        return $this->requestParams;
    }

    protected function getMethodRequest(): string
    {
        return $this->requestMethod;
    }

    protected function getRequestUri(): string
    {
        return $this->requestUri;
    }

    protected function getRootPath(): string
    {
        return $this->rootPath;
    }

    protected function getRequestParams():array
    {
        return $this->requestParams;
    }

    protected function getAction():string
    {
        return $this->action;
    }

    protected function getTask():string
    {
        return $this->task;
    }

    protected function getTitle():string
    {
        return $this->title;
    }

    protected function getCompleted():string
    {
        return $this->completed;
    }

    protected function getToken():string
    {
        return $this->token;
    }
}
