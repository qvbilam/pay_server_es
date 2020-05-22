<?php

namespace App\HttpController\Api;

use EasySwoole\Pay\WeChat\WeChat;
use App\Common\Business\Notify as NotifyBusiness;
use App\Common\Channel\Self\Wechat as WechatChannel;

class Notify extends ApiBase
{

    /*
     * 接受支付回调地址
     * */
    public function acceptPayNotify()
    {
        $content = $this->request()->getBody()->__toString();
        // todo 根据类型走方法.
        $this->wxResponse($content);
    }

    /*
     * 发送通知
     * */

    /*
     * 微信返回
     * */
    protected function wxResponse($params)
    {
        $content = $params;
        // 验证参数
        // $wechatChannelObj = new WechatChannel();
        // $wechatChannelObj->verify($content);
        $data = WechatChannel::XmlToArray($content);
        if (!isset($data['result_code']) || !isset($data['return_code']) || $data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
            return Wechat::fail();
        }
        try {
            $res = (new NotifyBusiness)->wechat($data);
        } catch (\Exception $e) {
            return Wechat::fail();
        }
        if (!$res) {
            return Wechat::fail();
        }
        return WeChat::success();
    }

}