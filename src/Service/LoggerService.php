<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/10/13
 * Time: 3:50 PM
 */

namespace App\Service;


use Lvinkim\SwordKernel\Component\ServiceInterface;
use Swoole\Coroutine;
use Symfony\Component\DependencyInjection\Container;

class LoggerService implements ServiceInterface
{
    /** @var string */
    private $logsDir;

    /** @var bool */
    private $debug;

    private $workerId;

    /**
     * $container 包含了所有已实例化的 Service 对象和 Action 对象
     * ActionInterface constructor.
     * @param Container $container
     * @param $settings array
     */
    public function __construct(Container $container, array $settings)
    {
        $this->logsDir = $settings["logsDir"] ?? "/tmp";
        $this->debug = $settings["debug"] ?? false;
        $this->workerId = $settings["workerId"] ?? 0;
    }

    /**
     * 调试日志
     * @param string $name
     * @param array $doc
     */
    public function debug(string $name, array $doc = [])
    {
        if ($this->debug) {
            $this->info($name, $doc);
        }
    }

    /**
     * @param string $name
     * @param array $doc
     */
    public function info(string $name, array $doc = [])
    {
        go(function () use ($name, $doc) {

            $logFile = $this->logsDir . "/{$name}.log." . date("Y-m-d");

            Coroutine::writeFile($logFile, json_encode(
                    array_merge($doc, [
                        "worker" => $this->workerId,
                        "uid" => Coroutine::getuid(),
                        "stats" => Coroutine::stats(),
                        "datetime" => date("Y-m-d H:i:s"),
                        "node" => getenv("NODE") ?: gethostname(),
                    ])
                ) . PHP_EOL);
        });
    }

}