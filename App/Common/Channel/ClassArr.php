<?php

namespace App\Common\Channel;

class ClassArr {

    /**
     * 支付相关
     * @return array
     */
    public static function payClassStat() {
        return [
            "self_wechat" => "App\Common\Channel\Self\Wechat",
            "self_alipay" => "app\lib\pay\Weixin",
            // ......
        ];
    }

    /*
     * $type 对应 paymethodClassState 的key
     * $supportedClass 是 paymethodClassState 的数组
     * $params 传入参数
     * $needInstance 是否需要实例话
     * */
    public static function initClass($type, $classs, $params = [], $needInstance = false) {
        // 如果我们工厂模式调用的方法是静态的，那么我们这个地方返回类库 AliSms
        // 如果不是静态的呢，我们就需要返回 对象
        if(!array_key_exists($type, $classs)) {
            // 处理下撒
            throw new \Exception("类型：{$type} 的类库找不到");
        }
        $className = $classs[$type];

        // new ReflectionClass('A') => 建立A反射类
        // ->newInstanceArgs($args)  => 相当于实例化A对象
        return $needInstance == true ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;

    }
}