<?php

namespace App\Common\Lib;

use EasySwoole\Component\Singleton;

class RedisKey
{
    use Singleton;

    public $prefix;

    public function __construct()
    {
        $this->prefix = \Yaconf::get('qvbilam_pay.redis_conf.prefix') ?: '';
    }

    /*
     * 获取异步支付结果通知key
     * */
    public function getNotifyPayResult()
    {
        $prefix = $this->prefix;
        $key = \Yaconf::get('qvbilam_pay.redis_key.notify_pay_result');
        if (empty($key)) {
            throw new \Exception('获取key失败');
        }
        return $prefix . $key;
    }
    /*
     * 获取商户交易次数
     * */
    public function getMerchantPayNum()
    {
        $prefix = $this->prefix;
        $key = \Yaconf::get('qvbilam_pay.redis_key.merchant_pay_num');
        if (empty($key)) {
            throw new \Exception('获取key失败');
        }
        return $prefix . $key;
    }


    /*
     * 获取商户订单有效时间
     * */
    public function getMerchantOrderValidTime()
    {
        $m = \Yaconf::get('qvbilam_pay.redis_conf.merchant_order_timer') ?: 60;
        $time = $m * 60;
        return $time;
    }

    /*
     * 获取商户订单
     * */
    public function getMerchantOrder()
    {
        $prefix = $this->prefix;
        $key = \Yaconf::get('qvbilam_pay.redis_key.merchant_order');
        if (empty($key)) {
            throw new \Exception('获取key失败');
        }
        return $prefix . $key;
    }

    /*
     * 获取商户订单定时器
     * */
    public function getMerchantOrderTimer()
    {
        $prefix = $this->prefix;
        $key = \Yaconf::get('qvbilam_pay.redis_key.merchant_order_timer');
        if (empty($key)) {
            throw new \Exception('获取key失败');
        }
        return $prefix . $key;
    }

    /*
     * 获取系统订单
     * */
    public function getServiceOrder()
    {
        $prefix = $this->prefix;
        $key = \Yaconf::get('qvbilam_pay.redis_key.service_order');
        if (empty($key)) {
            throw new \Exception('获取key失败');
        }
        return $prefix . $key;
    }

}