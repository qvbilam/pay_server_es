<?php
/**
 * Created by PhpStorm.
 * User: qvbilam
 * Date: 2020-05-06
 * Time: 16:28
 */

namespace App\HttpController\Api;

use EasySwoole\Http\AbstractInterface\Controller;
use App\Common\Lib\CodeStatus;
use EasySwoole\Http\Message\Status;

class ApiBase extends Controller
{
    public $params; // 接受get post form data传参
    public $payload; // body 包含request payload等传参

    // 相当于拦截器
    protected function onRequest(?string $action): ?bool
    {
        // 通过验证执行下面逻辑
        $this->getParmas();
        $this->getPayload();
        return true;
    }

    // 接口成功返回
    public function success($result = [], $status = '', $msg = '')
    {
        $status = (!empty($status)) ? $status : CodeStatus::SUCCESS;
        $msg = (!empty($msg)) ? $msg : CodeStatus::getReasonPhrase(CodeStatus::SUCCESS);
        $this->response()->write(json_encode([
            'status' => $status,
            'message' => $msg,
            'result' => $result,
        ]));
    }

    // 请求接口失败返回
    public function error($msg = '', $status = '')
    {
        $status = (!empty($status)) ? $status : CodeStatus::INVALID;
        $msg = (!empty($msg)) ? $msg : CodeStatus::getReasonPhrase(CodeStatus::INVALID);
        $this->response()->write(json_encode([
            'status' => $status,
            'message' => $msg,
        ]));
        $this->response()->end();
    }


    /*获取参数值*/
    public function getParmas()
    {
        $params = $this->request()->getRequestParam();
        $params['page'] = !empty($params['page']) ? intval($params['page']) : intval(\Yaconf::get('qvbilam_shop_es.default_page'));
        $params['size'] = !empty($params['size']) ? intval($params['size']) : intval(\Yaconf::get('qvbilam_shop_es.default_size'));
        // $params['from'] = ($params['page'] - 1) * $params['size']; limit用.mysqlidb包用不到.
        $this->params = $params;
    }

    /*获取body内的传参数*/
    public function getPayload()
    {
        $payload = $this->request()->getBody()->__toString();
        if (empty($payload)) {
            $this->payload = $payload;
            return true;
        }
        $this->payload = json_decode($payload, true);
    }


    // 自定义异常处理.控制器级别
    public function onException(\Throwable $throwable): void
    {

        $this->response()->withStatus(Status::CODE_BAD_REQUEST);
        $this->response()->write(json_encode([
            'code' => $throwable->getCode(),
            'msg' => $throwable->getMessage(),
        ]));
        $this->response()->end();
    }
}