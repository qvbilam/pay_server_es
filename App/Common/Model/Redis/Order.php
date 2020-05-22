<?php

namespace App\Common\Model\Redis;

use App\Common\Lib\RedisKey;

/*
 * 全部传入商户订单号
 * */

class Order extends RedisBase
{

    public function getKey()
    {
        $key = RedisKey::getInstance()->getMerchantOrder();
        if (!$key) {
            throw new \Exception('redis key不存在');
        }
        return $key;
    }

    /*
     * 添加服务订单
     * */
    public function addServiceOrder($trand_id, $merchant_id, $order_id)
    {
        $hKey = RedisKey::getInstance()->getServiceOrder();
        $this->redis->hSet($hKey, $trand_id, $merchant_id . '-*-' . $order_id);
    }

    /*
     * 通过商户及订单号获取订单信息
     * */
    public function getOrderInfoByTranId($order_id)
    {
        try {
            // 获取系统底单表中对应的 merchant_id的订单好
            $merchantOrderKey = $this->getKey();
            $serviceKey = RedisKey::getInstance()->getServiceOrder();
            $merchantOrderfield = $this->redis->hGet($serviceKey, $order_id);
            $data = $this->redis->hGet($merchantOrderKey, $merchantOrderfield);
            if ($data) {
                $data = json_decode($data, true);
            }
        } catch (\Exception $e) {
            return [];
        }
        return $data;
    }

    /*
     * 添加订单
     * 哈希存储订单信息
     * */
    public function addOrder($merchant_id, $order_id, $data, $timer = 1)
    {
        $key = $this->getKey();
        $hashKey = $merchant_id . '-*-' . $order_id;
        try {
            switch ($timer) {
                case 0;
                    break;
                case 1:
                    $this->addOrderTimer($hashKey);
                    break;
                case -1:
                    $this->delOrderTimer($hashKey);
                    break;
                default:
                    throw new \Exception('添加订单缓存类型错误');
                    break;
            }
            $res = $this->redis->hSet($key, $hashKey, json_encode($data, true));
        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }

    /*
     * 更新订单
     * 是否清除定时器
     * */
    public function updateOrder($merchant_id, $order_id, $data, $timer = false)
    {
        try {
            $key = $this->getKey();
            $hashKey = $merchant_id . '-*-' . $order_id;
            $get = $this->redis->hGet($key, $hashKey);
            if (!$get) {
                return false;
            }
            $get = json_decode($get, true);
            foreach ($data as $k => $v) {
                $get[$k] = $v;
            }
            $get['update_time'] = time();
            $this->redis->hSet($key, $hashKey, json_encode($get));
            if ($timer) {
                $this->delOrderTimer($hashKey);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /*
     * 删除订单
     * */
    public function delOrder($merchant_id, $order_id)
    {
        $key = $this->getKey();
        $hashKey = $merchant_id . '-*-' . $order_id;
        $this->redis->multi();
        try {
            $this->redis->hDel($key, $hashKey);
            $this->delOrderTimer($hashKey);
        } catch (\Exception $e) {
            $this->redis->discard();
            return false;
        }
        $this->redis->exec();
        return true;
    }

    /*
     * 订单计时器
     * 有序集合存储: score=订单时间 member=$merchant_id . ':' $order_id
     * 超出时间会被自动脚本删除
     * todo 自动脚本
     * */
    public function addOrderTimer($hashKey)
    {
        try {
            $key = RedisKey::getInstance()->getMerchantOrderTimer();
            $res = $this->redis->zAdd($key, time(), $hashKey);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /*
     * 清除订单计时器
     * 说明订单为成功 ｜ 失败
     * */
    public function delOrderTimer($hashKey)
    {
        try {
            $key = RedisKey::getInstance()->getMerchantOrderTimer();
            $res = $this->redis->zRem($key, $hashKey);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }
}