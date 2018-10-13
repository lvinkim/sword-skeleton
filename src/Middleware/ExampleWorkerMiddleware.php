<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/10/13
 * Time: 2:04 PM
 */

namespace App\Middleware;


use Lvinkim\SwordKernel\Component\WorkerMiddlewareInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * 示例类，可随时删除
 * Class ExampleWorkerMiddleware
 * @package App\Middleware
 */
class ExampleWorkerMiddleware implements WorkerMiddlewareInterface
{

    /**
     * $container 包含了所有已实例化的 Service 对象
     * WorkerMiddlewareInterface constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {

    }

    /**
     * @param $settings
     */
    public function __invoke($settings)
    {
        $workerId = $settings["workerId"] ?? 0;
        echo "worker {$workerId} - " . __METHOD__ . PHP_EOL;
    }

    /**
     * 执行顺序，值越大，优先级越高
     * @return int
     */
    public function priority(): int
    {
        return 10;
    }
}