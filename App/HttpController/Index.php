<?php


namespace App\HttpController;


use App\Common\Model\Redis\RedisBase;
use App\HttpController\Channel\PayType\Wechat;
use EasySwoole\Component\Di;
use EasySwoole\Http\AbstractInterface\Controller;
use App\HttpController\Api\ApiBase;

class Index extends Controller
{

    public function index()
    {
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    public function testnotify()
    {
        echo '收到json消息' . PHP_EOL;
        $data = $this->request()->getBody()->__toString();
        echo $data . PHP_EOL;
        $this->response()->write(json_encode(['code' => 0, 'msg' => 'ok']));
    }

    public function redis()
    {
//        $res = Di::getInstance()->get('REDIS')->get('angel');
        $res = RedisBase::getInstance()->redis->get('angel');
        $this->response()->write($res);
    }


}