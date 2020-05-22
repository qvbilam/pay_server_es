<?php

namespace App\Common\Model\Redis;

use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;
use App\Common\Lib\CodeStatus;
use EasySwoole\Redis\Redis;

class RedisBase
{
    use Singleton;

    public $redis;

    public function __construct(...$data)
    {
        /*判断有没有安装redis拓展*/
        if (!extension_loaded('redis')) {
            throw new \Exception(CodeStatus::getReasonPhrase(CodeStatus::REDIS_LOADED_ERROR));
        }
        try {
            $redis = Di::getInstance()->get('REDIS');
            if ($redis instanceof Redis) {
                $this->redis = $redis;
            } else {
                $redisConf = new \EasySwoole\Redis\Config\RedisConfig(\Yaconf::get('qvbilam_pay.redis_connect'));
                $this->redis = new Redis($redisConf);
            }
        } catch (\Exception $e) {
            throw new \Exception(CodeStatus::getReasonPhrase(CodeStatus::REDIS_CONNCE_ERROR));
        }
        if (!$this->redis) {
            throw new \Exception(CodeStatus::getReasonPhrase(CodeStatus::REDIS_CONNCE_ERROR));
        }
    }

}