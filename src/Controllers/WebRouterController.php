<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Abstractions\ParametersAbstract;

class WebRouterController extends ParametersAbstract
{
    private string $savePath;
    private string $pathTo;
    private string $absLocation;
    private string $relLocation;
    private string $extLocation;
    private string $setLocation;

    /**
     * @description Type of files accepted to webserver Apache/Nginx
    */
    private const _WEB_FILES_ = ['php', 'html', 'htm', 'phtml'];

    /**
     * @description Class Constructor
     */
    public function __construct()
    {
        parent::setParams();
    }

    /**
     * @description Class Destructor
     */
    public function __destruct()
    {
    }

    /**
     * @description Web Set Absolute Location
     * @param string $path_to #Mandatory
     * @return object
     */
    public function setAbsoluteLocation(string $path_to = "/"): object
    {
        if (preg_match('/^http([s]?):\/\//', $path_to, $m, PREG_OFFSET_CAPTURE)) {

            $error = "setAbsoluteLocation Error, this is not an absolute path !";
            DumperController::dumpError($error);
        }

        $this->savePath = $path_to;
        $this->pathTo = preg_replace('/^\/$/', '', $path_to);
        $this->setLocation = $this->schemeProtocol."://".$this->serverName.$this->pathTo;
        $this->absLocation = $this->setLocation;
        $this->extLocation = "";
        return $this;
    }

    /**
     * @description Web Get Absolute Location
     * @return string
     */
    public function getAbsoluteLocation(): string
    {
        return $this->absLocation;
    }

    /**
     * @description Web Set Relative Location
     * @param string $path_to #Mandatory
     * @return object
     */
    public function setRelativeLocation(string $path_to = "/"): object
    {
        if (preg_match('/^http([s]?):\/\//', $path_to, $m, PREG_OFFSET_CAPTURE)) {

            $error = "setRelativeLocation Error, this is not an relative path !";
            DumperController::dumpError($error);
        }

        $this->savePath = $path_to;
        $this->pathTo = preg_replace('/^\/$/', '', $path_to);
        $this->setLocation = $this->relLocation = $this->pathTo;
        $this->extLocation = "";
        return $this;
    }

    /**
     * @description Web Get Relative Location
     * @return string
     */
    public function getRelativeLocation(): string
    {
        return $this->relLocation;
    }

    /**
     * @description Web Set Absolute Location
     * @param string $url #Mandatory
     * @return object
     */
    public function setExternalLocation(string $url): object
    {
        if (!preg_match('/^http([s]?):\/\//', $url, $m, PREG_OFFSET_CAPTURE)) {

            $error = "setExternalLocation Error, this is not an external URL !";
            DumperController::dumpError($error);
        }

        $this->setLocation = "";
        $this->extLocation = $url;
        return $this;
    }

    /**
     * @description Web Get Absolute Location
     * @return string
     */
    public function getExternalLocation(): string
    {
        return $this->extLocation;
    }

    /**
     * @description Web Redirect
     * @return void
     */
    public function redirect(): void
    {
        if ($this->getExternalLocation() == "") {

            $internal_path = $this->rootPath."/".preg_replace('/(^\/)|(\?.*)$/', '', $this->savePath);

            if (is_dir($internal_path)) {

                if (FileTools::checkIfExistsFilesByType($internal_path, 'index', self::_WEB_FILES_) == false) {

                    $error = "redirect Error, not found directory or file resource !";
                    DumperController::dumpError($error);

                }

            } else {
                if (!file_exists($internal_path)) {
                    DumperController::dumpError("redirect Error, not found file resource !");
                }
            }

        } else {
            $this->setLocation = $this->getExternalLocation();
        }

        $this->doRedirect();

    }

    /**
     * @description Web Redirect To
     * @param string $location #Mandatory
     * @return void
     */
    public function redirectTo(string $location = "/"): void
    {
        $this->setLocation = $location;

        if (preg_match('/^http([s]?):\/\//', $location, $m, PREG_OFFSET_CAPTURE)) {
            $this->doRedirect();
        } elseif ($location == "/") {
            $this->setLocation = $this->schemeProtocol."://".$this->serverName;
            $this->doRedirect();
        } else {

            $internal_path = $this->rootPath."/".preg_replace('/(^\/)|(\?.*)$/', '', $location);

            if (is_dir($internal_path)) {

                if (FileTools::checkIfExistsFilesByType($internal_path, 'index', self::_WEB_FILES_) == false) {

                    $error = "redirect Error, not found directory or file resource !";
                    DumperController::dumpError($error);

                }

            } else {
                if (!file_exists($internal_path)) {
                    DumperController::dumpError("redirectTo Error, not found file resource ! " . $internal_path);
                }
            }
        }

        $this->setLocation = $location;
        $this->doRedirect();

    }

    /**
     * @description Do Redirect
     * @return void
     */
    private function doRedirect(): void
    {
        header("HTTP/1.1 200 ".$this->getStatusMessage(200));
        header("Location: {$this->setLocation}");
        exit();
    }

}
