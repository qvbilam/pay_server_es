<?php
namespace EasySwoole\EasySwoole;


use App\Process\ExpireRedis;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Component\Di;
use EasySwoole\Redis\Redis;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\Process\Config as ProcessConfig;


class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        // 热加载进程
        Di::getInstance()->set('MYSQL', \MysqliDb::class, \Yaconf::get('qvbilam_pay.mysql_connect'));
        Di::getInstance()->set('REDIS', Redis::class, new RedisConfig(\Yaconf::get('qvbilam_pay.redis_connect')));
        $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
        $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
        $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);
        $server = ServerManager::getInstance()->getSwooleServer();
        $hotReload->attachToServer($server);
        $processConfig = new ProcessConfig(); // 后续每个进程使用命进程名
        // 处理redis操作
        $expireRedisConfig = $processConfig->addProperty('expireRedisProcess');
        Manager::getInstance()->addProcess(new ExpireRedis($expireRedisConfig));
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}