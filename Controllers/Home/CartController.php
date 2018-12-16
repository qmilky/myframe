<?php
namespace Home;
use DB;
use AllCommonController;
class CartController extends AllCommonController

{
    public  function toCart()
    {
        $gid = $_GET['gid'];
        $bcnt = $_GET['bcnt'];
        $token = $_GET['token'];
        /*
                // 有session
                    等
                    不做

                    不等
                        做
                // 没有session
                        做
            */

        // 如果session中没值, 或者, 有值但不相等，若不进行控制，刷新to_cart.html页面时，此处代码会一直执行，商品数量一直添加
        if(empty($_SESSION['token'])  || $_SESSION['token']!=$token){
            $_SESSION['token']=$token;  //只有此处会将token存入session。（此处是加入购物车）
            //获取商品信息存入session
            if(empty($_SESSION['cart'][$gid])){
                $good = (new DB('shop_goods'))->where('id='.$gid)->first();
                $good->price = $this->fenToYuan($good->price);
                $_SESSION['cart'][$gid] =$good ;  //此处$gid在index_cart页面遍历$_SESSION['cart']时等于$k,加减购物车中商品数量时会用到;
                $_SESSION['cart'][$gid]->bcnt = $bcnt;
            }else{
                $_SESSION['cart'][$gid]->bcnt += $bcnt;
            }
        }
//        var_dump($_SESSION['cart']);

        include('./Views/Home/Cart/to_cart.html');
    }


        public  function index()
    {
        include('./Views/Home/Cart/index_cart.html');
    }

    //加1
    public function add()
    {
        $gid = $_GET['gid'];
        $_SESSION['cart'][$gid]->bcnt += 1;
        $stock = (new DB('shop_goods'))->where("id=".$gid)->first()->stock;
        if($_SESSION['cart'][$gid]->bcnt>=$stock){
            $_SESSION['cart'][$gid]->bcnt=$stock;
        }
        header('location:./index.php?m=home&c=cart&f=index');//快速
//        header('refresh:0;url=./index.php?m=home&c=cart&f=index');//会闪一下
        die;
    }


    //减1
    public function subtract()
    {
        $gid = $_GET['gid'];
        $_SESSION['cart'][$gid]->bcnt -=1;
        if($_SESSION['cart'][$gid]->bcnt<=1){
            $_SESSION['cart'][$gid]->bcnt=1;
        }
        header('location:./index.php?m=home&c=cart&f=index');
        die;
    }




    //移除某个商品
    public function del()
    {
        $gid = $_GET['gid'];
        unset($_SESSION['cart'][$gid]);  //删除数组中特定下标的值，下标是商品id；
        header('location:./index.php?m=home&c=cart&f=index');
        die;
    }




}