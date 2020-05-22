<?php

namespace App\HttpController\Api;

use Endroid\QrCode\QrCode;
use App\Common\Business\Pay as PayBussiness;

/*
 * 对客户接口
 * */
class Pay extends ApiBase
{
    /*
     * 统一下单
     * */
    public function unifiedorder()
    {
        $res = (new PayBussiness())->scan($this->payload);
        return $this->success($res);
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

    /*
     * 生成二维码
     * */
    public function scan()
    {
        $data = $this->params['data'] ?: '';
        $qrcode = (new QrCode($data));
        $this->response()->withHeader('Content-type', $qrcode->getContentType());
        $this->response()->write($qrcode->writeString());
    }
}