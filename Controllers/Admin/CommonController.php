<?php
namespace Admin;
use AllCommonController;
class CommonController extends AllCommonController
{
    public  function __construct()
    {
        //判断登录是否成功
        if(empty($_SESSION['adminFlag'])){
            echo '您还没有登录,请先登录...';
            header('refresh:2;url=./index.php?m=admin&c=login&f=login');
            die;
        }



        //判断权限
        $node = ucfirst($_GET['c']).'Controller@'.$_GET['f'];// 准备要访问的地方
        if(!in_array($node,$_SESSION['node_arr'])){
            if($_SESSION['userInfo']->auth==3){  //auth==3是人名群众，无法登陆后台。
                header('refresh:1;url=./index.php?m=home&c=first&f=first');
                die('您找到页面不存在 404!');
            }else{
                header('refresh:1;url=./index.php?m=admin&c=login&f=index');
                die('权限不够....2秒后跳转...!');
            }
        }

        
    }
    






}