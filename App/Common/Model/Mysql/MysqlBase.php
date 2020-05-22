<?php

namespace App\Common\Model\Mysql;

use EasySwoole\Component\Singleton;
use EasySwoole\Component\Di;
use App\Common\Lib\CodeStatus;

class MysqlBase
{
    use Singleton;

    public $db;
    public $table;

    public function __construct()
    {
        /*判断有没有安装mysqli拓展*/
        if (!extension_loaded('mysqli')) {
            throw new \Exception(CodeStatus::getReasonPhrase(CodeStatus::MYSQL_LOADED_ERROR));
        }
        $db = Di::getInstance()->get("MYSQL");
        if ($db instanceof \MysqliDb) {
            $this->db = $db;
        } else {
            $this->db = new \MysqliDb(\Yaconf::get('qvbilam_pay.mysql_connect'));
        }
        if (!$this->db) {
            throw new \Exception(CodeStatus::getReasonPhrase(CodeStatus::MYSQL_CONNECT_ERROR));
        }
    }

    /*
     * 通过条件获取数据
     * */
    public function getByConditon($conditon, $field = '*', $num = null)
    {
        $obj = $this->handleConditon($conditon);
        if ($obj == false) {
            return false;
        }
        try {
            if ($num == 1) {
                $data = $obj->getOne($this->table, $field);
            } else {
                $data = $obj->get($this->table, $num, $field);
            }
        } catch (\Exception $e) {
            return false;
        }
        return $data;
    }

    /*
     * 通过条件修改数据
     * */
    public function updateByConditon($conditon, $updateData)
    {
        $obj = $this->handleConditon($conditon);
        if ($obj == false) {
            return false;
        }
        try {
            $res = $obj->update($this->table, $updateData);
        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }

    /*
     * 处理条件
     * */
    protected function handleConditon($conditon)
    {
        $res = $this->db;
        if (empty($conditon)) {
            return false;
        }
        foreach ($conditon as $k => $v) {
            if (!is_array($v)) {
                $v = explode(',', $v);
            }
            $res->where($k, ...$v);
        }
        return $res;
    }

}