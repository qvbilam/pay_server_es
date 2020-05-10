<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        // 支付二维码
        $routeCollector->addRoute(['GET', 'POST'], \Yaconf::get('qvbilam_pay.route.pay_qrcode'), '/Api/Pay/scan');
        // 微信回调地址
        $routeCollector->addRoute(['GET', 'POST'], '/notify/{type}', '/Api/Notify/acceptPayNotify');

    }
}