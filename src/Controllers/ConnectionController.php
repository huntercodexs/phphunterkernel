<?php

namespace PhpHunter\Kernel\Controllers;

use PDO;
use Exception;
use PDOException;
use PhpHunter\Kernel\Controllers\SettingsController;
use PhpHunter\Kernel\Abstractions\ParametersAbstract;

abstract class ConnectionController extends ParametersAbstract
{
    protected array $acceptedDatabase = [
        "mysql",
        "mssql",
        "postgres",
        "mongodb",
    ];

    /**
     * @description to control rollback statement
     */
    protected bool $doRollback = false;
    protected string $rollbackError;

    /**
     * @description storage query result
    */
    protected array $queryResult = [];

    /**
     * @description to handler connection
    */
    protected string|object|array $connection;

    /**
     * @description connection settings
    */
    protected string $dbType;
    protected string $driver;
    protected string $server;
    protected string $port;
    protected string $database;
    protected string $user;
    protected string $password;

    /**
     * @description Constructor Class
     */
    public function __construct()
    {
    }

    /**
     * @description Set Connection
     * @param string $db_type #Mandatory
     * @return object
     */
    protected function setConnection(string $db_type): object
    {
        $app_config = new SettingsController();
        $settings = $app_config->getAppSettings();

        $this->driver   = $settings()['databases'][$db_type]['driver'];
        $this->server   = $settings()['databases'][$db_type]['server'];
        $this->port     = $settings()['databases'][$db_type]['port'];
        $this->database = $settings()['databases'][$db_type]['database'];
        $this->user     = $settings()['databases'][$db_type]['user'];
        $this->password = $settings()['databases'][$db_type]['password'];

        return $this;
    }

    /**
     * @description Open Connection
     * @param string $db_type #Mandatory
     * @return object
     */
    protected function openConnection(string $db_type): object
    {
        try {

            switch ($db_type) {
                case "mysql":
                    $this->connection = new PDO(
                        "{$this->driver}:host={$this->server}:{$this->port};dbname={$this->database}",
                        "{$this->user}",
                        "{$this->password}");
                    break;

                case "mssql":
                    $this->connection = new PDO(
                        "{$this->driver}:Server={$this->server};Database={$this->database}",
                        "{$this->user}",
                        "{$this->password}");
                    break;

            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            $except = $e->getMessage();
            $error = "[PDOException:500]";
            $error .= "\nError: " . $except;

            if (preg_match('/could not find driver/', $except, $m, PREG_OFFSET_CAPTURE)) {
                $error .= "\nAvailable drivers: " . implode(", ", PDO::getAvailableDrivers());
            }
            HunterCatcherController::hunterException($error, true);
        }

        return $this;
    }

    /**
     * @description Start Transaction
     * @return object
     */
    protected function startTransaction(): object
    {
        $this->connection->beginTransaction();
        return $this;
    }

    /**
     * @description Query Trigger
     * @param string $query #Mandatory
     * @return object
     */
    protected function queryTrigger(string $query): object
    {
        $this->queryResult = $this->connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return $this;
    }

    /**
     * @description Query Transaction
     * @param string $query #Mandatory
     * @return object
     */
    protected function queryTransaction(string $query): object
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
        } catch (PDOException|Exception $e) {
            $this->doRollback = true;
            $this->rollbackError = $e->getMessage();
        }

        return $this;
    }

    /**
     * @description Rollback Transaction
     * @return object
     */
    protected function rollbackTransaction(): object
    {
        if ($this->doRollback) {
            $this->connection->rollback();
        }
        return $this;
    }

    /**
     * @description Commit Transaction
     * @return object
     */
    protected function commitTransaction(): object
    {
        if (!$this->doRollback) {
            $this->connection->commit();
        }
        return $this;
    }

    /**
     * @description Close Connection
     * @return void
     */
    protected function closeConnection(): void
    {
        unset($this->connection);
        if ($this->doRollback) {
            HunterCatcherController::hunterException($this->rollbackError, true);
        }
    }

    /**
     * @description Dispatch Transaction
     * @param string $db_type #Mandatory
     * @param string $query #Mandatory
     * @return bool
     */
    protected function dispatchTransaction(string $db_type, string $query): bool
    {
        $this
            ->setConnection($db_type)
            ->openConnection($db_type)
            ->startTransaction()
            ->queryTransaction($query)
            ->rollbackTransaction()
            ->commitTransaction()
            ->closeConnection();

        return true;
    }

    /**
     * @description Dispatch Query
     * @param string $db_type #Mandatory
     * @param string $query #Mandatory
     * @return array
     */
    protected function dispatchQuery(string $db_type, string $query): array
    {
        $this
            ->setConnection($db_type)
            ->openConnection($db_type)
            ->queryTrigger($query)
            ->closeConnection();

        return $this->queryResult;
    }
}
