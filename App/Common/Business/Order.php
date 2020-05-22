<?php

namespace App\Common\Business;

use App\Common\Model\Mysql\MerchantOrder as MerchantOrderModel;

class Order extends BusinessBase
{
    public $model;

    public function __construct()
    {
        $this->model = new MerchantOrderModel();
    }

    /*
    * 查询订单
    * */
    public function orderquery()
    {

    }

    /*
     * 订单退款
     * */
    public function refund()
    {

    }

    /*
     * 退款查询
     * */
    public function refundquery()
    {


    }


}