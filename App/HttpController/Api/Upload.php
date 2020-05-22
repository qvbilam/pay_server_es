<?php

namespace App\HttpController\Api;

use App\Common\Model\Redis\RedisBase;

class Upload extends ApiBase
{
    protected $chunckSize = 1 * 1024 * 1024; // 分块的大小/Mb

    // 初始化分块上传
    public function init()
    {
        // 1.解析请求信息
        $userName = $this->params['user_name'];
        $fileHash = $this->params['file_hash'];
        $fileSize = $this->params['file_size'];
        // 3. 生成分块上传初始化信息
        $res = [
            'upload_id' => $userName . '-' . time(), // 上传id,没次上传不一样
            'file_hash' => $fileHash, // 文件hash值
            'file_size' => $fileSize, // 文件大小
            'chunck_size' => $this->chunckSize, // 文件块的大小
            'chunck_count' => ceil($fileSize / $this->chunckSize), // 文件块总数量
        ];
        // 4.初始化信息写入到redis中
        $redis = RedisBase::getInstance()->redis;
        foreach ($res as $k => $v) {
            $redis->hSet("File_" . $res['upload_id'], $k, $v);
        }
        $redis->hSet("File_" . $res['upload_id'], 'success_chunck', '');
        // 5.初始化信息返回给客户端
        return $this->success($res);
    }

    public function chunckUpload()
    {
        // 1. 解析请求
        $userName = $this->params['user_name'];
        $uploadId = $this->params['upload_id'];
        $fileUserPath = $this->params['file_path'];
        $fileName = $this->params['file_name'];
        $filePath = EASYSWOOLE_ROOT . "/Upload/$userName/" . trim($fileUserPath, '/') . "/chunck/";
        if (!file_exists(iconv("UTF-8", "GBK", $filePath))) {
            mkdir($filePath, 0744, true);
        }
        $file = $this->request()->getUploadedFile('upload_file'); // 获取上传文件
        $flag = $file->moveTo($filePath . $fileName);
        if (!$flag) {
            return $this->error('上传失败');
        }
        return $this->success();
    }

    // 上传合并
    public function completeUpload()
    {
        // 1. 解析请求
        $userName = $this->params['user_name'];
        $fileHash = $this->params['file_hash'];
        $fileSize = $this->params['file_size'];
        $uploadId = $this->params['upload_id'];
        $fileName = $this->params['file_name'];
        // 2. 获得redis
        // 3. 通过upload_id查询redis判断所有分块是否完成
        $redis = RedisBase::getInstance()->redis;
        $res = $redis->hGetAll("File_" . $uploadId);
        if (!$res) {
            return $this->error('上传失败');
        }
        $chunckNumString = $res['chunck_number'];
        if (empty($chunckNumString)) {
            return $this->error('分块全部没上传成功');
        }
        $chunckNum = explode(',', $chunckNumString);
        if (count($chunckNum) != $res['chunck_count']) {
            return $this->error('分块上传失败');
        }
        // 4. 合并分块
        // 6. 响应结果
        return $this->success();
    }

    public function test()
    {
        $file = EASYSWOOLE_ROOT . '/App/common/View/upload/test.html';
        $this->response()->write(file_get_contents($file));
    }
}