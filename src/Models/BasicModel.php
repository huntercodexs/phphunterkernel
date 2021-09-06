<?php

namespace PhpHunter\Kernel\Models;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Builders\QueryBuilder;
use PhpHunter\Kernel\Controllers\HunterCatcherController;
use PhpHunter\Kernel\Controllers\InitServerController;

abstract class BasicModel extends QueryBuilder
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
     * @param array $values #Mandatoy
     * @return bool
     */
    protected function new(array $values): bool
    {
        return true;
    }

    /**
     * @description Select [READ:HTTP/GET]
     * @param int $id #Mandatory
     * @param array $fields #Optional
     * @return array
     */
    protected function read(int $id, array $fields): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Select [READ:HTTP/GET]
     * @param array $fields #Optional
     * @return array
     */
    protected function readAll(array $fields): array
    {
        $this->firstly();
        return [];
    }

    /**
     * @description Up [UPDATE:HTTP/PUT]
     * @param string $param #Optional
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function up(string $param, array $fields): bool
    {
        return true;
    }

    /**
     * @description Down [DELETE:HTTP/DELETE]
     * @param int $id #Mandatory
     * @param array $params #Optional
     * @return bool
     */
    protected function down(int $id, array $params = []): bool
    {
        return true;
    }

    /**
     * @description Fix #BasicModel
     * @param string $param #Optional
     * @param array $fields #Optional
     * @return bool
     */
    protected function fix(string $param, array $fields): bool
    {
        return true;
    }

    /**
     * @description Create [CREATE:HTTP/POST]
     * @param array $fields #Mandatoy
     * @return bool
     */
    protected function create(array $fields): bool
    {
        return true;
    }

    /**
     * From Query Builder: DO NOT CHANGE !
    */
    public function insert(array $fields): object
    {
        return parent::insert($fields);
    }

    public function select(array $fields, string $table, string $alias): object
    {
        return parent::select($fields, $table, $alias);
    }

    public function update(string $table): object
    {
        return parent::update($table);
    }

    public function delete(string $param): object
    {
        return parent::delete($param);
    }

    public function from(string $table): object
    {
        return parent::from($table);
    }

    public function patcher(string $table): object
    {
        return parent::patcher($table);
    }

    public function into(string $table): object
    {
        parent::into($table);
    }

    public function values(array $values): object
    {
        parent::values($values);
    }

    public function join(string $table, string $alias, string $on): object
    {
        return parent::join($table, $alias, $on);
    }

    public function set(string $field_name, string $field_value): object
    {
        parent::set($field_name, $field_value);
    }

    public function leftJoin(string $table, string $alias, string $on): object
    {
        return parent::leftJoin($table, $alias, $on);
    }

    public function rightJoin(string $table, string $alias, string $on): object
    {
        return parent::rightJoin($table, $alias, $on);
    }

    public function outerJoin(string $table, string $alias, string $on): object
    {
        return parent::outerJoin($table, $alias, $on);
    }

    public function innerJoin(string $table, string $alias, string $on): object
    {
        return parent::innerJoin($table, $alias, $on);
    }

    public function where(string $where, string $op = ""): object
    {
        return parent::where($where, $op);
    }

    public function groupBy(string $by): object
    {
        return parent::groupBy($by);
    }

    public function orderBy(string $by): object
    {
        return parent::orderBy($by);
    }

    public function limit(string $limit, string $cmd = ""): object
    {
        return parent::limit($limit, $cmd);
    }

    public function getSQL(): null|string
    {
        return parent::pureSQL();
    }

    public function builder(): object
    {
        parent::builder();
    }

    public function pureSQL(string $query): void
    {
        parent::pureQuery($query);
    }

    public function persist(): bool
    {
        return parent::persist();
    }

}
