<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/9/25
 * Time: 10:15 PM
 */

namespace App\Tests\Functional\ExampleService;


use App\Service\ExampleService;
use App\Tests\Functional\SwordKernelTestCase;

class ExampleServiceTest extends SwordKernelTestCase
{

    /** @var ExampleService */
    private $exampleService;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $this->exampleService = self::getContainer()->get(ExampleService::class);
    }

    public function testGetAppName()
    {
        $appName = $this->exampleService->getAppName();
        $this->assertTrue(is_string($appName));
    }
}