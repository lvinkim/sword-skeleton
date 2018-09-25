<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/9/25
 * Time: 8:24 PM
 */

return (function () {

    return [
        "kernel" => "\Lvinkim\SwordKernel\Kernel",
        "vendor" => dirname(__DIR__) . "/vendor/autoload.php",
        "settings" => __DIR__ . "/settings.config.php",
        "swoole" => [

        ],
        "server" => [
            "host" => "0.0.0.0",
            "port" => "8080",
        ],
    ];

})();
