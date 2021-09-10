<?php

namespace PhpHunter\Kernel\Builders;

class MsSqlBuilder
{
    /**
     * @description query builder dependence injection
     */
    private QueryBuilder $queryBuilder;

    /**
     * @description Constructor Class
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    //--------------------------------------------------------------------------------------------
    // INSERT
    //--------------------------------------------------------------------------------------------

    /**
     * @description _insert_
     * @param array $fields #Mandatory
     * @param array $values #Mandatory
     * @return void
     */
    public function _insert_(array $fields, array $values): void
    {
        $this->queryBuilder->setCountInsertFields(count($fields));
        $fields = implode(', ', $fields);
        $this->queryBuilder->setCountInsertValues(count($values));
        $values = "'".implode("', '", $values)."'";
        $command = "\nINSERT INTO {$this->queryBuilder->getNameModel()}\n\t({$fields})\nVALUES\n\t({$values})";
        $this->queryBuilder->setPushBuilder($command);
    }

    //--------------------------------------------------------------------------------------------
    // SELECT
    //--------------------------------------------------------------------------------------------

    /**
     * @description _select_
     * @param array $fields #Mandatory
     * @param bool $distinct #Optional
     * @return void
     */
    public function _select_(array $fields = [], bool $distinct = false): void
    {
        if (count($fields) == 0) {
            $fields[0] = "*";
        }

        $fields = implode(', ', $fields);

        if ($distinct) {
            $command = "\nSELECT DISTINCT\n\t{$fields}\nFROM\n\t{$this->queryBuilder->getNameModel()} {$this->queryBuilder->getAlias()}\n";
        } else {
            $command = "\nSELECT\n\t{$fields}\nFROM\n\t{$this->queryBuilder->getNameModel()} {$this->queryBuilder->getAlias()}\n";
        }
        $this->queryBuilder->setPushBuilder($command);
    }

    /**
     * @description _join_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _join_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tJOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _leftJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _leftJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tLEFT JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _rightJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _rightJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tRIGHT JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _innerJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _innerJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tINNER JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _outerJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _outerJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tOUTER JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _fullJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _fullJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tFULL JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _crossJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _crossJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tCROSS JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _fullOuterJoin_
     * @param string $table #Mandatory
     * @param string $alias #Mandatory
     * @param string $on #Mandatory
     * @return void
     */
    public function _fullOuterJoin_(string $table, string $alias, string $on): void
    {
        $this->queryBuilder->setPushBuilder("\tFULL OUTER JOIN {$table} {$alias} ON {$on}\n");
    }

    /**
     * @description _union_
     * @return void
     */
    public function _union_(): void
    {
        $this->queryBuilder->setPushBuilder("\nUNION\n");
    }

    /**
     * @description _unionAll_
     * @return void
     */
    public function _unionAll_(): void
    {
        $this->queryBuilder->setPushBuilder("\nUNION ALL\n");
    }

    /**
     * @description _intersect_
     * @return void
     */
    public function _intersect_(): void
    {
        $this->queryBuilder->setPushBuilder("\nINTERSECT\n");
    }

    /**
     * @description _except_
     * @return void
     */
    public function _except_(): void
    {
        $this->queryBuilder->setPushBuilder("\nEXCEPT\n");
    }

    /**
     * @description _groupBy_
     * @param string $by #Mandatory
     * @return void
     */
    public function _groupBy_(string $by): void
    {
        $this->queryBuilder->setPushBuilder("\nGROUP BY\n\t{$by}");
    }

    /**
     * @description _orderBy_
     * @param string $by #Mandatory
     * @return void
     */
    public function _orderBy_(string $by): void
    {
        $this->queryBuilder->setPushBuilder("\nORDER BY\n\t{$by}");
    }

    //--------------------------------------------------------------------------------------------
    // UPDATE
    //--------------------------------------------------------------------------------------------

    /**
     * @description _update_
     * @return void
     */
    public function _update_(): void
    {
        $command = "\nUPDATE {$this->queryBuilder->getNameModel()}\n";
        $this->queryBuilder->setPushBuilder($command);
    }

    /**
     * @description _set_
     * @param string $field_name #Mandatory
     * @param string $field_value #Mandatory
     * @return void
     */
    public function _set_(string $field_name, string $field_value): void
    {
        if ($this->queryBuilder->getActiveSet() == false) {
            $this->queryBuilder->setPushBuilder("SET");
            $this->queryBuilder->setActiveSet(true);
        }

        $command = "{$field_name} = '{$field_value}'";
        $this->queryBuilder->setSaveSet($command);
    }

    //--------------------------------------------------------------------------------------------
    // DELETE
    //--------------------------------------------------------------------------------------------

    /**
     * @description _delete_
     * @param int|string $id #Mandatory
     * @return void
     */
    public function _delete_(int|string $id): void
    {
        $command = "DELETE FROM {$this->queryBuilder->getNameModel()} WHERE id = '{$id}'";
        $this->queryBuilder->setPushBuilder($command);
    }

    //--------------------------------------------------------------------------------------------
    // UPDATE-FIX
    //--------------------------------------------------------------------------------------------

    /**
     * @description _patcher_
     * @return void
     */
    public function _patcher_(): void
    {
        $command = "\n/*[PATCHER]*/\nUPDATE {$this->queryBuilder->getNameModel()}\n";
        $this->queryBuilder->setPushBuilder($command);
    }

    //--------------------------------------------------------------------------------------------
    // CREATE
    //--------------------------------------------------------------------------------------------

    /**
     * @description _create_
     * @return void
     */
    public function _create_(): void
    {
        //Code here...
    }

    //--------------------------------------------------------------------------------------------
    // GENERIC
    //--------------------------------------------------------------------------------------------

    /**
     * @description _where_
     * @param string $where #Mandatory
     * @param string $op #Optional
     * @return void
     */
    public function _where_(string $where, string $op = ""): void
    {
        if ($this->queryBuilder->getActiveWhere() == false) {
            $this->queryBuilder->setPushBuilder("WHERE");
            $this->queryBuilder->setActiveWhere(true);
        }

        if ($op != "") {
            $this->queryBuilder->setPushBuilder("\n\t{$op} {$where}");
        } else {
            $this->queryBuilder->setPushBuilder("\n\t{$where}");
        }
    }

    /**
     * @description _limit_
     * @param string $limit #Mandatory
     * @param string $cmd #Optional
     * @return void
     */
    public function _limit_(string $limit, string $cmd = ""): void
    {
        $select_command = $this->queryBuilder->getPushBuilder()[0];
        $new_select_command = str_replace("SELECT", "SELECT TOP {$limit}", $select_command);
        $this->queryBuilder->setPushBuilder($new_select_command, 0);
    }

}
