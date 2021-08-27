<?php

namespace PhpHunter\Kernel\Abstractions;

abstract class ParametersAbstract extends StatusCodeAbstract
{
    protected string $rootPath;
    protected string $schemeProtocol;
    protected string $serverName;
    protected string $requestMethod;
    protected string $requestUri;
    protected string $contentType;
    protected array $requestHeaders;
    protected array $requestParams;
    protected array $initParams;
    protected array $params;

    protected string $id;
    protected string $email;
    protected string $name;
    protected string $password;
    protected string $createAt;
    protected string $updatedAt;
    protected string $action;
    protected string $task;
    protected string $title;
    protected string $completed;
    protected string $token;
    protected string $state;
    protected string $schedule;

    //------------------------------------------------------------------------------------------------
    // INIT PARAMS
    //------------------------------------------------------------------------------------------------

    /**
     * @description Init Params
     */
    protected function initParams()
    {
        $this->requestHeaders = apache_request_headers();
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? "";
        $this->rootPath = $_SERVER['DOCUMENT_ROOT'];
        $this->schemeProtocol = $_SERVER['REQUEST_SCHEME'];
        $this->serverName = $_SERVER['SERVER_NAME'];
        $this->requestParams = $_REQUEST;

        $this->id = $this->requestParams['id'] ?? "";
        $this->email = $this->requestParams['email'] ?? "";
        $this->name = $this->requestParams['name'] ?? "";
        $this->password = $this->requestParams['password'] ?? "";
        $this->createAt = $this->requestParams['create_at'] ?? "";
        $this->updatedAt = $this->requestParams['updated_at'] ?? "";
        $this->action = $this->requestParams['action'] ?? "";
        $this->task = $this->requestParams['task'] ?? "";
        $this->title = $this->requestParams['title'] ?? "";
        $this->completed = $this->requestParams['completed'] ?? "";
        $this->token = $this->requestParams['token'] ?? "";
        $this->token = $this->requestParams['state'] ?? "";
        $this->schedule = $this->schedule['schedule'] ?? "";

        $this->requestParams = array_merge($this->requestParams, [
            "requestMethod" => $this->requestMethod,
            "requestMethod" => $this->requestMethod,
            "requestUri" => $this->requestUri,
            "rootPath" => $this->rootPath,
            "schemeProtocol" => $this->schemeProtocol,
            "serverName" => $this->serverName
        ]);

        /**
         * When Content Type is multpart/form-data, then:
         * explode => multipart/form-data; boundary=--------------------------010101010101010101010101
         * explode[0] = multipart/form-data
         */
        $c_type = explode(";", $this->contentType)[0];
        $this->contentType = $c_type ?? $this->contentType;
        
        $this->prepareParameters($this->requestUri);
    }
    
    /**
     * @description Prepare Parameters
     * @param string $uri #Mandatory
     */
    private function prepareParameters(string $uri): array
    {
        $params = [];

        /**
         * Try Get Query String Params from URI
         */
        preg_match('/(\?.*)/', $uri, $source, PREG_OFFSET_CAPTURE);

        if ($_SERVER['CONTENT_LENGTH'] == 0 && count($source) == 0) {
            return $params;
        }

        switch (strtoupper($this->requestMethod)) {

            case "PUT" || "GET" || "POST":
                $params = $this->getParamsByContentType();
                break;

            case "DELETE":

                if ($_SERVER['CONTENT_LENGTH'] != 0) {
                    parse_str(
                        file_get_contents(
                            'php://input',
                            false,
                            null,
                            0,
                            $_SERVER['CONTENT_LENGTH']),
                        $params);
                }
                break;

        }

        if(count($source) > 0) {
            $params = array_merge($params, $this->paramsExtractor($source));
        }

        return $this->initParams = $params;
    }

    /**
     * @description Get Params By Content Type
     */
    private function getParamsByContentType(): array
    {
        $params = [];

        if ($this->requestMethod == "POST") {

            switch ($this->contentType) {

                case "application/json":
                    $params = json_decode(
                        file_get_contents(
                            'php://input',
                            false,
                            null,
                            0,
                            $_SERVER['CONTENT_LENGTH']),
                        true);
                    break;

                default:
                    $params = $_POST;
            }

        } else {

            switch ($this->contentType) {

                case "application/json":
                    $params = json_decode(
                        file_get_contents(
                            'php://input',
                            false,
                            null,
                            0,
                            $_SERVER['CONTENT_LENGTH']),
                        true);
                    break;

                case "application/x-www-form-urlencoded":
                    parse_str(
                        file_get_contents(
                            'php://input',
                            false,
                            null,
                            0,
                            $_SERVER['CONTENT_LENGTH']),
                        $params);
                    break;

                case "multipart/form-data":
                    $params = $this->paramsExtractor($this->multipartFormDataExtractor());
                    break;
            }

        }

        return $params;

    }

    /**
     * @description Params Extractor
     */
    private function paramsExtractor($source): array
    {
        $extracted = [];
        $generic_source = is_array($source) ? $source[0][0] : $source;
        $source_extract = explode("&", preg_replace('/^\?/', '', $generic_source));

        for ($i = 0; $i < count($source_extract); $i++) {

            if ($i == count($source_extract)) {
                break;
            }

            $get_param = explode("=", $source_extract[$i]);

            if ($get_param[0]) {
                $extracted[$get_param[0]] = $get_param[1];
            }
        }

        return $extracted;
    }

    /**
     * @description Multipart Form Data Extractor
     */
    private function multipartFormDataExtractor(): string
    {
        $params = file_get_contents(
            'php://input',
            false,
            null,
            0,
            $_SERVER['CONTENT_LENGTH']);

        $params = preg_replace('/-{28}[0-9]{24}/', '', $params);
        $params = preg_replace('/Content-Disposition: form-data; /', '', $params);
        $params = preg_replace('/\n|\r\n|\r/', ';', $params);
        $params = preg_replace('/name="/', '', $params);
        $params = preg_replace('/";;/', '=', $params);
        $params = preg_replace('/;;/', '&', $params);
        $params = preg_replace('/;|--/', '', $params);

        return $params;
    }

    //------------------------------------------------------------------------------------------------
    // PARAMS
    //------------------------------------------------------------------------------------------------

    /**
     * @description Set Params
     * @param array $params #Mandatory
     */
    protected function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @description Get Params
     */
    protected function getParams(): array
    {
        return $this->params;
    }

    /**
     * @description Set Param
     * @param string $param #Mandatory
     * @param string $value #Mandatory
     */
    protected function setParam(string $param, string $value)
    {
        $this->params[$param] = $value;
    }

    /**
     * @description Get Param
     * @param string $param #Mandatory
     */
    protected function getParam(string $param): string
    {
        return $this->params[$param] ?? "";
    }

    //------------------------------------------------------------------------------------------------
    // EXTRAS/PARAMS
    //------------------------------------------------------------------------------------------------

    protected function headerParams(): array
    {
        return $this->requestHeaders;
    }

    protected function getRequestParam($param): string
    {
        return $this->requestParams[$param] ?? "";
    }

    protected function getRequestParams(): array
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
