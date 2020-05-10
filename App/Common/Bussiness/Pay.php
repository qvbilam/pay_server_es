<?php


namespace App\Common\Bussiness;

use App\HttpController\Api\Validate\Pay as PayValidate;
use App\Common\Channel\ClassArr;

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
        $payType = PayValidate::unifiedorder($params);
        // 调用不同的支付
        $payClass = ClassArr::payClassStat();
        $payObj = ClassArr::initClass($payType['channel'], $payClass, [], true);
        $functionName = $payType['type'];
        $codeUrl = $payObj->$functionName($params['money'], $tradeNo, $params['body'], $attach);
        $data['code_url'] = $codeUrl;
        $data['sign'] = (new Sign())->getPaySign($data);
        return $data;
    }

}