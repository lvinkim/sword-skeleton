<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/9/25
 * Time: 10:07 PM
 */

namespace App\Tests\Functional;


use Lvinkim\SwordKernel\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class SwordKernelTestCase extends TestCase
{
    /** @var Kernel */
    protected static $kernel;

    /** @var Container */
    protected static $container;

    /**
     * @return Kernel
     * @throws \Exception
     */
    protected function bootKernel()
    {
        if (!(self::$kernel instanceof Kernel)) {
            $settings = require dirname(__DIR__) . "/../config/settings.config.php";
            self::$kernel = new Kernel($settings);
            self::$kernel->dispatchWorkerStart(0);
        }

        return self::$kernel;
    }

    /**
     * @return Container
     * @throws \Exception
     */
    protected function getContainer()
    {
        if (!(self::$container instanceof Container)) {
            self::$container = self::bootKernel()->getContainer();
        }

        return self::$container;
    }
}