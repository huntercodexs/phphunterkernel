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
    protected string $serviceNamespace;

    /**
     * @description When Middleware is set
     */
    protected string $middlewareClass;
    protected string $methodMiddleware;
    protected string $staticMiddleware;

    /**
     * @description When Controller or Service is set
     */
    protected string $classServiceOrController;
    protected string $staticServiceOrController;
    protected string $noStaticMethod;
    protected string $staticClass;
    protected string $staticMethod;

    /**
     * @description Define a mandatory method that service or controller should be has
    */
    protected string $firstApply = "setParams";

    /**
     * @description When Route is a service
     */
    protected bool $isRouteService = false;

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
     * @description Set Mandatory Method
     * @return void
     */
    public function setFirstApply(string $method): void
    {
        $this->firstApply = $method;
    }

    /**
     * @description Configuration Setup
     * @return void
     */
    public function configurationSetup(): void
    {
        $app_config = new SettingsController();
        $appConfig = $app_config->getAppSettings();

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

        if (isset($appConfig()['namespace']['services']) && $appConfig()['namespace']['services'] != "") {
            $this->serviceNamespace = $appConfig()['namespace']['services'];
        } else {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Configuration Error to PhpHunterApiPlug:services'],
                500,
                true
            );
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
        $this->classServiceOrController = "";
        $this->staticClass = "";
        $this->staticMethod = "";
        $this->staticServiceOrController = "";
        $this->routeFound = false;
        $this->isRouteService = false;
        $this->controllerArgsFromUri = [];
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
     * @description API Check Request Method
     * @param string $method #Mandatory
     * @return bool
     */
    protected function checkRequestMethod(string $method): bool
    {
        //TODO: Check code block bellow (if is needed or not)
        /*if ($this->requestMethod != $method) {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "HTTP Method Not Allowed !"],
                405,
                true
            );
        }*/
        if ($this->requestMethod == $method) {
            return true;
        }
        return false;
    }

    /**
     * @description Check Request Type
     * @return void
     */
    private function checkRequestType(): void
    {
        $api_config = new SettingsController();
        $apiConfig = $api_config->getApiSettings();
        $accepted_content = $apiConfig()['accepted_content'];

        if (!in_array($this->contentType, $accepted_content[strtoupper($this->requestMethod)])) {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "HTTP-Method/Content-Type Not Allowed !"],
                405,
                true
            );
        }
    }

    /**
     * @description Is Service
     * @param string $route #Mandatory
     * @param string $type #Optional
     * @return bool
     */
    private function isService(string $route, string $type = ""): bool
    {
        if (preg_match("/^\/api\/service\/{$type}/", $route, $m, PREG_OFFSET_CAPTURE)) {
            $this->isRouteService = true;
            return true;
        }
        $this->isRouteService = false;
        return false;
    }

    /**
     * @description Is Service
     * @return bool
     */
    private function isFileSend(): bool
    {
        if (count($this->files) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @description Check Send File
     * @return void
     */
    private function checkSendFile(): void
    {
        if (!$this->isFileSend() || $this->requestMethod != "POST" || $this->contentType != "multipart/form-data") {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "File Service Not Acceptable !"],
                406,
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
        if (!$route || $route == null) {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "Missing Configuration Route"],
                500,
                true
            );
        }
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
     * @description API Route Matcher
     * @param string $verb #Http-Method/Mandatory
     * @param string $route #RoutePath/Mandatory
     * @return void
     */
    protected function routeMatcher(string $verb, string $route): void
    {
        $this->routeFound = false;
        $uri = $this->getRequestUri();
        $route_reg_exp = $this->regExpConvert($route);

        //Route Found
        if (preg_match("/^{$route_reg_exp}$/", $uri, $m, PREG_OFFSET_CAPTURE)) {

            //Cconfirm Http-Method
            if ($this->checkRequestMethod($verb)) {

                /**
                 * When Service is a File-Service check if file send is OK
                */
                if ($this->isService($route, "file")) {
                    $this->checkSendFile();
                }

                /**
                 * Again, if is a route to Service, just check it and set isRouteService = true
                */
                $this->isService($route);

                /**
                 * Now check all values and parameters of the Request
                */
                $this->checkRequestType();
                $this->paramsMapper();
                $this->setControllerArgsFromUri($m, $route);
                $this->setRouterDetails($verb, $route);
                $this->routeFound = true;

            }
        }
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
        $service_or_controller = "";

        if ($callback1 != "" && $callback2 != "") {
            /*Middleware & (Service Or Controller)*/
            $middleware = $callback1;
            $service_or_controller = $callback2;
        } elseif ($callback1 != "" && $callback2 == "") {
            /*Only Service Or Controller*/
            $service_or_controller = $callback1;
        } else {
            HunterCatcherController::hunterApiCatcher(
                ["error" => "Missing Middleware/Service/Controller !"],
                500,
                true
            );
        }

        /*Middleware is set*/
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

        /*Service Or Controller is set (to instanceOf callback [@])*/
        if (preg_match('/@/', $service_or_controller, $m)) {
            $exp1 = explode('@', $service_or_controller);
            if ($this->isRouteService) {
                $this->classServiceOrController = $this->serviceNamespace . $exp1[0];
            } else {
                $this->classServiceOrController = $this->controllerNamespace . $exp1[0];
            }
            $this->noStaticMethod = $exp1[1];
        }

        /*Service Or Controller is set (to Static callback [::])*/
        if (preg_match('/::/', $service_or_controller, $m)) {
            $exp = explode("::", $service_or_controller);
            $this->staticClass = $exp[0];
            $this->staticMethod = $exp[1];
            if ($this->isRouteService) {
                $this->staticServiceOrController = $this->serviceNamespace . $this->staticClass;
            } else {
                $this->staticServiceOrController = $this->controllerNamespace . $this->staticClass;
            }
        }
    }

    /**
     * @description API Run
     * @return void
     */
    protected function run(): void
    {
        if ($this->routeFound) {

            /**
             * Middleware
             */

            if ($this->middlewareClass != "") {
                $instanceOfMiddleware = new $this->middlewareClass();
                $instanceOfMiddleware->{$this->methodMiddleware}();
            } elseif ($this->staticMiddleware != "") {
                "{$this->staticMiddleware}"();
            }

            /*MergeParams - to Static and InstanceOf Services Or Controllers*/
            $params_merge = array_merge($this->controllerArgsFromUri, $this->initParams, $this->files);

            /**
             * Service Or Controller
            */

            if ($this->classServiceOrController != "") {

                /*InstanceOf: Service Or Controller*/

                $instanceOfServiceOrController = new $this->classServiceOrController();

                /**
                 * Has {firstApply} in Controller - this is mandatory !
                 * This value can be replaced by $app->setFirstApply('newMethodName')
                 * in the InstanceOf Class, the default value is setParams
                 */
                if ($this->firstApply != "") {
                    if (!method_exists($instanceOfServiceOrController, $this->firstApply)) {
                        HunterCatcherController::hunterApiCatcher(
                            ["error" => "Missing {$this->firstApply} in {$this->classServiceOrController}"],
                            500,
                            true
                        );
                    }

                    /*firstApply: Mandatory for all Services Or Controllers*/
                    $instanceOfServiceOrController->{$this->firstApply}($params_merge);
                }

                /*Has method in Controller - this is mandatory*/
                if (!method_exists($instanceOfServiceOrController, $this->noStaticMethod)) {
                    HunterCatcherController::hunterApiCatcher(
                        ["error" => "Missing {$this->noStaticMethod} in {$this->classServiceOrController}"],
                        500,
                        true
                    );
                }

                /*Controller Method Call*/
                $instanceOfServiceOrController->{$this->noStaticMethod}($params_merge);

            } elseif ($this->staticServiceOrController != "") {

                /*Static: Service Or Controller*/

                /*Has static method in Controller - this is mandatory*/
                if (!method_exists($this->staticServiceOrController, $this->staticMethod)) {
                    HunterCatcherController::hunterApiCatcher(
                        ["error" => "Missing {$this->staticMethod} in {$this->staticServiceOrController}"],
                        500,
                        true
                    );
                }

                //Example: ControllerName::methodName(params=[]);
                "{$this->staticServiceOrController}::{$this->staticMethod}"($params_merge);
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
