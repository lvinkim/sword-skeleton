<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/9/25
 * Time: 9:24 PM
 */

namespace App\Service;


use Lvinkim\SwordKernel\Component\ServiceInterface;
use Symfony\Component\DependencyInjection\Container;

class ExampleService implements ServiceInterface
{
    private $settings;

    /**
     * $container 包含了所有已实例化的 Service 对象和 Action 对象
     * ActionInterface constructor.
     * @param Container $container
     * @param $settings array
     */
    public function __construct(Container $container, array $settings)
    {
        $this->settings = $settings;
    }

    public function getAppName()
    {
        return $this->settings["app"];
    }
}