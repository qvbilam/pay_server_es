<?php

namespace App\Common\Model\Redis;

use App\Common\Lib\RedisKey;

/*
 * 全部传入商户订单号
 * */

class Pay extends RedisBase
{

    public function getKey()
    {
        $key = RedisKey::getInstance()->getMerchantPayNum();
        if (!$key) {
            throw new \Exception('redis key不存在');
        }
        return $key;
    }

    public function checkMerchantPayNum($merchantId)
    {
        try {
            $key = $this->getKey();
            $merchantKey = $key . $merchantId;
            $merchantNum = $this->redis->get($merchantKey);
            if($merchantNum > 5){
                throw new \Exception('请求频繁');
            }
        } catch (\Exception $e) {
            throw new \Exception('错误');
        }
        return true;
    }


    public function addMerchantPayNum($merchantId)
    {
        try {
            $key = $this->getKey();
            $expirTime = \Yaconf::get('qvbilam_pay.merchant_order_time') ?: 60;
            $incKey = $key . $merchantId;
            $this->redis->incr($incKey);
            $this->redis->expire($incKey, $expirTime);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}