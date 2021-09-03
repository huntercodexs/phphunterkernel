<?php

namespace PhpHunter\Kernel\Models;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Controllers\ConnectionController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;
use PhpHunter\Kernel\Controllers\InitServerController;

abstract class BasicModel extends ConnectionController
{
    protected array $result = [];
    protected array $dataMask = [
        'password',
        'key',
        'secret_key',
        'secret_manager',
        'api_key',
        'pass',
        'passwd',
        'secret',
        'token',
        'api_token',
        'remember_token'
    ];
    protected array $dataHidden = [];
    protected array $dataOnly = [];

    /**
     * @description Data Hidden Replace
     * @return void
     */
    protected function dataMaskReplace(array $source): void
    {
        $this->dataMask = $source;
    }

    /**
     * @description Data Hidden Append
     * @return void
     */
    protected function dataMaskAdd(array $source): void
    {
        array_merge($this->dataMask, $source);
    }

    /**
     * @description Data Hidden
     * @return array
     */
    protected function dataMask(): array
    {
        return $this->dataMask;
    }

    /**
     * @description Data Hidden
     * @return void
     */
    protected function firstly(): void
    {
        $array_handler = new ArrayHandler();

        /**
         * @description There is a priority in the manipulation of array data, as follows,
         * see that one case overwrites the previous one...
     *          1. If defined dataMask
         *          2. If defined dataHidden overwrite dataMask
         *              3. If defined dataOnly overwrite dataHidden
        */

        /*First*/
        if(isset($this->dataMask) && count($this->dataMask) > 0) {
            $array_handler->setArrayData($this->result);
            $array_handler->setArraySearch($this->dataMask);
            $this->result = $array_handler->arrayMasterHandler('mask');
        }

        /*Second*/
        if(isset($this->dataHidden) && count($this->dataHidden) > 0) {
            $array_handler->setArrayData($this->result);
            $array_handler->setArraySearch($this->dataHidden);
            $this->result = $array_handler->arrayMasterHandler('hidden');
        }

        /*Third*/
        if(isset($this->dataOnly) && count($this->dataOnly) > 0) {
            $array_handler->setArrayData($this->result);
            $array_handler->setArraySearch($this->dataOnly);
            $this->result = $array_handler->arrayMasterHandler('only');
        }
    }

    /**
     * @description Insert [CREATE:HTTP/POST]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function insert(array $fields): bool
    {
        return true;
    }

    /**
     * @description Select [READ:HTTP/GET]
     * @param array $fields #Optional
     * @return array
     */
    protected function select(array $fields = []): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Update [UPDATE:HTTP/PUT]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function update(array $fields): bool
    {
        return true;
    }

    /**
     * @description Delete [DELETE:HTTP/DELETE]
     * @param int $id #Mandatory
     * @param array $params #Optional
     * @return bool
     */
    protected function delete(int $id, array $params = []): bool
    {
        return true;
    }

    /**
     * @description Patcher [PATCH:HTTP/PATCH]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function patcher(array $fields): bool
    {
        return true;
    }

}
