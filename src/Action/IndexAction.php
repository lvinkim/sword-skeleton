<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/9/25
 * Time: 9:53 PM
 */

namespace App\Action;


use App\Service\ExampleService;
use Lvinkim\SwordKernel\Component\ActionInterface;
use Lvinkim\SwordKernel\Component\ActionResponse;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\DependencyInjection\Container;

class IndexAction implements ActionInterface
{

    /** @var ExampleService */
    private $exampleService;

    /**
     * $container 包含了所有已实例化的 Service 对象和 Action 对象
     * ActionInterface constructor.
     * @param Container $container
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->exampleService = $container->get(ExampleService::class);
    }

    /**
     * Action 类入口函数
     * @param Request $request
     * @param Response $response
     * @param $settings
     * @return ActionResponse
     */
    public function __invoke(Request $request, Response $response, array $settings): ActionResponse
    {
        return new ActionResponse(json_encode(["app" => $this->exampleService->getAppName()]));
    }
}