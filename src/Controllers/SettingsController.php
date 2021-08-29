<?php

/**
 * DO NOT CHANGES THIS FILE !
*/

namespace PhpHunter\Kernel\Controllers;

class SettingsController
{
    private string $rootDir;
    private string $namespaceSettings;
    private string $envSettings;
    private string $appSettings;
    private string $apiSettings;
    private string $modelsSettings;
    private string $viewsSettings;
    private string $controllersSettings;
    private string $servicesSettings;
    private string $templatesSettings;

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
        $this->rootDir = $_SERVER['DOCUMENT_ROOT'];

        /**
         * Conector between Framework and Kernel, do not change this !
         */
        $this->namespaceSettings = "PhpHunter\\Framework\\Settings\\";
        $sulfix_file = "Setting";

        $this->envSettings = "Env{$sulfix_file}";
        $this->appSettings = "App{$sulfix_file}";
        $this->apiSettings = "Api{$sulfix_file}";
        $this->modelsSettings = "Model{$sulfix_file}";
        $this->viewsSettings = "View{$sulfix_file}";
        $this->controllersSettings = "Controller{$sulfix_file}";
        $this->servicesSettings = "Service{$sulfix_file}";
        $this->templatesSettings = "Template{$sulfix_file}";
    }

    /**
     * @description Return Settings
     * @param string $config #Mandatory
     * @return string
     */
    private function returnSettings(string $config): string
    {
        return $this->namespaceSettings.$config."::getConfig";
    }

    /**
     * @description Get Env Settings
     * @return string
     */
    public function getEnvSettings(): string
    {
        return $this->returnSettings($this->envSettings);
    }

    /**
     * @description Get App Settings
     * @return string
     */
    public function getAppSettings(): string
    {
        return $this->returnSettings($this->appSettings);
    }

    /**
     * @description Get Api Settings
     * @return string
     */
    public function getApiSettings(): string
    {
        $cfg_file = "{$this->rootDir}/src/PhpHunter/Settings/{$this->apiSettings}.php";
        if (!file_exists($cfg_file)) {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Missing Settings File '.$this->apiSettings],
                500,
                true
            );
        }
        return $this->returnSettings($this->apiSettings);
    }

    /**
     * @description Get Models Settings
     * @return string
     */
    public function getModelsSettings(): string
    {
        return $this->returnSettings($this->modelsSettings);
    }

    /**
     * @description Get Views Settings
     * @return string
     */
    public function getViewsSettings(): string
    {
        return $this->returnSettings($this->viewsSettings);
    }

    /**
     * @description Get Controllers Settings
     * @return string
     */
    public function getControllersSettings(): string
    {
        return $this->returnSettings($this->controllersSettings);
    }

    /**
     * @description Get Services Settings
     * @return string
     */
    public function getServicesSettings(): string
    {
        $cfg_file = "{$this->rootDir}/src/PhpHunter/Settings/{$this->servicesSettings}.php";
        if (!file_exists($cfg_file)) {
            HunterCatcherController::hunterApiCatcher(
                ['error' => 'Missing Settings File '.$this->servicesSettings],
                500,
                true
            );
        }
        return $this->returnSettings($this->servicesSettings);
    }

    /**
     * @description Get Templates Settings
     * @return string
     */
    public function getTemplatesSettings(): string
    {
        return $this->returnSettings($this->templatesSettings);
    }

}
