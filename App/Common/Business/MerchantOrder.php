<?php

namespace App\Common\Business;

use App\Common\Model\Mysql\MerchantOrder as MerchantOrderModel;
use App\Common\Model\Redis\Order as OrderRedis;

class MerchantOrder extends BusinessBase
{
    public $model;

    public function __construct()
    {
        $this->model = new MerchantOrderModel();
    }

    /*
     * 修改redis订单
     * $timer清除定时器
     * */
    public function updateRedisOrder($merchant_id, $order_id,$data,$timer=false)
    {
        try{
            $res = (new OrderRedis())->updateOrder($merchant_id,$order_id,$data,$timer);
        }catch (\Exception $e){
            return false;
        }
        return $res;
    }

    /*
     * 清除redis的订单
     * */
    public function delRedisOrder($merchant_id, $order_id)
    {
        try {
            $res = (new OrderRedis())->delOrder($merchant_id, $order_id);
        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }


}