<?php

namespace App\HttpController\Api;

use EasySwoole\Pay\WeChat\WeChat;
use App\Common\Channel\Self\Wechat as WechatChannel;

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
        $content = $this->request()->getBody()->__toString();
        print_r($content);
        echo '验证参数:' . PHP_EOL;
        (new WechatChannel())->verify($content);
        echo '支付结果转array' . PHP_EOL;
        $arr  = WechatChannel::XmlToArray($content);
        print_r($arr);
//        if(empty($this->payload['type'])){
//            return $this->error('请求失败');
//        }
//        $data = $this->payload;
        // todo 验证参数
//        Wechat::fail();
//        WeChat::success();
    }

}