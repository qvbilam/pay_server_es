<?php

namespace App\HttpController\Api\Validate;

use App\Common\Business\MerchantOrder;
use EasySwoole\Validate\Validate;
use App\Common\Model\Mysql\Merchant as MerchantModel;
use App\Common\Business\Sign;
use App\Common\Business\MerchantOrder as MerchantOrdeModel;

class Pay
{
    /*
     * 支持支付的方式
     * */
    static protected function payType()
    {

        return [
            3011 => ['channel' => 'self_wechat', 'type' => 'scan'],    // 微信扫码
            4011 => ['channel' => 'self_alipay', 'type' => 'scan'],    // 支付宝扫码
        ];
    }

    /*
     * 验证统一下单
     * merchant_id,app_id,app_secret.商户信息
     * pay_type,支付方式
     * trade_no,订单
     * money,钱/分
     * time,时间戳
     * product_id,product_name,货物id,货物名称
     * notify_url,通知地址,不存在不用管.
     * */
    static public function unifiedorder($data)
    {
        $payType = self::payType();
        $valitor = new Validate();
        $valitor->addColumn('merchant_id')->required('merchant_id 不能为空');
        $valitor->addColumn('app_id')->required('app_id 不能为空');
        $valitor->addColumn('body')->required('body 不能为空');
        $valitor->addColumn('pay_type')->required('支付方式不能为空')->inArray(array_keys($payType), false, '支付方式错误');
        $valitor->addColumn('out_trade_no')->required('out_trade_no 不能为空');
        $valitor->addColumn('money')->integer('不是合法的金额');
        $time = $data['time'] + 5 * 60;
        $valitor->addColumn('time')->timestamp('不是合法的时间戳');
        if (time() > $time || time() < $data['time']) {
            throw new \Exception('请求过期');
        }
        if (isset($data['notify_url'])) {
            $valitor->addColumn('notify_url')->url('不是有效的url');
        }
        $valitor->addColumn('sign')->required('sign 不能为空');
        $bool = $valitor->validate($data);
        if ($bool != true) {
            throw new \Exception($valitor->getError()->__toString(), -1);
        }
        $paramSign = $data['sign'];
        unset($data['sign']);
        try {
            $selfSign = (new Sign())->getPaySign($data);
        } catch (\Exception $e) {
            throw new \Exception('签名失败');
        }
        if ($paramSign != $selfSign) {
            throw new \Exception('签名错误:' . $selfSign);
        }
        // 验证商户订单是否重复
        $checkMerchantOrder = (new MerchantOrdeModel)->model->getByConditon(['merchant_id' => $data['merchant_id'], 'merchant_order' => $data['out_trade_no']]);
        if ($checkMerchantOrder) {
            throw new \Exception('商户订单重复');
        }
        return $payType[$data['pay_type']];
    }
}

