<?php

namespace PhpHunter\Kernel\Builders;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Controllers\ConnectionController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;

/*Specific Builders*/
use PhpHunter\Kernel\Builders\MySqlBuilder;
use PhpHunter\Kernel\Builders\MsSqlBuilder;
use PhpHunter\Kernel\Builders\PostgresBuilder;
use PhpHunter\Kernel\Builders\MongodbBuilder;
use PhpHunter\Kernel\Builders\SqliteBuilder;

abstract class QueryBuilder extends ConnectionController
{
    /**
     * @description to control insert query operations
     */
    protected int $countInsertFields;
    protected int $countInsertValues;

    /**
     * @description storage step by step to create a sql instructions
     */
    protected array $pushBuilder = [];

    /**
     * @description Alias to model
     */
    protected string $alias;

    /**
     * @description Alias to model
     */
    protected string $modelName;

    /**
     * @description control use where clausule in the query
     */
    protected bool $activeWhere = false;

    /**
     * @description used when the sql query is update
     */
    protected array $saveSet = [];

    /**
     * @description control use set clausule in the query
     */
    protected bool $activeSet = false;

    /**
     * @description Columns on model (in database)
     */
    protected array $modelColumns = [];

    /**
     * @description define wich command is in use
     */
    private string $queryCommand;

    /**
     * @description storage temporary querys
     */
    private string $tmpQuery = "";

    /**
     * @description to use on query delete
     */
    private string $queryDelete = "";

    /**
     * @description query after builder
     */
    private ?string $queryBuilder = null;

    /**
     * @description the specific builder to db_type
     */
    private object $currentBuilder;

    /**
     * @description the sql commands accepted
    */
    private const __SQL_COMMANDS__ = [
        "INSERT",
        "SELECT",
        "UPDATE",
        "DELETE",
        "ALTER",
        "DROP",
        "CREATE"
    ];

    /**
     * @description Namespace for builders
    */
    private string $builders = "PhpHunter\\Kernel\\Builders\\";

    //--------------------------------------------------------------------------------------------
    // INSERT
    //--------------------------------------------------------------------------------------------

    /**
     * @description Insert
     * @param array $values #Mandatory
     * @return object
     */
    protected function insert(array $values): object
    {
        $this->queryCommand = "insert";
        $builder = "{$this->builders}{$this->targetBuilder}";
        $this->currentBuilder = new $builder($this);
        $this->currentBuilder->_insert_($this->modelColumns, $values);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // SELECT
    //--------------------------------------------------------------------------------------------

    /**
     * @description Select
     * @param array $fields #Optional
     * @param bool $distinct #Optional
     * @return object
     */
    protected function select(array $fields = [], bool $distinct = false): object
    {
        $this->queryCommand = "select";
        $builder = "{$this->builders}{$this->targetBuilder}";
        $this->currentBuilder = new $builder($this);
        $this->currentBuilder->_select_($fields, $distinct);
        return $this;
    }

    /**
     * @description Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function join(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_join_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Left Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function leftJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_leftJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Right Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function rightJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_rightJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Inner Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function innerJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_innerJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Outer Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function outerJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_outerJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Full Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function fullJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_fullJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Cross Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function crossJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_crossJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Full Outer Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function fullOuterJoin(string $table, string $alias, string $on): object
    {
        $this->currentBuilder->_fullOuterJoin_($table, $alias, $on);
        return $this;
    }

    /**
     * @description Union
     * @return object
     */
    protected function union(): object
    {
        $this->currentBuilder->_union_();
        return $this;
    }

    /**
     * @description Union All
     * @return object
     */
    protected function unionAll(): object
    {
        $this->currentBuilder->_unionAll_();
        return $this;
    }

    /**
     * @description Intersect
     * @return object
     */
    protected function intersect(): object
    {
        $this->currentBuilder->_intersect_();
        return $this;
    }

    /**
     * @description Except
     * @return object
     */
    protected function except(): object
    {
        $this->currentBuilder->_except_();
        return $this;
    }

    /**
     * @description Group By
     * @param string $by #Mandatory
     * @return object
     */
    protected function groupBy(string $by): object
    {
        $this->currentBuilder->_groupBy_($by);
        return $this;
    }

    /**
     * @description Order By
     * @param string $by #Mandatory
     * @return object
     */
    protected function orderBy(string $by): object
    {
        $this->currentBuilder->_orderBy_($by);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // UPDATE
    //--------------------------------------------------------------------------------------------

    /**
     * @description Update
     * @return object
     */
    protected function update(): object
    {
        $this->queryCommand = "update";
        $builder = "{$this->builders}{$this->targetBuilder}";
        $this->currentBuilder = new $builder($this);
        $this->currentBuilder->_update_();
        return $this;
    }

    /**
     * @description Update
     * @param string $field_name #Optional
     * @param string $field_value #Optional
     * @return object
     */
    protected function set(string $field_name, string $field_value): object
    {
        $this->currentBuilder->_set_($field_name, $field_value);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // DELETE
    //--------------------------------------------------------------------------------------------

    /**
     * @description Delete
     * @param int|string $id #Mandatory
     * @return object
     */
    protected function delete(int|string $id): object
    {
        $this->queryCommand = "delete";
        $builder = "{$this->builders}{$this->targetBuilder}";
        $this->currentBuilder = new $builder($this);
        $this->currentBuilder->_delete_($id);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // UPDATE-FIX
    //--------------------------------------------------------------------------------------------

    /**
     * @description Patcher
     * @return object
     */
    protected function patcher(): object
    {
        $this->queryCommand = "patcher";
        $builder = "{$this->builders}{$this->targetBuilder}";
        $this->currentBuilder = new $builder($this);
        $this->currentBuilder->_patcher_();
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // CREATE
    //--------------------------------------------------------------------------------------------

    /**
     * @description Create
     * @return object
     */
    protected function create(): object
    {
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // GENERIC
    //--------------------------------------------------------------------------------------------

    /**
     * @description Where
     * @param string $where #Mandatory
     * @param string $op #Optional
     * @return object
     */
    protected function where(string $where, string $op = ""): object
    {
        $this->currentBuilder->_where_($where, $op);
        return $this;
    }

    /**
     * @description Limit
     * @param string $limit #Mandatory
     * @param string $cmd #Optional
     * @return object
     */
    protected function limit(string $limit, string $cmd = ""): object
    {
        $this->currentBuilder->_limit_($limit, $cmd);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // BUILDER
    //--------------------------------------------------------------------------------------------

    /**
     * @description Builder
     * @return object
     */
    protected function builder(): object
    {
        $this->queryBuilder = "";

        if ($this->queryCommand == 'insert') {
            $this->insertBuilder();
        } elseif ($this->queryCommand == 'select') {
            $this->selectBuilder();
        } elseif ($this->queryCommand == 'update') {
            $this->updateBuilder();
        } elseif ($this->queryCommand == 'delete') {
            $this->deleteBuilder();
        } elseif ($this->queryCommand == 'patcher') {
            $this->patcherBuilder();
        }

        return $this;
    }

    /**
     * @description Insert Builder
     * @return void
     */
    private function insertBuilder(): void
    {
        for ($h = 0; $h < count($this->pushBuilder); $h++) {
            $this->queryBuilder .= $this->pushBuilder[$h];
        }
        $this->queryBuilder .= ";";

        if ($this->countInsertFields != $this->countInsertValues) {
            HunterCatcherController::hunterApiCatcher(
                [
                    'critical-error' => "Invalid fields/values to insert query !",
                    'query' => $this->querySanitize()
                ], 500, true);
        }
    }

    /**
     * @description Select Builder
     * @return void
     */
    private function selectBuilder(): void
    {
        for ($h = 0; $h < count($this->pushBuilder); $h++) {
            $this->queryBuilder .= $this->pushBuilder[$h];
        }
        $this->queryBuilder .= ";";
    }

    /**
     * @description Update Builder
     * @return void
     */
    private function updateBuilder(): void
    {

        for ($h = 0; $h < count($this->pushBuilder); $h++) {
            $this->queryBuilder .= $this->pushBuilder[$h];
            if (preg_match('/SET/', $this->pushBuilder[$h], $m, PREG_OFFSET_CAPTURE)) {
                $set_values = implode(', ', $this->saveSet);
                $this->queryBuilder .= "\n\t".$set_values."\n";
            }
        }
        $this->queryBuilder .= ";";

        $this->updateCheck();
    }

    /**
     * @description Delete Builder
     * @return void
     */
    private function deleteBuilder(): void
    {
        for ($h = 0; $h < count($this->pushBuilder); $h++) {
            $this->queryBuilder .= $this->pushBuilder[$h];
        }
        $this->queryBuilder .= ";";
    }

    /**
     * @description Patcher Builder
     * @return void
     */
    private function patcherBuilder(): void
    {
        if (count($this->saveSet) > 1) {
            HunterCatcherController::hunterApiCatcher(
                [
                    'error' => "Operation is not allowed for this query, its only patch no update !"
                ], 500, true);
        }

        for ($h = 0; $h < count($this->pushBuilder); $h++) {
            $this->queryBuilder .= $this->pushBuilder[$h];
            if (preg_match('/SET/', $this->pushBuilder[$h], $m, PREG_OFFSET_CAPTURE)) {
                $this->queryBuilder .= "\n\t".$this->saveSet[0]."\n";
            }
        }
        $this->queryBuilder .= ";";

        $this->updateCheck();
    }

    /**
     * @description Update Check
     * @return void
     */
    private function updateCheck(): void
    {
        if (count($this->saveSet) == 0) {
            HunterCatcherController::hunterApiCatcher(
                [
                    'critical-error' => "Invalid operation for Update (missing SET parameters) !",
                    'query' => $this->querySanitize()
                ], 500, true);
        }

        if (!in_array('WHERE', $this->pushBuilder)) {
            HunterCatcherController::hunterApiCatcher(
                [
                    'critical-error' => "Invalid operation for Update (missing WHERE) !",
                    'query' => $this->querySanitize()
                ], 500, true);
        }
    }

    //--------------------------------------------------------------------------------------------
    // QUERY SQL
    //--------------------------------------------------------------------------------------------

    /**
     * @description Pure Query
     * @return object
     */
    protected function pureSQL(string $query): object
    {
        $this->queryCommand = "pure";
        $this->queryBuilder = $query;
        return $this;
    }

    /**
     * @description Pure SQL
     * @return null|string
     */
    protected function getSQL(): null|string
    {
        return $this->queryBuilder;
    }

    /**
     * @description Query Optimize
     * @return string
     */
    private function querySanitize(): string
    {
        pr($this->queryBuilder);
        unset($this->currentBuilder);
        return preg_replace('/\t/', '',
            preg_replace('/\n/', ' ', $this->queryBuilder
            )
        );
    }

    /**
     * @description Save (use only to data persist)
     * @return bool
     */
    protected function save(): bool
    {
        if ($this->queryCommand == "select") {
            HunterCatcherController::hunterException('Error: Operation not accepted, use run() !', true);
        }
        return $this->dispatchTransaction($this->dbType, $this->querySanitize());
    }

    /**
     * @description Run
     * @return void
     */
    protected function run(): void
    {
        if ($this->queryCommand != "select" && $this->queryCommand != "pure") {
            HunterCatcherController::hunterException('Error: Operation not accepted, use dispatcher() !', true);
        }
        $this->dispatchQuery($this->dbType, $this->querySanitize());
    }

    //--------------------------------------------------------------------------------------------
    // SETTERS & GETTERS
    //--------------------------------------------------------------------------------------------

    public function setCountInsertFields(int $count): void
    {
        $this->countInsertFields = $count;
    }

    public function getCountInsertFields(): int
    {
        return $this->countInsertFields;
    }

    public function setCountInsertValues(int $count): void
    {
        $this->countInsertValues = $count;
    }

    public function getCountInsertValues(): int
    {
        return $this->countInsertValues;
    }

    public function setPushBuilder(string $push, int $idx = -1): void
    {
        if ($idx != "-1"){
            $this->pushBuilder[$idx] = $push;
        } else {
            array_push($this->pushBuilder, $push);
        }
    }

    public function getPushBuilder(): array
    {
        return $this->pushBuilder;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setModelName(string $name): void
    {
        $this->modelName = $name;
    }

    public function getNameModel(): string
    {
        return $this->modelName;
    }

    public function setActiveWhere(bool $active): void
    {
        $this->activeWhere = $active;
    }

    public function getActiveWhere(): bool
    {
        return $this->activeWhere;
    }

    public function setSaveSet(string $save): void
    {
        array_push($this->saveSet, $save);
    }

    public function getSaveSet(): array
    {
        return $this->saveSet;
    }

    public function setActiveSet(bool $active): void
    {
        $this->activeSet = $active;
    }

    public function getActiveSet(): bool
    {
        return $this->activeSet;
    }

    //--------------------------------------------------------------------------------------------
    // TESTERS & HELPERS
    //--------------------------------------------------------------------------------------------

    /**
     * @description Test Query Builder
     * @return void
     */
    protected function testQueryBuilder(): void
    {
        pr($this->dbType);
        pr("Query Builder is working...");
        pr(get_called_class());
        pr(get_parent_class());
        pr(get_class_methods(get_called_class()));
        pr(get_class_vars(get_called_class()));
        die;
    }
}
