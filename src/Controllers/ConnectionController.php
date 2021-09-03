<?php

namespace PhpHunter\Kernel\Controllers;

use PhpHunter\Kernel\Abstractions\ParametersAbstract;

abstract class ConnectionController extends ParametersAbstract
{
    protected string $connection;
    protected string $server;
    protected string $database;
    protected string $port;
    protected string $user;
    protected string $password;

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
