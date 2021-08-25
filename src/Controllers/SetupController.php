<?php

namespace PhpHunter\Kernel\Controllers;

class SetupController
{
    private string $appConfigurationSetup;

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
        $this->appConfigurationSetup = $_SERVER['DOCUMENT_ROOT']."/app/Configuration/PhpHunterSetup.php";
    }

    public function getAppConfigurationSetup(): string
    {
        return $this->appConfigurationSetup;
    }

}
