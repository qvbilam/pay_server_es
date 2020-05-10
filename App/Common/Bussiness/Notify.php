<?php

namespace App\Common\Bussiness;

use EasySwoole\Pay\Pay;


class Notify
{
    /*
     * 微信
     * */
    public function wechat($params)
    {
        $content = $params;
        $pay = new Pay();
        $data = $pay->weChat($this->wechatConfig)->verify($content);
        $msg = $data->__toString();
        // $status = $data['result_code'] == 'SUCCESS' ? 'bind_succ' : 'bind_fail';

    }

    public static function XmlToArray($xml)
    {
        if (!$xml) {
            throw new \Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }
}