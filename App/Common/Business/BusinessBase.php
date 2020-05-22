<?php


namespace App\Common\Business;


class BusinessBase
{
    public $model;

    /*
     * 单条数据插入
     * 一唯数组
     * */
    public function add($data)
    {
        try {
            $res = $this->model->db->insert($this->model->table, $data);
        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }

    /*
     * 批量插入
     * 二维数组
     * */
    public function addAll($data)
    {
        try {
            $res = $this->model->db->insertMulti($this->model->table, $data);
        } catch (\Exception $e) {
            return false;
        }
        return $res;
    }
}