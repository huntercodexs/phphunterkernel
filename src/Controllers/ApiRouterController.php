<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Abstractions\ParametersAbstract;

class ApiRouterController extends ParametersAbstract
{
    /**
     * @description Namespace
    */
    protected string $middlewareNamespace;
    protected string $controllerNamespace;

    /**
     * @description When Middleware is set
     */
    protected string $middlewareClass;
    protected string $methodMiddleware;
    protected string $staticMiddleware;

    /**
     * @description When Controller is set
     */
    protected string $controllerClass;
    protected string $methodController;
    protected string $staticController;
    protected string $staticClass;
    protected string $staticMethod;

    /**
     * @description Route Found
    */
    protected bool $routeFound = false;

    /**
     * @description Route Details
    */
    protected string $routeRoute;
    protected string $routeUri;
    protected string $routeVerb;

    /**
     * @description Controller Arguments
     */
    protected array $controllerArgsFromUri = [];

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
        $this->configurationSetup();
        $this->resetSettings();
        $this->initParams();
    }

    /**
     * @description Destructor Class
     */
    public function __destruct()
    {
        $this->resetSettings();
    }

    /**
     * @description API Convert RegExp
     * @param string $data #Route-Path/Mandatory
     * @return string
     */
    protected function regExpConvert(string $data): string
    {
        $convert = str_replace('/', '\/', $data);
        $convert = preg_replace('/{(.*):number}/', '([0-9]+)', $convert);
        $convert = preg_replace('/{(.*):string}/', '([0-9a-zA-Z]+)', $convert);
        $convert = preg_replace('/{(.*):alpha}/', '([0-9a-zA-Z_]+)', $convert);
        $convert = preg_replace('/{(.*):alpha2}/', '([0-9a-zA-Z_\-]+)', $convert);
        $convert = preg_replace('/{(.*):email}/', '([a-zA-Z0-9\.\_\-]+@[a-zA-Z0-9\.\_\-]+.[a-zA-Z]{2,4})', $convert);
        $convert = preg_replace('/{(.*):query_string}/', '(\?.*)', $convert);
        return preg_replace('/{(.*):symbol}/', '([^0-9a-zA-Z]+)', $convert);
    }

    /**
     * @description API Controller Arguments
     * @param array $args #Args/Mandatory
     * @param string $route #Route-Path/Mandatory
     * @return void
     */
    protected function setControllerArgsFromUri(array $args, string $route): void
    {
        $pattern = '/[{]([0-9a-zA-Z_]+):(number|string|alpha[2]?|email|query_string|symbol)[}]/';
        preg_match_all($pattern, $route, $args_name_from_route, PREG_OFFSET_CAPTURE);

        $args_from_uri = [];

        /*Has parameters*/
        if (count($args) > 1) {
            for ($p = 1; $p < count($args); $p++) {
                $args_from_uri[$args_name_from_route[1][$p-1][0]] = $args[$p][0];
            }
            $this->controllerArgsFromUri = $args_from_uri;
        }
    }

    /**
     * @description API Set Router Details
     * @param string $verb #Http-Method|Mandatory
     * @param string $route #Route-Path|Mandatory
     * @return void
     */
    protected function setRouterDetails(string $verb, string $route): void
    {
        $this->routeVerb = $verb;
        $this->routeRoute = $route;
        $this->routeUri = $this->getRequestUri();
    }

    /**
     * @description API Get Router Details
     * @return array
     */
    protected function getRouterDetails(): array
    {
        return [
            "HTTP-METHOD" => $this->routeVerb,
            "ROUTE" => $this->routeRoute,
            "URI" => $this->routeUri
        ];
    }

    /**
     * @description API Show Router Details
     * @return object
     */
    protected function showRouterDetails(): object
    {
        DumperController::dump((string)[
            "HTTP-METHOD" => $this->routeVerb,
            "ROUTE" => $this->routeRoute,
            "URI" => $this->routeUri
        ]);

        return $this;
    }

    /**
     * @description API Route Matcher
     * @param string $verb #Http-Method/Mandatory
     * @param string $route #RoutePath/Mandatory
     * @return void
     */
    protected function routeMatcher(string $verb, string $route): void
    {
        $route_reg_exp = $this->regExpConvert($route);
        $uri = $this->getRequestUri();

        if (!preg_match("/^{$route_reg_exp}$/", $uri, $m, PREG_OFFSET_CAPTURE)) {
            $this->routeFound = false;
        } else {
            $this->checkRequestType();
            $this->checkRequestMethod($verb);
            $this->setControllerArgsFromUri($m, $route);
            $this->setRouterDetails($verb, $route);
            $this->routeFound = true;
        }
    }

    /**
     * @description API Reset Settings
     * @return void
     */
    protected function resetSettings(): void
    {
        $this->middlewareClass = "";
        $this->staticMiddleware = "";
        $this->controllerClass = "";
        $this->staticClass = "";
        $this->staticMethod = "";
        $this->staticController = "";
        $this->routeFound = false;
        $this->controllerArgsFromUri = [];
    }

    /**
     * @description API Setup Run
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return void
     */
    protected function runSetup(string $callback1 = "", string $callback2 = ""): void
    {
        $middleware = "";
        $controller = "";

        if ($callback1 != "" && $callback2 != "") {
            /*Middleware & Controller*/
            $middleware = $callback1;
            $controller = $callback2;
        } elseif ($callback1 != "" && $callback2 == "") {
            /*Only Controller*/
            $controller = $callback1;
        } else {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "Missing Middleware/Controller !"],
                500,
                true
            );
        }

        if ($middleware != "") {
            /*Middleware: No static methods*/
            if (preg_match('/@/', $callback1, $m)) {
                $exp1 = explode('@', $callback1);
                $this->middlewareClass = $this->middlewareNamespace . $exp1[0];
                $this->methodMiddleware = $exp1[1];
            }

            /*Middleware: Static methods*/
            if (preg_match('/::/', $callback1, $m)) {
                $this->staticMiddleware = $this->middlewareNamespace . $callback1;
            }
        }

        /*Controller: No static methods*/
        if (preg_match('/@/', $controller, $m)) {
            $exp1 = explode('@', $controller);
            $this->controllerClass = $this->controllerNamespace . $exp1[0];
            $this->methodController = $exp1[1];
        }

        /*Controller: Static methods*/
        if (preg_match('/::/', $controller, $m)) {
            $exp = explode("::", $controller);
            $this->staticClass = $exp[0];
            $this->staticMethod = $exp[1];
            $this->staticController = $this->controllerNamespace . $this->staticClass;
        }
    }

    /**
     * @description API Run
     * @return void
    */
    protected function run(): void
    {
        if ($this->routeFound) {

            /*Middleware*/
            if ($this->middlewareClass != "") {
                $instanceOfMiddleware = new $this->middlewareClass();
                $instanceOfMiddleware->{$this->methodMiddleware}();
            } elseif ($this->staticMiddleware != "") {
                "{$this->staticMiddleware}"();
            }

            /*MergeParams - to Static and Instancef Controllers*/
            $params_merge = array_merge($this->controllerArgsFromUri, $this->initParams);

            /*Controller*/
            if ($this->controllerClass != "") {

                /*InstanceOf*/
                $instanceOfController = new $this->controllerClass();

                /*Has setParams in Controller - this is mandatory*/
                if (!method_exists($instanceOfController, 'setParams')) {
                    HunterCatcherController::hunterApiCatcher(
                        ["error" => "Missing setParams in {$this->controllerClass}"],
                        500,
                        true
                    );
                }
                /*SetParams*/
                $instanceOfController->setParams($params_merge);

                /*Has method in Controller - this is mandatory*/
                if (!method_exists($instanceOfController, $this->methodController)) {
                    HunterCatcherController::hunterApiCatcher(
                        ["error" => "Missing {$this->methodController} in {$this->controllerClass}"],
                        500,
                        true
                    );
                }

                /*Controller Method Call*/
                $instanceOfController->{$this->methodController}();

            } elseif ($this->staticController != "") {
                /*Static*/

                /*Has static method in Controller - this is mandatory*/
                if (!method_exists($this->staticController, $this->staticMethod)) {
                    HunterCatcherController::hunterApiCatcher(
                        ["error" => "Missing {$this->staticMethod} in {$this->staticController}"],
                        500,
                        true
                    );
                }

                //Example: ControllerName::methodName(params=[]);
                "{$this->staticController}::{$this->staticMethod}"($params_merge);
            }

            exit();
        }

        $this->resetSettings();
    }

    /**
     * @description API Exception
     * @return void
     */
    protected function exception(): void
    {
        HunterCatcherController::hunterApiCatcher(
            ["exception" => "Route Not Found !"],
            404,
            true
        );
    }

    /**
     * @description API Router Runner
     * @param string $verb #Http-Method/Mandatory
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return void
     */
    protected function routerRunner(string $verb, string $route, string $callback1 = "", string $callback2 = ""): void
    {
        $this->routeMatcher($verb, $route);
        if ($this->routeFound) {
            $this->runSetup($callback1, $callback2);
        }
    }

    /**
     * @description API Check Request Method
     * @param string $method #Mandatory
     * @return void
     */
    protected function checkRequestMethod(string $method): void
    {
        if ($this->requestMethod != $method) {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "HTTP Method Not Allowed !"],
                405,
                true
            );
        }
    }

    /**
     * @description Prevent Wrong Route
     * @param string $route #Mandatory
     * @return void
     */
    protected function preventWrongRoute(string $route): void
    {
        if (!$route) {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "Missing Configuration Route"],
                500,
                true
            );
        }
    }

    /**
     * @description Configuration Setup
     * @return void
     */
    public function configurationSetup(): void
    {
        $app_config = new SetupController();
        $appConfig = $app_config->getAppConfigurationSetup();

        if (isset($appConfig()['namespace']['middlewares']) && $appConfig()['namespace']['middlewares'] != "") {
            $this->middlewareNamespace = $appConfig()['namespace']['middlewares'];
        } else {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Configuration Error to PhpHunterApiPlug:middlewares'],
                500,
                true
            );
        }

        if (isset($appConfig()['namespace']['controllers']) && $appConfig()['namespace']['controllers'] != "") {
            $this->controllerNamespace = $appConfig()['namespace']['controllers'];
        } else {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Configuration Error to PhpHunterApiPlug:controllers'],
                500,
                true
            );
        }
    }

    /**
     * @description Check Request Type
     * @return void
     */
    private function checkRequestType(): void
    {
        $api_config = new SetupController();
        $apiConfig = $api_config->getApiConfigurationSetup();
        $accepted_content = $apiConfig()['accepted_content'];

        if (!in_array($this->contentType, $accepted_content[strtoupper($this->requestMethod)])) {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "Not Acceptable !"],
                406,
                true
            );
        }
    }

    /**
     * @description Check Send File
     * @param array $resource #Mandatory
     * @return bool
     */
    private function checkSendFile(array $resource): bool
    {
        /*Requisição para enviar arquivo*/
        if ($resource['service'] == "atlas/FileManager" && $resource['action'] == "send") {
            if ($resource['type'] != "POST" || $resource['content'] != "multipart/form-data") {
                return false;
            }
            $check_file = new FileManagerController();
            if (!$check_file->validateFile()) {
                return false;
            }
        }

        return true;
    }
    
    //------------------------------------------------------------------------------------------------
    // REST/HTTP/METHOD
    // GET|POST|PUT|DELETE|PATCH
    //------------------------------------------------------------------------------------------------

    /**
     * @methods [GET]
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return object
    */
    protected function get(string $route = "", string $callback1 = "", string $callback2 = ""): object
    {
        $this->preventWrongRoute($route);
        $this->routerRunner("GET", $route, $callback1, $callback2);
        return $this;
    }

    /**
     * @methods [POST]
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return object
     */
    protected function post(string $route = "", string $callback1 = "", string $callback2 = ""): object
    {
        $this->preventWrongRoute($route);
        $this->routerRunner("POST", $route, $callback1, $callback2);
        return $this;
    }

    /**
     * @methods [PUT]
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return object
     */
    protected function put(string $route = "", string $callback1 = "", string $callback2 = ""): object
    {
        $this->preventWrongRoute($route);
        $this->routerRunner("PUT", $route, $callback1, $callback2);
        return $this;
    }

    /**
     * @methods [DELETE]
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return object
     */
    protected function delete(string $route = "", string $callback1 = "", string $callback2 = ""): object
    {
        $this->preventWrongRoute($route);
        $this->routerRunner("DELETE", $route, $callback1, $callback2);
        return $this;
    }

    /**
     * @methods [PATCH]
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     * @return object
     */
    protected function patch(string $route = "", string $callback1 = "", string $callback2 = ""): object
    {
        $this->preventWrongRoute($route);
        $this->routerRunner("PATCH", $route, $callback1, $callback2);
        return $this;
    }

}
