<?php

namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use App\Common\Model\Redis\RedisBase;
use App\Common\Lib\RedisKey;
use App\Common\Business\Sign;
use Ixudra\Curl\CurlService;
use EasySwoole\Component\Di;
use App\Common\Business\Notify;

class ExpireRedis extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            while (1){
                $this->payResNotifyToMerchantay();
                sleep(1);
            }

        });
    }

    protected function payResNotifyToMerchantay()
    {
        $redis = Di::getInstance()->get('REDIS');
        $notifyKey = RedisKey::getInstance()->getNotifyPayResult();
        $merchantPayNotify = $redis->zRangeByScore($notifyKey, 0, time(), ['withscores' => TRUE]);
        foreach ($merchantPayNotify as $value) {
            $merchantOrderKey = RedisKey::getInstance()->getMerchantOrder();
            $data = RedisBase::getInstance()->redis->hGet($merchantOrderKey, $value);
            if (!$data) {
                continue;
            }
            $data = json_decode($data, true);
            $curlData = [
                'merchant_id' => $data['merchant_id'],
                'app_id' => $data['app_id'],
                'pay_type' => $data['pay_type'],
                'out_trade_no' => $data['out_trade_no'],
                'transaction_id' => $data['transaction_id'],
                'money' => $data['money'],
                'time' => time(),
                'body' => $data['body'],
            ];
            if (isset($data['attach_data'])) {
                $curlData['attach_data'] = $data['attach_data'];
            }
            $curlData['sign'] = (new Sign())->getPaySign($curlData);
            $this->ResPayResNotifyToMerchant($data['notify_url'], $curlData);
        }
        var_dump('my task run at ' . time());
    }


    /*
     * 处理对商户通知
     * 默认post | json 传输
     * */
    protected function ResPayResNotifyToMerchant($url, $data, $requstType = 'post')
    {
        $curlParam = [
            'action' => $requstType == 'post' ? 'post' : 'get',
            'url' => $url,
            'data' => $data,
        ];
        go(function () use ($curlParam) {
            $redis = Di::getInstance()->get('REDIS');
            $curlService = new CurlService();
            $action = $curlParam['action'];
            $res = $curlService->to($curlParam['url'])
                ->withData($curlParam['data'])
                ->asJson(true)
                ->$action();
            // 商户返回信息
            if (!$res) {
                return false;
            }
            if (!$res || $res['code'] != 0) {
                return false;
            }
            // 删除通知
            $key = RedisKey::getInstance()->getNotifyPayResult();
            // todo 完善需要注意组装对二维数组
            $redis->zRem($key, $curlParam['data']['merchant_id'] . '-*-' . $curlParam['data']['out_trade_no']);
        });
    }




}