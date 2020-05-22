<?php

namespace App\Common\Model\Redis;

use App\Common\Lib\RedisKey;

/*
 * 全部传入商户订单号
 * */

class Notify extends RedisBase
{

    public function getKey()
    {
        $key = RedisKey::getInstance()->getNotifyPayResult();
        if (!$key) {
            throw new \Exception('redis key不存在');
        }
        return $key;
    }

    public function addNotify($merchant_id, $order_id)
    {
        try {
            $key = $this->getKey();
            $zKey = $merchant_id . '-*-' . $order_id;
            $this->redis->zAdd($key, time(), $zKey);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}