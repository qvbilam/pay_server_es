<?php

namespace App\HttpController\Api;

use EasySwoole\Pay\WeChat\WeChat;

class Notify extends ApiBase
{

    /*
     * 接受支付回调地址
     * */
    public function acceptPayNotify()
    {
        echo '支付回调:paramas参数' . PHP_EOL;
        print_r($this->params);
        echo '支付回调:payload参数' . PHP_EOL;
        print_r($this->payload);
//        if(empty($this->payload['type'])){
//            return $this->error('请求失败');
//        }
//        $data = $this->payload;
        // todo 验证参数
//        Wechat::fail();
//        WeChat::success();
    }

}