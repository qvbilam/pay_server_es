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
        if(empty($this->payload['type'])){
            return $this->error('请求失败');
        }
        $data = $this->payload;
        // todo 验证参数
        Wechat::fail();
        WeChat::success();
    }

}