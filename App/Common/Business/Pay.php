<?php


namespace App\Common\Business;

use App\Common\Model\Redis\RedisBase;
use App\HttpController\Api\Validate\Pay as PayValidate;
use App\Common\Channel\ClassArr;
use App\Common\Model\Redis\Order as OrderRedis;
use App\Common\Model\Redis\Pay as PayRedis;

class Pay
{

    /*
     * 扫码支付
     * */
    public function scan($params)
    {
        mt_srand();
        $tradeNo = 'CN' . date('YmdHis') . mt_rand(1000, 9999);
        $data = [
            'merchant_id' => $params['merchant_id'],
            'app_id' => $params['app_id'],
            'pay_type' => $params['pay_type'],
            'money' => $params['money'],
            'out_trade_no' => $params['out_trade_no'],
            'transaction_id' => $tradeNo,
            'body' => $params['body'],
        ];
        if (!empty($params['attach'])) {
            $attach = $data['attach'] = $params['attach'];
        } else {
            $attach = '';
        }
        if (!empty($params['notify_url'])) {
            $data['notify_url'] = $params['notify_url'];
        }
        $payType = PayValidate::unifiedorder($params);
        // 调用不同的支付
        $payClass = ClassArr::payClassStat();
        $payObj = ClassArr::initClass($payType['channel'], $payClass, [], true);
        $functionName = $payType['type'];
        $codeUrl = $payObj->$functionName($params['money'], $tradeNo, $params['body'], $attach);
        $data['code_url'] = $codeUrl;
        $params['transaction_id'] = $tradeNo;
        go(function () use ($params) {
            $params['result'] = 'wait';
            $params['create_time'] = time();
            // 系统 - 商户号-*-订单缓存呢
            (new OrderRedis())->addServiceOrder($params['transaction_id'],$params['merchant_id'],$params['out_trade_no']);
            // 商户订单缓存
            (new OrderRedis())->addOrder($params['merchant_id'], $params['out_trade_no'], $params, true);
            // 支付次数缓存
            (new PayRedis())->addMerchantPayNum($params['merchant_id']);
        });
        return $data;
    }

}