<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/10/13
 * Time: 3:45 PM
 */

namespace App\Middleware\Request;


use App\Service\LoggerService;
use Lvinkim\SwordKernel\Component\ActionResponse;
use Lvinkim\SwordKernel\Component\RequestMiddlewareInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;
use Symfony\Component\DependencyInjection\Container;

/**
 * 访问日志
 * Class AccessMiddleware
 * @package App\Middleware\Request
 */
class AccessMiddleware implements RequestMiddlewareInterface
{
    /** @var LoggerService */
    private $logger;

    /**
     * $container 包含了所有已实例化的 Service 对象
     * RequestMiddlewareInterface constructor.
     * @param Container $container
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->logger = $container->get(LoggerService::class);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $settings
     * @param Table $table
     */
    public function before(Request $request, Response $response, $settings, Table $table)
    {
        $this->logger->info("access", [
            "fd" => $request->fd,
            "ip" => $request->server["remote_addr"] ?? "",
            "method" => $request->server["request_method"] ?? "",
            "path" => $request->server["path_info"] ?? "/",
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $settings
     * @param Table $table
     * @param ActionResponse $actionResponse
     */
    public function after(Request $request, Response $response, $settings, Table $table, ActionResponse $actionResponse)
    {
        if ($actionResponse->getStatusCode() > 400) {
            $this->logger->info("error", [
                "fd" => $request->fd,
                "ip" => $request->server["remote_addr"] ?? "",
                "method" => $request->server["request_method"] ?? "",
                "path" => $request->server["path_info"] ?? "/",
                "status" => $actionResponse->getStatusCode(),
                "get" => $request->get,
                "post" => $request->post,
                "raw" => $request->rawcontent(),
            ]);
        }
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