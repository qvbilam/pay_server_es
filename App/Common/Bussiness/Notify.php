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
        $status = $data['result_code'] == 'SUCCESS' ? 'bind_succ' : 'bind_fail';
        // todo msg信息整合.
        // 修改数据失败
        if ($upateStatus['code'] != ReturnCode::SUCCESS) {
            $errorMsg = $upateStatus['msg'];
            go(function () use ($errorMsg, $msg) {
                $path = EASYSWOOLE_ROOT . '/Log/wx-error/';
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                file_put_contents($path . date('Y-m-d') . '.log', "[" . date('Y-m-d H:i:s') . "]" . 'msg: ' . $errorMsg . ' result: ' . $msg . "\r\n", FILE_APPEND);
            });
            return $this->response()->write($pay->weChat($this->wechatConfig)->fail());
        }
        // 修改数据成功
        go(function () use ($msg) {
            $path = EASYSWOOLE_ROOT . '/Log/wx-success/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            file_put_contents($path . date("Y-m-d") . '.log', "[" . date('Y-m-d H:i:s') . "]" . ' result: ' . $msg . "\r\n", FILE_APPEND);
        });
        return $this->response()->write($pay->weChat($this->wechatConfig)->success());
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