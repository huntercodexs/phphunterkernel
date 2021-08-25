<?php

namespace PhpHunter\Controllers;

use PhpHunter\Abstractions\RequestAbstract;
use PhpHunter\Configuration\PhpHunterSetup;

class ApiRouterController extends RequestAbstract
{
    /**
     * @description Namespace
    */
    private string $middlewareNamespace;
    private string $controllerNamespace;

    /**
     * @description When Middleware is set
     */
    private string $middlewareClass;
    private string $methodMiddleware;
    private string $staticMiddleware;

    /**
     * @description When Controller is set
     */
    private string $controllerClass;
    private string $methodController;
    private string $staticController;

    /**
     * @description Route Found
    */
    private bool $routeFound = false;

    /**
     * @description Route Details
    */
    private string $routeRoute;
    private string $routeUri;
    private string $routeVerb;

    /**
     * @description Controller Arguments
     */
    private string $controllerArgs = "";

    /**
     * @description Constructor Class
     * @throws HunterCatcherController
     */
    public function __construct()
    {
        $this->configurationSetup();
        $this->resetSettings();
        $this->setParams();
    }

    /**
     * @description Destructor Class
     */
    public function __destruct()
    {
        $this->resetSettings();
    }

    /**
     * @description API Reset Settings
     * @throws HunterCatcherController
     */
    private function configurationSetup(): void
    {
        $app_config = new SetupController();
        if (!file_exists($app_config->getAppConfigurationSetup())) {
            throw new HunterCatcherController(
                'Missing Configuration File PhpHunter Setup',
                500
            );
        }

        require_once $app_config->getAppConfigurationSetup();

        //TODO: Melhorar a lógica do trecho de código abaixo
        try {
            $this->middlewareNamespace = PhpHunterSetup::getConfig()['namespace']['middlewares'];
            $this->controllerNamespace = PhpHunterSetup::getConfig()['namespace']['controllers'];
        } catch (\Exception $e) {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Configuration Error to PhpHunter Setup, '.$e->getMessage()],
                500,
                true
            );
        }
    }

    /**
     * @description API Convert RegExp
     * @param string $data #RoutePath/Mandatory
     */
    private function regExpConvert(string $data): string
    {
        $convert = str_replace('/', '\/', $data);
        $convert = preg_replace('/{(.*):number}/', '([0-9]+)', $convert);
        $convert = preg_replace('/{(.*):string}/', '([0-9a-zA-Z]+)', $convert);
        $convert = preg_replace('/{(.*):alpha}/', '([0-9a-zA-Z_]+)', $convert);
        $convert = preg_replace('/{(.*):alpha2}/', '([0-9a-zA-Z_\-]+)', $convert);
        $convert = preg_replace('/{(.*):email}/', '([a-zA-Z0-9\.\_\-]+@[a-zA-Z0-9\.\_\-]+.[a-zA-Z]{2,4})', $convert);
        return preg_replace('/{(.*):symbol}/', '([^0-9a-zA-Z]+)', $convert);
    }

    /**
     * @description API Controller Arguments
     */
    private function setControllerArgs(array $args): void
    {
        $controllerArgs = [];
        /*Has parameters*/
        if (count($args) > 1) {
            for ($p = 1; $p < count($args); $p++) {
                $controllerArgs[] = $args[$p][0];
            }
            $this->controllerArgs = implode(",", $controllerArgs);
        }
    }

    /**
     * @description API Router Details
     */
    private function setRouterDetails($verb, $route): void
    {
        $this->routeVerb = $verb;
        $this->routeRoute = $route;
        $this->routeUri = $this->getRequestUri();
    }

    /**
     * @description API Get Router Details
     */
    private function getRouterDetails(): array
    {
        return [
            "HTTP-METHOD" => $this->routeVerb,
            "ROUTE" => $this->routeRoute,
            "URI" => $this->routeUri
        ];
    }

    /**
     * @description API Show Router Details
     */
    public function showRouterDetails(): object
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
     */
    private function routeMatcher(string $verb, string $route): void
    {
        $route_reg_exp = $this->regExpConvert($route);
        $uri = $this->getRequestUri();

        if (!preg_match("/^{$route_reg_exp}$/", $uri, $m, PREG_OFFSET_CAPTURE)) {
            $this->routeFound = false;
        } else {
            $this->checkRequestMethod($verb);
            $this->routeFound = true;
            $this->setControllerArgs($m);
            $this->setRouterDetails($verb, $route);
        }
    }

    /**
     * @description API Reset Settings
     */
    private function resetSettings(): void
    {
        $this->middlewareClass = "";
        $this->staticMiddleware = "";
        $this->controllerClass = "";
        $this->staticController= "";
        $this->routeFound = false;
        $this->controllerArgs = "";
    }

    /**
     * @description API Setup Run
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
     */
    private function runSetup(string $callback1 = "", string $callback2 = "")
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
            $this->staticController = $this->controllerNamespace . $controller;
        }
    }

    /**
     * @description API Run
    */
    public function run(): void
    {
        if ($this->routeFound) {

            /*Middleware*/
            if ($this->middlewareClass != "") {
                $instanceOfMiddleware = new $this->middlewareClass();
                $instanceOfMiddleware->{$this->methodMiddleware}();
            } elseif ($this->staticMiddleware != "") {
                "{$this->staticMiddleware}"();
            }

            /*Controller*/
            if ($this->controllerClass != "") {
                $instanceOfController = new $this->controllerClass();
                $instanceOfController->{$this->methodController}($this->controllerArgs);
            } elseif ($this->staticController != "") {
                "{$this->staticController}"($this->controllerArgs);
            }

            exit();
        }

        $this->resetSettings();
    }

    /**
     * @description API Exception
     */
    public function exception(): void
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
     */
    private function routerRunner(string $verb, string $route, string $callback1 = "", string $callback2 = "")
    {
        $this->routeMatcher($verb, $route);
        if ($this->routeFound) {
            $this->runSetup($callback1, $callback2);
        }
    }

    /**
     * @description API Check Request Method
     * @param string $method #Mandatory
     */
    private function checkRequestMethod(string $method)
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
     */
    private function preventWrongRoute(string $route)
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
     * @methods [GET]
     * @param string $route #RoutePath/Mandatory
     * @param string $callback1 #Middleware/Optional
     * @param string $callback2 #Controller/Mandatory
    */
    public function get(string $route = "", string $callback1 = "", string $callback2 = ""): object
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
     */
    public function post(string $route = "", string $callback1 = "", string $callback2 = ""): object
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
     */
    public function put(string $route = "", string $callback1 = "", string $callback2 = ""): object
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
     */
    public function delete(string $route = "", string $callback1 = "", string $callback2 = ""): object
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
     */
    public function patch(string $route = "", string $callback1 = "", string $callback2 = ""): object
    {
        $this->preventWrongRoute($route);
        $this->routerRunner("PATCH", $route, $callback1, $callback2);
        return $this;
    }

}
