<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Abstractions\ParametersAbstract;

class FileManagerController extends ParametersAbstract
{
    private int $chmod;
    private int $acceptedFileSize;
    private string $prefix;
    private string $pathToSave;
    private string $extensionFile;
    private array $acceptedFiles;

    /**
     * @description Constructor Class
    */
    public function __construct()
    {
        $api_service_config = new SettingsController();
        $ApiServiceConfig = $api_service_config->getServicesSettings();
        $upload_config = $ApiServiceConfig()['upload'];

        $this->acceptedFiles = $upload_config['accepted'] ?? "";
        $this->acceptedFileSize = $upload_config['maxsize'] ?? "";
        $this->pathToSave = $upload_config['dir'] ?? "";
        $this->chmod = $upload_config['chmod'] ?? "";
        $this->prefix = $upload_config['prefix'] ?? "";
    }

    /**
     * @description Check Accept Files Extension
     * @return bool
     */
    private function checkAcceptFilesExtension(): bool
    {
        $checkin = explode(".", $this->files['file']['name']);
        $this->extensionFile = strtolower(end($checkin));

        if (!is_array($checkin) || count($checkin) == 0) {
            return false;
        }
        if (!in_array($this->extensionFile, $this->acceptedFiles)) {
            return false;
        }

        return true;
    }

    /**
     * @description Check Accept Files Size
     * @return bool
     */
    private function checkAcceptFilesSize($size): bool
    {
        if ($size > $this->acceptedFileSize) {
            return false;
        }

        return true;
    }

    /**
     * @description Validate File
     * @return bool
     */
    protected function validateFile(): bool
    {
        if (!isset($this->files['file'])) {
            return false;
        }
        if ($this->files['file']['error'] != "0") {
            return false;
        }
        if ($this->files['file']['name'] == "") {
            return false;
        }
        if ($this->files['file']['tmp_name'] == "") {
            return false;
        }
        if ($this->files['file']['type'] == "") {
            return false;
        }
        if ($this->files['file']['size'] == "") {
            return false;
        }
        if (!$this->checkAcceptFilesExtension()) {
            return false;
        }
        if (!$this->checkAcceptFilesSize($this->files['file']['size'])) {
            return false;
        }

        return true;
    }

    /**
     * @description Send
     * @return bool
     */
    protected function send(): bool
    {
        $finalFileName = $this->prefix.basename($this->files['file']['name']);
        $finalFileName = str_replace(" ", "_", $finalFileName);
        $finalFileName = preg_replace('/\.([a-zA-Z]{3,4})$/', '', $finalFileName);
        $finalFileName = $finalFileName."_".date("YmdHis").".".$this->extensionFile;

        if (move_uploaded_file($this->files['file']['tmp_name'], $this->pathToSave . $finalFileName)) {
            if ($this->chmod != "" && is_numeric($this->chmod)) {
                chmod($this->pathToSave . $finalFileName, $this->chmod);
            }
            return true;
        }

        return false;
    }
}
