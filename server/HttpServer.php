<?php

namespace App\Server;

use Lvinkim\SwordKernel\Component\KernelInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/9/25
 * Time: 6:22 PM
 */
class HttpServer
{

    /** @var KernelInterface */
    private $kernel;

    /** @var \swoole_table */
    private $table;

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run()
    {
        $serverConfig = $this->config["server"] ?? [];
        $host = $serverConfig["host"] ?? "0.0.0.0";
        $port = $serverConfig["port"] ?? 8080;

        $server = new Server($host, $port);

        $server->set($this->config["swoole"] ?? []);

        $server->on('start', [$this, 'onStart']);
        $server->on('WorkerStart', [$this, 'onWorkerStart']);
        $server->on('ManagerStart', [$this, 'onManagerStart']);
        $server->on('request', [$this, 'onRequest']);

        $this->createTable();

        $server->start();
    }

    public function onRequest(Request $request, Response $response)
    {
        $this->kernel->dispatchRequest($request, $response, $this->table);
    }

    public function onWorkerStart(Server $server, int $workerId)
    {
        swoole_set_process_name("sword-worker-{$workerId}");

        require $this->config["vendor"] . "";

        $settings = require $this->config["settings"] . "";
        $kernelClassName = $this->config["kernel"];

        $this->kernel = new $kernelClassName($settings);

        $this->kernel->dispatchWorkerStart($workerId, $this->table);

    }

    public function onManagerStart(Server $server)
    {
        swoole_set_process_name("sword-manager");
        $masterPidPath = $this->config["server"]["masterPidPath"] ?? "";
        $managerPidPath = $this->config["server"]["managerPidPath"] ?? "";

        file_put_contents($masterPidPath, $server->master_pid);
        file_put_contents($managerPidPath, $server->manager_pid);
    }

    public function onStart()
    {
        swoole_set_process_name("sword-master");
    }

    private function createTable()
    {
        $tableSize = intval($this->config["tableSize"] ?? 1024);
        $tableColumns = (array)($this->config["tableColumns"] ?? []);
        $this->table = new \swoole_table($tableSize);
        foreach ($tableColumns as $column) {
            $name = strval($column["name"] ?? "");
            $type = intval($column["type"] ?? \swoole_table::TYPE_STRING);
            $size = intval($column["size"] ?? 4);
            if ($name) {
                $this->table->column($name, $type, $size);
            }
        }
        $this->table->create();
    }
}