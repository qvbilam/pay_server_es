<?php

namespace App\Common\Business;

use App\Common\Model\Mysql\ServiceOrder as ServiceOrderModel;

class ServiceOrder extends BusinessBase
{
    public $model;

    public function __construct()
    {
        $this->model = new ServiceOrderModel();
    }
}