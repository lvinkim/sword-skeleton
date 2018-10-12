<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 24/09/2018
 * Time: 7:20 PM
 */

require dirname(__DIR__) . "/server/Command.php";
require dirname(__DIR__) . "/server/HttpServer.php";

$settings = require dirname(__DIR__) . "/config/kernel.config.php";

(new \App\Server\Command($settings))->run();
