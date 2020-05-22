<?php

namespace App\Common\Business;

use App\Common\Model\Redis\Notify as NotifyRedis;
use App\Common\Model\Redis\Order as OrderRedis;


class Notify extends BusinessBase
{
    public function __construct()
    {

    }

    /*
     * 微信
     * 成功调用
     * 以转换成数组
     * [appid] => wxf67e5d6039607945
        [bank_type] => OTHERS
        [cash_fee] => 1
        [fee_type] => CNY
        [is_subscribe] => N
        [mch_id] => 1497029642
        [nonce_str] => nRlOoqV73rKvfe2gASCFjiadLN1Ypczb
        [openid] => oJeEe5T3ahYKTD3vIYYc62-GKxAs
        [out_trade_no] => CN202005111203021287
        [result_code] => SUCCESS
        [return_code] => SUCCESS
        [sign] => DCB99DBA2B41D906643FFDF50ADF8497
        [time_end] => 20200511120330
        [total_fee] => 1
        [trade_type] => NATIVE
        [transaction_id] => 4200000542202005115447613401
     * */
    public function wechat($params)
    {
        try {
            // 添加到系统订单表中
            $ServiceOrderdata = [
                'out_trade_no' => $params['out_trade_no'],
                'transaction_id' => $params['transaction_id'],
                'type' => 1,
                'money' => $params['cash_fee'],
                'return_data' => json_encode($params),
                'result' => $params['result_code'],
                'create_time' => time(),
                'update_time' => time(),
            ];
            $res = (new ServiceOrder())->add($ServiceOrderdata);
            if (!$res) {
                return false;
            }
            // 获取redis信息
            $orderRedisObj = new OrderRedis();
            // 通过系统内部生成的订单号查询商户订单详情
            $merchantOrderInfo = $orderRedisObj->getOrderInfoByTranId($params['out_trade_no']);
            print_r($merchantOrderInfo);
            // 商户订单请求存在
            if ($merchantOrderInfo) {
                $merchantOrderInfo['result'] = $params['result_code'] == 'SUCCESS' ? 'success' : 'fail';
                $merchantOrderInfo['update_time'] = time();
                // 更新redis,并删除定时中的订单信息
                $orderRedisObj->addOrder($merchantOrderInfo['merchant_id'], $merchantOrderInfo['out_trade_no'], $merchantOrderInfo, -1);
                // 将结算成功的数据写入到mysql中,将redis中的删除
                go(function () use ($merchantOrderInfo) {
                    $insertData = [
                        'merchant_id' => $merchantOrderInfo['merchant_id'],
                        'merchant_order' => $merchantOrderInfo['out_trade_no'],
                        'pay_method' => $merchantOrderInfo['pay_type'],
                        'pay_channel' => 1,
                        'pay_type' => 1,
                        'money' => $merchantOrderInfo['money'],
                        'notify_url' => isset($merchantOrderInfo['notify_url']) ? $merchantOrderInfo['notify_url'] : '',
                        'notify_type' => 1,
                        'body' => $merchantOrderInfo['body'],
                        'attach_data' => isset($merchantOrderInfo['attach_data']) ? $merchantOrderInfo['attach_data'] : '',
                        'result' => $merchantOrderInfo['result'],
                        'create_time' => $merchantOrderInfo['create_time'],
                        'update_time' => $merchantOrderInfo['update_time'],
                    ];
                    // 添加到mysql中
                    (new MerchantOrder())->add($insertData);
                    // 修改redis中的商户订单信息,并清理定时器中的订单信息
                    (new MerchantOrder())->updateRedisOrder($merchantOrderInfo['merchant_id'], $merchantOrderInfo['out_trade_no'], ['result' => $merchantOrderInfo['result']], true);
                });
                // 需要回调
                if (!empty($merchantOrderInfo['notify_url'])) {
                    go(function () use ($merchantOrderInfo) {
                        // 将回调信息写入到redis
                        (new NotifyRedis())->addNotify($merchantOrderInfo['merchant_id'], $merchantOrderInfo['out_trade_no']);
                    });
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }




}