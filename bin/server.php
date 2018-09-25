<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 24/09/2018
 * Time: 7:20 PM
 */

require dirname(__DIR__) . "/server/HttpServer.php";

$setting = require dirname(__DIR__) . "/config/kernel.config.php";

$httpServer = new \Server\HttpServer($setting);

$httpServer->run();
