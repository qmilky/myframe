<?php
namespace Home;
use Admin\CateController;
use DB;
class FirstController
{
    //显示首页
    public  function first()
    {
//        $cates = (new DB('shop_cates'))->select();
        $cates = CateController::getCates();
//        echo '<pre>';print_r($cates);die;
        include('./Views/Home/First/first_index.html');
    }

}