<?php

namespace App\Common\Bussiness;

use App\Common\Model\Mysql\Merchant as MerchantModel;
use App\Common\Lib\Sign as SignFactory;

class Sign
{
    /*
     * 获取支付sign
     * */
    public function getPaySign($data)
    {
        $merchantInfo = (new MerchantModel())->getByConditon([
            'merchant_id' => [$data['merchant_id'], '='],
            'app_id' => [$data['app_id'], '='],
        ], 'status,app_secret', 1);
        if (empty($merchantInfo)) {
            echo (new MerchantModel())->db->getLastQuery() . PHP_EOL;
            throw new \Exception('商户不存在', -1);
        }
        if ($merchantInfo['status'] <= 0) {
            throw new \Exception('用户已被禁用', -1);
        }
        $sign = SignFactory::paySign($data, $merchantInfo['app_secret']);
        return $sign;
    }

}