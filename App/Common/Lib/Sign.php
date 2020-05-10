<?php

namespace App\Common\Lib;

class Sign
{
    /*
     * 用户签名算法
     * */
    static public function paySign(array $data, $key)
    {
        ksort($data);
        $md5 = md5(urldecode(http_build_query($data)) . '&key=' . $key);
        $sign = strtoupper($md5);
        return $sign;
    }
}