<?php
namespace Home;
use DB;
use AllCommonController;
class GoodsController extends AllCommonController
{
    public  function index()
    {
        $cid = $_GET['cid'];
        $db = new DB ('shop_goods');
        if(!empty($cid)){

            //找分类的子类
            $subCates = (new DB('shop_cates'))->where(" path like '%,{$cid},%'")->select(['id','cname']);
            $cid_arr = array_column($subCates,'id');
            array_push($cid_arr,$cid);
            $str = ' cid in ('.implode(',',$cid_arr).')';
            $db->where($str);
        }
        // // 时间条件
        // if(){
        // $db -> where(一些条件);
        // }

        // // 价格条件
        // if (){
        // $db - > where(一些条件)
        // }

        $cnt = $db->rowCount();
        $goods = $db->limit($cnt,5)->select();
        foreach($goods as $k=>$v){
            $v->price=$this->fenToYuan($v->price);
        }
//        var_dump($goods);die;
        include('./Views/Home/Goods/index_goods.html');
    }

//    详情页
    public  function show()
    {
        $gid = $_GET['gid'];
        $goods = (new DB('shop_goods'))->where('id='.$gid)->first();
        $goods->price = $this->fenToYuan($goods->price);
        $token = uniqid('goods_',true);

        include('./Views/Home/Goods/show_goods.html');
    }












}