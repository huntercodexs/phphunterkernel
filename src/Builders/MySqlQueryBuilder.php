<?php

namespace PhpHunter\Kernel\Builders;

use PhpHunter\Kernel\Utils\FileTools;
use PhpHunter\Kernel\Utils\ArrayHandler;
use PhpHunter\Kernel\Controllers\ConnectionController;
use PhpHunter\Kernel\Controllers\HunterCatcherController;

class MySqlQueryBuilder extends ConnectionController
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
     * @description control use set clausule in the query
     */
    private bool $activeSet = false;

    /**
     * @description storage temporary querys
     */
    private string $tmpQuery = "";

    /**
     * @description to use on query delete
     */
    private string $queryDelete = "";

    /**
     * @description storage step by step to create a sql instructions
     */
    private array $sqlBuilder = [];

    /**
     * @description used when the sql query is update
     */
    private array $saveSet = [];

    /**
     * @description query after builder
     */
    private ?string $queryBuilder = null;

    /**
     * @description to control insert query operations
    */
    private int $countInsertFields;
    private int $countInsertValues;

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
    // INSERT
    //--------------------------------------------------------------------------------------------

    /**
     * @description Insert
     * @param array $fields #Mandatory
     * @return object
     */
    protected function insert(array $fields): object
    {
        $this->queryCommand = "insert";
        $this->countInsertFields = count($fields);
        $fields = implode(', ', $fields);
        $this->tmpQuery = "\nINSERT INTO {{{_TABLE_NAME_}}}\n\t({$fields})\n";
        return $this;
    }

    /**
     * @description Into
     * @param string $table #Mandatory
     * @return object
     */
    protected function into(string $table): object
    {
        $command = str_replace('{{{_TABLE_NAME_}}}',$table, $this->tmpQuery);
        array_push($this->sqlBuilder, $command);
        return $this;
    }

    /**
     * @description Values
     * @param array $values #Mandatory
     * @return object
     */
    protected function values(array $values): object
    {
        $this->countInsertValues = count($values);
        $values = "'".implode("', '", $values)."'";
        $command = "VALUES\n\t({$values})";
        array_push($this->sqlBuilder, $command);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // SELECT
    //--------------------------------------------------------------------------------------------

    /**
     * @description Select
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function select(array $fields, string $model, string $alias): object
    {
        if (count($fields) == 0) {
            $fields[0] = "*";
        }
        $this->queryCommand = "select";
        $fields = implode(', ', $fields);
        $command = "\nSELECT\n\t{$fields}\nFROM\n\t{$model} {$alias}\n";
        array_push($this->sqlBuilder, $command);
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

    //--------------------------------------------------------------------------------------------
    // UPDATE
    //--------------------------------------------------------------------------------------------

    /**
     * @description Update
     * @param string $table #Optional
     * @return object
     */
    protected function update(string $table): object
    {
        $this->queryCommand = "update";
        $command = "\nUPDATE {$table}\n";
        array_push($this->sqlBuilder, $command);
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
        if ($this->activeSet == false) {
            array_push($this->sqlBuilder, "SET");
            $this->activeSet = true;
        }

        $command = "{$field_name} = '{$field_value}'";
        array_push($this->saveSet, $command);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // DELETE
    //--------------------------------------------------------------------------------------------

    /**
     * @description Delete
     * @param string $param #Mandatory
     * @return object
     */
    protected function delete(string $param): object
    {
        $this->queryCommand = "delete";
        $this->queryDelete = "DELETE FROM {{{_TABLE_NAME_}}} WHERE {$param} ";
        return $this;
    }

    /**
     * @description From (Delete)
     * @param string $table #Mandatory
     * @return object
     */
    protected function from(string $table): object
    {
        $command = str_replace('{{{_TABLE_NAME_}}}', $table, $this->queryDelete);
        array_push($this->sqlBuilder, $command);
        return $this;
    }

    //--------------------------------------------------------------------------------------------
    // UPDATE-FIX
    //--------------------------------------------------------------------------------------------

    /**
     * @description Patcher
     * @param array $fields #Mandatory
     * @param string $model #Mandatory
     * @param string $alias #Optional
     * @return object
     */
    protected function patcher(string $table): object
    {
        $this->queryCommand = "patcher";
        $command = "\n/*[PATCHER]*/\nUPDATE {$table}\n";
        array_push($this->sqlBuilder, $command);
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
     * @description Limit
     * @param string $limit #Mandatory
     * @param string $cmd #Optional
     * @return object
     */
    protected function limit(string $limit, string $cmd = ""): object
    {
        if ($cmd == "delete") {
            array_push($this->sqlBuilder, "LIMIT {$limit}");
        } else {
            array_push($this->sqlBuilder, "\nLIMIT {$limit}");
        }
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
        for ($h = 0; $h < count($this->sqlBuilder); $h++) {
            $this->queryBuilder .= $this->sqlBuilder[$h];
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
        for ($h = 0; $h < count($this->sqlBuilder); $h++) {
            $this->queryBuilder .= $this->sqlBuilder[$h];
            if (preg_match('/SET/', $this->sqlBuilder[$h], $m, PREG_OFFSET_CAPTURE)) {
                $set_values = implode(', ', $this->saveSet);
                $this->queryBuilder .= "\n\t".$set_values."\n";
            }
        }
        $this->queryBuilder .= ";";
    }

    /**
     * @description Delete Builder
     * @return void
     */
    private function deleteBuilder(): void
    {
        for ($h = 0; $h < count($this->sqlBuilder); $h++) {
            $this->queryBuilder .= $this->sqlBuilder[$h];
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

        for ($h = 0; $h < count($this->sqlBuilder); $h++) {
            $this->queryBuilder .= $this->sqlBuilder[$h];
            if (preg_match('/SET/', $this->sqlBuilder[$h], $m, PREG_OFFSET_CAPTURE)) {
                $this->queryBuilder .= "\n\t".$this->saveSet[0]."\n";
            }
        }
        $this->queryBuilder .= ";";
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
     * @return string
     */
    private function querySanitize(): string
    {
        return preg_replace('/\t/', '',
            preg_replace('/\n/', ' ', $this->queryBuilder
            )
        );
    }

    /**
     * @description Persist (use only to data persist)
     * @return bool
     */
    protected function persist(): bool
    {
        if ($this->queryCommand == "select") {
            HunterCatcherController::hunterException('Error: Operation not accepted, use run() !', true);
        }
        return $this->dispatchTransaction('mysql', $this->querySanitize());
    }

    /**
     * @description Run
     * @return array
     */
    protected function run(): array
    {
        if ($this->queryCommand != "select") {
            HunterCatcherController::hunterException('Error: Operation not accepted, use dispatcher() !', true);
        }
        return $this->dispatchQuery('mysql', $this->querySanitize());
    }
}
