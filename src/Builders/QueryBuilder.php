<?php

namespace PhpHunter\Kernel\Builders;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Controllers\ConnectionController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;

class QueryBuilder extends ConnectionController
{
    /**
     * @description Use to instance of query builder
     */
    protected object $qb;

    /**
     * @description Alias to model
     */
    protected string $alias;

    /**
     * @description define wich command is in use
     */
    private string $queryCommand;

    /**
     * @description the model that will be affected
     */
    private string $queryModel;

    /**
     * @description control use where clausule in the query
     */
    private bool $activeWhere = false;

    /**
     * @description storage step by step to create a sql instructions
     */
    private array $sqlBuilder = [];

    /**
     * @description query after builder
     */
    private ?string $queryBuilder = null;

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

    //--------------------------------------------------------------------------------------------
    // QUERY COMMANDS
    //--------------------------------------------------------------------------------------------

    /**
     * @description Insert
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function insert(array $fields, string $model, string $alias): object
    {
        $this->queryCommand = "insert";
        return $this;
    }

    /**
     * @description Select
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function select(array $fields, string $model, string $alias): object
    {
        $this->queryCommand = "select";
        $fields = implode(', ', $fields);
        $command = "\nSELECT\n\t{$fields}\nFROM\n\t{$model} {$alias}\n";
        array_push($this->sqlBuilder, $command);
        return $this;
    }

    /**
     * @description Update
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function update(array $fields, string $model, string $alias): object
    {
        $this->queryCommand = "update";
        return $this;
    }

    /**
     * @description Delete
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function delete(array $fields, string $model, string $alias): object
    {
        $this->queryCommand = "delete";
        return $this;
    }

    /**
     * @description Patcher
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function patcher(array $fields, string $model, string $alias): object
    {
        $this->queryCommand = "patcher";
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // QUERY CONDITIONS
    //--------------------------------------------------------------------------------------------

    /**
     * @description Join
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function join(string $table, string $alias, string $on): object
    {
        array_push($this->sqlBuilder, "\tJOIN {$table} {$alias} {$on}\n");
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
        array_push($this->sqlBuilder, "\tLEFT JOIN {$table} {$alias} {$on}\n");
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
        array_push($this->sqlBuilder, "\tRIGHT JOIN {$table} {$alias} {$on}\n");
        return $this;
    }

    /**
     * @description Inner Select
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function innerJoin(string $table, string $alias, string $on): object
    {
        array_push($this->sqlBuilder, "\tINNER JOIN {$table} {$alias} {$on}\n");
        return $this;
    }

    /**
     * @description Outer Select
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return object
     */
    protected function outerJoin(string $table, string $alias, string $on): object
    {
        array_push($this->sqlBuilder, "\tOUTER JOIN {$table} {$alias} {$on}\n");
        return $this;
    }

    /**
     * @description Where
     * @param string $where #Mandatory
     * @param string $op #Optional
     * @return object
     */
    protected function where(string $where, string $op = ""): object
    {
        if ($this->activeWhere == false) {
            array_push($this->sqlBuilder, "WHERE");
            $this->activeWhere = true;
        }

        if ($op != "") {
            array_push($this->sqlBuilder, "\n\t{$op} {$where}");
        } else {
            array_push($this->sqlBuilder, "\n\t{$where}");
        }

        return $this;
    }

    /**
     * @description Group By
     * @param string $by #Mandatory
     * @return object
     */
    protected function groupBy(string $by): object
    {
        array_push($this->sqlBuilder, "\nGROUP BY\n\t{$by}");
        return $this;
    }

    /**
     * @description Order By
     * @param string $by #Mandatory
     * @return object
     */
    protected function orderBy(string $by): object
    {
        array_push($this->sqlBuilder, "\nORDER BY\n\t{$by}");
        return $this;
    }

    /**
     * @description Limit
     * @param string $limit #Mandatory
     * @return object
     */
    protected function limit(string $limit): object
    {
        array_push($this->sqlBuilder, "\nLIMIT {$limit}");
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // QUERY BUILDERS
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
    }

    /**
     * @description Select Builder
     * @return void
     */
    private function selectBuilder(): void
    {
        for ($h = 0; $h < count($this->sqlBuilder); $h++) {
            $this->queryBuilder .= $this->sqlBuilder[$h];
        }
        $this->queryBuilder .= ";";
    }

    /**
     * @description Update Builder
     * @return void
     */
    private function updateBuilder(): void
    {
    }

    /**
     * @description Delete Builder
     * @return void
     */
    private function deleteBuilder(): void
    {
    }

    /**
     * @description Patcher Builder
     * @return void
     */
    private function patcherBuilder(): void
    {
    }

    //--------------------------------------------------------------------------------------------
    // QUERY SQL
    //--------------------------------------------------------------------------------------------

    /**
     * @description Pure Query
     * @return void
     */
    protected function pureSQL(string $query): void
    {
        $this->queryBuilder = $query;
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
     * @return void
     */
    private function queryOptimize(): void
    {
        $this->queryBuilder = preg_replace('/\t/', '',
            preg_replace('/\n/', ' ', $this->queryBuilder
            )
        );
    }

    /**
     * @description Persist
     * @return bool
     */
    protected function persist(): bool
    {
        $this->queryOptimize();
        return true;
    }
}
