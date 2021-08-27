<?php

namespace Atlas\Controllers;

class FileManagerController extends AndyController
{
    private $acceptedFiles;
    private $acceptedFileSize;
    private $file;
    private $pathToSave;

    public function __construct()
    {
        $this->file = $_FILES;
        $this->acceptedFiles = ["gif", "png", "jpg", "jpeg", "pdf"];
        $this->acceptedFileSize = 1024 * 1024 * 2; //2MB
        $this->pathToSave = "file_manager/";
    }

    public function checkAcceptFilesExtension(): bool
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

    public function checkAcceptFilesSize($size): bool
    {
        if ($size > $this->acceptedFileSize) {
            return false;
        }
        return true;
    }

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

    public function send()
    {
        $finalFileName = basename($this->file['filename']['name']);

        if (move_uploaded_file($this->file['filename']['tmp_name'], $this->pathToSave . $finalFileName)) {

            $this->returnCode = 200;
            $this->returnData = ['message' => 'Arquivo enviado com sucesso'];

        } else {

            $this->returnCode = 500;
            $this->returnData = ['error' => 'Internal Server Error'];

            echo "Não foi possível enviar o arquivo, tente novamente";

        }
    }
}
