<?php
namespace Admin;
use DB;
use PDO;

class OrderController extends CommonController
{
    public   function index()
    {
        $orders = (new DB('shop_orders'))->select();
        foreach($orders as $k=>$v){
            $v->ormb=$this->fenToYuan($v->ormb);
        }
        $uid  = array_column($orders,'uid');
        $uids = array_unique($uid);
//        $uid_str = implode(',',$uids);
        $user = (new DB('shop_users'))->whereIn('id',$uids)
            ->select(['id','uname']);
        $users = array_column($user,'uname','id');
        include('./Views/Admin/Order/index_order.html');
    }
    //订单详情
    public  function show()
    {
        $oid = $_GET['oid'];
        $db = new DB('shop_order_details');
        $sql = "
            SELECT d.*,g.gname,g.gpic FROM shop_order_details as d LEFT JOIN 
            shop_goods g ON d.gid=g.id
            where d.oid='{$oid}'
        ";
        $details=$db::getPDO()->query($sql)->fetchAll(PDO::FETCH_CLASS);
        foreach($details as $k=>$v){
            $v->bprice=$this->fenToYuan($v->bprice);
        }
//        echo '<pre>';
//        print_r($details);die;


        include('./Views/Admin/Order/show_order.html');
    }
}