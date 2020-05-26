<?php

namespace App\HttpController\Api;

use App\Common\Model\Redis\RedisBase;

class Upload extends ApiBase
{
//    protected $chunckSize = 1 * 1024 * 1024; // 分块的大小/Mb
    protected $chunckSize = 100 * 1024; // 分块的大小/Mb

    // 初始化分块上传
    public function init()
    {
        // 1.解析请求信息
        $userName = $this->params['user_name'];
        $fileHash = $this->params['file_hash'];
        $fileSize = $this->params['file_size'];
        $filePath = $this->params['file_path'];
        $fileName = $this->params['file_name'];
        // 3. 生成分块上传初始化信息
        $res = [
            'upload_id' => $userName . '-' . time(), // 上传id,没次上传不一样
            'file_hash' => $fileHash, // 文件hash值
            'file_size' => $fileSize, // 文件大小
            'file_path' => trim($filePath, '/') . '/', // 用户上传目录
            'file_name' => $fileName, // 用户上传文件名称
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

    // 分块上传
    public function chunckUpload()
    {
        // 1. 解析请求
        $userName = $this->params['user_name'];
        $uploadId = $this->params['upload_id'];
        $fileInfo = RedisBase::getInstance()->redis->hGetAll("File_" . $uploadId);
        $fileUserPath = $fileInfo['file_path'] ?: '';
        $fileUserName = $fileInfo['file_name'] ?: '';
        $filePath = EASYSWOOLE_ROOT . "/Upload/$userName/" . $fileUserPath . $fileUserName . ".chunck/";
        if (!file_exists(iconv("UTF-8", "GBK", $filePath))) {
            mkdir($filePath, 0744, true);
        }
        $file = $this->request()->getUploadedFile('upload_file'); // 获取上传文件
        $flag = $file->moveTo($filePath . $file->getClientFilename());
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
        $uploadId = $this->params['upload_id'];
        // 3. 通过upload_id查询redis判断所有分块是否完成
        $redis = RedisBase::getInstance()->redis;
        $fileInfo = $redis->hGetAll("File_" . $uploadId);

        if (!$fileInfo) {
            return $this->error('上传失败');
        }
        $chunckNumString = $fileInfo['chunck_number'];
        if (empty($chunckNumString)) {
            // return $this->error('分块全部没上传成功');
        }
        $chunckNum = explode(',', $chunckNumString);
        if (count($chunckNum) != $fileInfo['chunck_count']) {
            return $this->error('分块上传失败');
        }
        // 4. 合并分块
        $filePath = $fileInfo['file_path'] ?: '';
        $fileName = $fileInfo['file_name'] ?: '';
        $fileSha1 = $this->mergeUserFIle($userName, $fileName, $filePath);
        // 5. 删除分块
        $delFile = $this->delFile($userName, $fileName, $filePath);
        // 6. 响应结果
        if ($fileSha1 != $fileInfo['file_hash']) {
            // return $this->error('');
        }
        if (!$delFile) {

        }
        $redis->del("File_" . $uploadId);
        return $this->success();
    }

    /*
     * 合并用户文件
     * $userName: 用户名
     * $userFileName: 用户文件名
     * $userFilePaht: 用户上传目录
     * */
    protected function mergeUserFIle($userName, $userFileName, $userFilePath)
    {
        $chunckFilePath = EASYSWOOLE_ROOT . "/Upload/$userName/$userFilePath" . $userFileName . ".chunck/";
        $newFilaPath = EASYSWOOLE_ROOT . "/Upload/$userName/$userFilePath";
        $newFile = $newFilaPath . $userFileName;
        // 获取分块的文件名
        $chunckFile = scandir($chunckFilePath);
        foreach ($chunckFile as $value) {
            if ($value != '.' && $value != '..') {
                // 读区文件内容
                $content = fopen($chunckFilePath . '/' . $value, 'rb');
                file_put_contents($newFile, $content, FILE_APPEND);
            }
        }
        return sha1_file($newFile);
    }

    protected function delFile($userName, $userFileName, $userFilePath)
    {
        $dir = EASYSWOOLE_ROOT . "/Upload/$userName/$userFilePath" . $userFileName . ".chunck/";
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }

    }
}