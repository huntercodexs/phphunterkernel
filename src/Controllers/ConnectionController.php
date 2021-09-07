<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Abstractions\ParametersAbstract;

abstract class ConnectionController extends ParametersAbstract
{
    protected array $acceptedDatabase = [
        "mysql",
        "mssql",
        "postgres",
        "mongodb",
    ];

    protected string $dbType;
    protected string $connection;
    protected string $server;
    protected string $database;
    protected string $port;
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
     * @return void
     */
    public function setConnection(): object
    {
    }

    /**
     * @description Open Connection
     * @return void
     */
    public function openConnection(): object
    {
    }

    /**
     * @description Close Connection
     * @return void
     */
    public function closeConnection(): object
    {
    }
}
