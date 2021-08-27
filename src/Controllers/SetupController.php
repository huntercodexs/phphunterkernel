<?php

/**
 * DO NOT CHANGES THIS FILE !
*/

namespace PhpHunter\Kernel\Controllers;

class SetupController
{
    private string $rootDir;
    private string $namespaceConfiguration;
    private string $envConfigurationSetup;
    private string $appConfigurationSetup;
    private string $apiConfigurationSetup;
    private string $modelsConfigurationSetup;
    private string $viewsConfigurationSetup;
    private string $controllersConfigurationSetup;
    private string $servicesConfigurationSetup;
    private string $templatesConfigurationSetup;

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
        $this->rootDir = $_SERVER['DOCUMENT_ROOT'];

        /**
         * Conector betewen Framework and Kernel, do not change this !
         */
        $this->namespaceConfiguration = "PhpHunter\\Framework\\App\\Configuration\\";

        $this->envConfigurationSetup = "PhpHunterEnvSetup";
        $this->appConfigurationSetup = "PhpHunterAppSetup";
        $this->apiConfigurationSetup = "PhpHunterApiSetup";
        $this->modelsConfigurationSetup = "PhpHunterModelsSetup";
        $this->viewsConfigurationSetup = "PhpHunterViewsSetup";
        $this->controllersConfigurationSetup = "PhpHunterControllersSetup";
        $this->servicesConfigurationSetup = "PhpHunterServicesSetup";
        $this->templatesConfigurationSetup = "PhpHunterTemplatesSetup";
    }

    /**
     * @description Return Configuration
     * @param string $config #Mandatory
     */
    private function returnConfiguration(string $config): string
    {
        return $this->namespaceConfiguration.$config."::getConfig";
    }

    /**
     * @description Get Env Configuration Setup
     */
    public function getEnvConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->envConfigurationSetup);
    }

    /**
     * @description Get App Configuration Setup
     */
    public function getAppConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->appConfigurationSetup);
    }

    /**
     * @description Get Api Configuration Setup
     */
    public function getApiConfigurationSetup(): string
    {
        $cfg_file = "{$this->rootDir}/app/Configuration/{$this->apiConfigurationSetup}.php";
        if (!file_exists($cfg_file)) {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Missing Configuration File '.$this->apiConfigurationSetup],
                500,
                true
            );
        }
        return $this->returnConfiguration($this->apiConfigurationSetup);
    }

    /**
     * @description Get Models Configuration Setup
     */
    public function getModelsConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->modelsConfigurationSetup);
    }

    /**
     * @description Get Views Configuration Setup
     */
    public function getViewsConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->viewsConfigurationSetup);
    }

    /**
     * @description Get Controllers Configuration Setup
     */
    public function getControllersConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->controllersConfigurationSetup);
    }

    /**
     * @description Get Services Configuration Setup
     */
    public function getServicesConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->servicesConfigurationSetup);
    }

    /**
     * @description Get Templates Configuration Setup
     */
    public function getTemplatesConfigurationSetup(): string
    {
        return $this->returnConfiguration($this->templatesConfigurationSetup);
    }

}
