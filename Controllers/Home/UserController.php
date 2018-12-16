<?php
namespace Home;
use DB;
class UserController
{
    public  function login()
    {
        if($_SERVER['REQUEST_METHOD']=='POST'){
           $data['uname'] =$_POST['uname'];
           $data['upwd']  =md5($_POST['upwd']);
           $user = (new DB('shop_users'))->where($data)->first();
           if($user){
            $_SESSION['homeFlag'] = true;
            $_SESSION['homeUserInfo'] = $user;
            //若从下单处来，则原路返回（back），否则回到首页；
               if(empty($_SESSION['back'])){
                   header('refresh:2;url=./index.php?m=home&c=first&f=first');
               }else{
                   header('refresh:3;url='.$_SESSION['back']);
                   unset($_SESSION['back']);//记得删除；
               }
               die('登录成功，尽情玩耍吧。。。');
           }else{
               header('refresh:2;url=./index.php?m=home&c=login&f=login');
               die('登录失败，请重新登录。。。');
           }
        }

        include('./Views/Home/User/home_login.html');
    }
    public  function logOut()
    {
        $_SESSION['homeFlag'] = false;
        header('refresh:2;url=./index.php?m=home&c=first&f=first');
        die('正在退出...3秒后跳转...');
    }

}