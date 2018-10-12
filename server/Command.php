<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 03/07/2018
 * Time: 11:11 PM
 */

namespace App\Server;

class Command
{
    /**
     * These words will be as a Boolean value
     */
    const TRUE_WORDS = '|on|yes|true|';
    const FALSE_WORDS = '|off|no|false|';

    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function run($argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }
        $script = array_shift($argv);
        list($args, $shortOpts, $longOpts) = $this->parse($argv);

        if (!isset($args[0])) {
            $this->listCommand();
        } else {
            switch ($args[0]) {
                case 'swoole:start':
                    $this->startCommand();
                    break;
                case 'swoole:reload':
                    $this->reloadCommand();
                    break;
                case 'swoole:stop':
                    $this->stopCommand();
                    break;
                default:
                    $this->errorCommand($args[0]);
                    break;
            }
        }
    }

    private function listCommand()
    {
        echo("swoole:start\t启动服务" . PHP_EOL);
        echo("swoole:reload\t重载服务" . PHP_EOL);
        echo("swoole:stop\t停止服务" . PHP_EOL);
    }

    private function startCommand()
    {
        echo("启动服务" . PHP_EOL);
        $httpKernel = new HttpServer($this->settings);
        $httpKernel->run();
    }

    private function reloadCommand()
    {
        echo("正在重载服务" . PHP_EOL);

        $managerPidPath = $this->settings["server"]["managerPidPath"] ?? "";
        if (is_readable($managerPidPath)) {
            $managerPid = file_get_contents($managerPidPath);
        } else {
            $managerPid = 0;
        }

        if ($managerPid) {
            posix_kill($managerPid, SIGUSR1);
            echo("重载服务完成" . PHP_EOL);
        } else {
            echo("重载服务失败" . PHP_EOL);
        }

    }

    private function stopCommand()
    {
        echo("正在停止服务" . PHP_EOL);

        $masterPidPath = $this->settings["server"]["masterPidPath"] ?? "";
        if (is_readable($masterPidPath)) {
            $masterPid = file_get_contents($masterPidPath);
        } else {
            $masterPid = 0;
        }

        $timeout = 60;
        $startTime = time();
        $masterPid && posix_kill($masterPid, SIGTERM);

        $stoped = true;
        while (1) {
            $masterIsAlive = $masterPid && posix_kill($masterPid, SIGTERM);
            if ($masterIsAlive) {
                if (time() - $startTime >= $timeout) {
                    $stoped = false;
                    break;
                }
                usleep(100000);
                continue;
            }
            break;
        }
        if ($stoped) {
            echo('停止服务成功' . PHP_EOL);

            if (is_writable($masterPidPath)) {
                file_put_contents($masterPidPath, 0);
            }

            $managerPidPath = $this->settings["server"]["managerPidPath"] ?? "";
            if (is_writable($managerPidPath)) {
                file_put_contents($managerPidPath, 0);
            }

        } else {
            echo('停止服务失败' . PHP_EOL);
        }
    }

    private function errorCommand($arg)
    {
        echo("命令 {$arg} 不存在" . PHP_EOL);

    }

    private function parse(array $params, array $config = []): array
    {
        $config = array_merge([
            // List of parameters without values(bool option keys)
            'noValues' => [], // ['debug', 'h']
            // Whether merge short-opts and long-opts
            'mergeOpts' => false,
            // list of params allow array.
            'arrayValues' => [], // ['names', 'status']
        ], $config);

        $args = $sOpts = $lOpts = [];
        $noValues = array_flip((array)$config['noValues']);
        $arrayValues = array_flip((array)$config['arrayValues']);

        // each() will deprecated at 7.2. so,there use current and next instead it.
        // while (list(,$p) = each($params)) {
        while (false !== ($p = current($params))) {
            next($params);

            // is options
            if ($p{0} === '-') {
                $val = true;
                $opt = substr($p, 1);
                $isLong = false;

                // long-opt: (--<opt>)
                if ($opt{0} === '-') {
                    $opt = substr($opt, 1);
                    $isLong = true;

                    // long-opt: value specified inline (--<opt>=<value>)
                    if (strpos($opt, '=') !== false) {
                        list($opt, $val) = explode('=', $opt, 2);
                    }

                    // short-opt: value specified inline (-<opt>=<value>)
                } elseif (isset($opt{1}) && $opt{1} === '=') {
                    list($opt, $val) = explode('=', $opt, 2);
                }

                // check if next parameter is a descriptor or a value
                $nxt = current($params);

                // next elem is value. fix: allow empty string ''
                if ($val === true && !isset($noValues[$opt]) && $this->nextIsValue($nxt)) {
                    // list(,$val) = each($params);
                    $val = $nxt;
                    next($params);

                    // short-opt: bool opts. like -e -abc
                } elseif (!$isLong && $val === true) {
                    foreach (str_split($opt) as $char) {
                        $sOpts[$char] = true;
                    }

                    continue;
                }

                $val = $this->filterBool($val);
                $isArray = isset($arrayValues[$opt]);

                if ($isLong) {
                    if ($isArray) {
                        $lOpts[$opt][] = $val;
                    } else {
                        $lOpts[$opt] = $val;
                    }
                } else {
                    if ($isArray) {
                        $sOpts[$opt][] = $val;
                    } else {
                        $sOpts[$opt] = $val;
                    }
                }

                // arguments: param doesn't belong to any option, define it is args
            } else {
                // value specified inline (<arg>=<value>)
                if (strpos($p, '=') !== false) {
                    list($name, $val) = explode('=', $p, 2);
                    $args[$name] = $this->filterBool($val);
                } else {
                    $args[] = $p;
                }
            }
        }

        if ($config['mergeOpts']) {
            return [$args, array_merge($sOpts, $lOpts)];
        }

        return [$args, $sOpts, $lOpts];
    }

    /**
     * @param string|bool $val
     * @param bool $enable
     * @return bool|mixed
     */
    private function filterBool($val, $enable = true)
    {
        if ($enable) {
            if (\is_bool($val) || is_numeric($val)) {
                return $val;
            }

            // check it is a bool value.
            if (false !== stripos(self::TRUE_WORDS, "|$val|")) {
                return true;
            }

            if (false !== stripos(self::FALSE_WORDS, "|$val|")) {
                return false;
            }
        }

        return $val;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    private function nextIsValue($val): bool
    {
        // current() fetch error, will return FALSE
        if ($val === false) {
            return false;
        }

        // if is: '', 0
        if (!$val) {
            return true;
        }

        // it isn't option or named argument
        return $val{0} !== '-' && false === strpos($val, '=');
    }
}