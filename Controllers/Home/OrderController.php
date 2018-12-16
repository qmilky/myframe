<?php
namespace Home;
use DB;
use AllCommonController;
class OrderController extends AllCommonController
{
    private $oid;
    public  function getInfo()
    {
        //判断是否登录
        if(empty($_SESSION['homeFlag'])){
            $_SESSION['back'] = $_SERVER['REQUEST_URI'];  //为了使用户登录成功后重新跳回此处。
            header('refresh:2;url=./index.php?m=home&c=user&f=login');
            die('您还没有登录，请先登录。。。');
        }
        include('./Views/Home/Order/getinfo_order.html');
    }
    public  function jsy()
    {
        $_SESSION['order']['rec']=$_POST['rec'];
        $_SESSION['order']['tel']=$_POST['tel'];
        $_SESSION['order']['addr']=$_POST['addr'];
        $_SESSION['order']['umsg']=$_POST['umsg'];
        $_SESSION['order']['uid'] = $_SESSION['homeUserInfo']->id;
        include('./Views/Home/Order/index_jsy.html');
    }

    public function finished()
    {
        //开启事务
        DB::getPDO()->beginTransaction();
        // 修改库存和销量，   // 写入主表信息  ；// 写入详情表,主表和详情表顺序不可以反，否则oid无法写入详情表。
        if($this->updateStock() && $this->writeOrder() && $this->writeDetail()){
            DB::getPDO()->commit();
            //清空购物车
            $this->clearCart();

        }else{
            DB::getPDO()->rollBack();
            echo '提交订单失败';
            header('refresh:2;url=./index.php?m=home&c=cart&f=index');
            die;
        }
    }

    public function updateStock()
    {
        $sql = " update shop_goods set stock=stock-:cnt,salecnt=salecnt+:cnt where id=:gid";
        // 准备执行, 返回预处理对象
       $stmt = DB::getPDO()->prepare($sql);
       foreach($_SESSION['cart'] as $k=>$v){
           // 具体的,实际的,执行
           $stmt -> execute([':cnt'=>$v->bcnt,':gid'=>$k]);
           // 返回上一条SQL语句的受影响行数
           $row = $stmt->rowCount();
           if(!$row){
               echo '修改库存失败';
               return false;
           }
       }
       return true;
        
    }
    // 写入主表信息
    public  function writeOrder()
    {
        $data['oid']=date('Ymdhis').mt_rand(10000,99999);
        $data['ormb'] = $this->yuanToFen($_SESSION['order']['ormb']);
        $data['ocnt'] = $_SESSION['order']['ocnt'];
        $data['uid'] = $_SESSION['order']['uid'];
        $data['rec'] = $_SESSION['order']['rec'];
        $data['addr'] = $_SESSION['order']['addr'];
        $data['tel'] = $_SESSION['order']['tel'];
        $data['status'] = 1;
        $data['umsg'] = $_SESSION['order']['umsg'];
        $data['otime'] = time();
        $this->oid=$data['oid'];
        $res = (new DB('shop_orders'))->insert($data);
//        var_dump($res);die;
        if(!$res){
            echo '写入主表失败';
        }
        return $res;
    }
    //写入详情表信息
    public function writeDetail()
    {
        /*
            $sql = "insert into shop_detail(字段列表) values(值)";
            $sql = "insert into shop_detail(字段列表) values(值)";
            $sql = "insert into shop_detail(字段列表) values(值)";

            $sql = "insert into shop_detail(字段列表) values(值1),(值2),(值3),";
            */
        $sql = " insert into shop_order_details(oid,gid,bprice,bcnt) values";
//        echo '<pre>';print_r($_SESSION['cart']);die;

        foreach($_SESSION['cart'] as $k=>$v){
            $bprice = $this->yuanToFen($v->price);
            $sql .= "('{$this->oid}','{$k}','$bprice','{$v->bcnt}'),";  //此处$k=$v->id=gid商品id;
        }
        $sql = rtrim($sql,',');
        $res  = DB::getPDO()->exec($sql);
//        var_dump($res);die;
        if(!$res){
            echo '写入详情表失败';
        }
        return $res;

    }

    //清空购物车
    public function clearCart()
    {
        include('./Views/Home/Order/finished.html');
        unset($_SESSION['cart']);
        unset($_SESSION['order']);
        die;
    }












}
