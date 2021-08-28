<?php

namespace PhpHunter\Kernel\Controllers;

class FileManagerController
{
    private array $file;
    private array $acceptedFiles;
    private string $pathToSave;
    private string $prefix;
    private int $chmod;
    private int $acceptedFileSize;

    /**
     * @description Constructor Class
    */
    public function __construct(array $files)
    {
        $api_service_config = new SetupController();
        $ApiServiceConfig = $api_service_config->getServicesConfigurationSetup();
        $upload_config = $ApiServiceConfig()['upload'];

        $this->file = $files;
        $this->acceptedFiles = $upload_config['accepted'];
        $this->acceptedFileSize = $upload_config['maxsize'];
        $this->pathToSave = $upload_config['dir'];
        $this->chmod = $upload_config['chmod'];
        $this->prefix = $upload_config['prefix'];
    }

    /**
     * @description Check Accept Files Extension
     * @return bool
     */
    private function checkAcceptFilesExtension(): bool
    {
        $checkin = explode(".", $this->file['filename']['name']);
        $extension = strtolower(end($checkin));

        if (!is_array($checkin) || count($checkin) == 0) {
            return false;
        }
        if (!in_array($extension, $this->acceptedFiles)) {
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
    public function validateFile(): bool
    {
        if (!isset($this->file['filename'])) {
            return false;
        }
        if ($this->file['filename']['error'] != "0") {
            return false;
        }
        if ($this->file['filename']['name'] == "") {
            return false;
        }
        if ($this->file['filename']['tmp_name'] == "") {
            return false;
        }
        if ($this->file['filename']['type'] == "") {
            return false;
        }
        if ($this->file['filename']['size'] == "") {
            return false;
        }
        if (!$this->checkAcceptFilesExtension()) {
            return false;
        }
        if (!$this->checkAcceptFilesSize($this->file['filename']['size'])) {
            return false;
        }
        return true;
    }

    /**
     * @description Send
     * @return bool
     */
    public function send(): bool
    {
        $finalFileName = $this->prefix.basename($this->file['filename']['name']);

        if (move_uploaded_file($this->file['filename']['tmp_name'], $this->pathToSave . $finalFileName)) {
            if ($this->chmod != "" && is_numeric($this->chmod)) {
                chmod($this->pathToSave . $finalFileName, $this->chmod);
            }
            return true;
        }
        return false;
    }
}
