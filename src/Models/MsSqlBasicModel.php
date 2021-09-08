<?php

namespace PhpHunter\Kernel\Models;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Builders\MsSqlQueryBuilder;
use PhpHunter\Kernel\Controllers\InitServerController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;

abstract class MsSqlBasicModel extends MsSqlQueryBuilder
{
    /**
     * @description Use to storage the result of the querys
    */
    protected array $result = [];

    /**
     * @description Use to mask fields in results that don't can is clear, note that
     * already is defined any values by default
    */
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

    /**
     * @description Use to hidden fields in results that don't can is visible
    */
    protected array $dataHidden = [];

    /**
     * @description Use to filter results and show only fields that are in array
     */
    protected array $dataOnly = [];

    /**
     * @description id
     * @var int $id
     */
    protected string $id;

    /**
     * @description name
     * @var string $name
     */
    protected string $name;

    /**
     * @description description
     * @var string $description
     */
    protected string $description;

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
        $this->qb = new MsSqlQueryBuilder();
    }

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
     * @description New [CREATE:HTTP/POST]
     * @param array $body_params #Mandatoy
     * @example [POST] http://local.phphunter.dockerized/api/user
     * @return bool
     */
    protected function new(array $body_params): bool
    {
        return true;
    }

    /**
     * @description Select [READ:HTTP/GET]
     * @param array $uri_rest_params #Mandatory
     * @param array $only_fields #Optional
     * @example [GET] http://local.phphunter.dockerized/api/user/444444
     * @return array
     */
    protected function read(array $uri_rest_params, array $only_fields = []): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Select [READ:HTTP/GET]
     * @param array $only_fields #Optional
     * @param array $criteria #Optional
     * @example [GET] http://local.phphunter.dockerized/api/user
     * @return array
     */
    protected function readAll(array $only_fields = [], array $criteria = []): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Up [UPDATE:HTTP/PUT]
     * @param array $uri_rest_params #Optional
     * @param array $body_params #Mandatoy
     * @example [PUT] http://local.phphunter.dockerized/api/user/333333
     * @return bool
     */
    protected function up(array $uri_rest_params, array $body_params): bool
    {
        return true;
    }

    /**
     * @description Down [DELETE:HTTP/DELETE]
     * @param array $uri_rest_params #Mandatory
     * @example [DELETE] http://local.phphunter.dockerized/api/user/222222
     * @return bool
     */
    protected function down(array $uri_rest_params): bool
    {
        return true;
    }

    /**
     * @description Fix #BasicModel
     * @param array $uri_rest_params #Mandatory
     * @param array $body_params #Mandatory
     * @example [PATCH] http://local.phphunter.dockerized/api/user/111111
     * @return bool
     */
    protected function fix(array $uri_rest_params, array $body_params): bool
    {
        return true;
    }

    /**
     * @description Generate [CREATE:HTTP/POST]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function generate(array $fields): bool
    {
        return true;
    }
}
