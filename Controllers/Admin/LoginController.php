<?php
namespace Admin;
use DB;
use PDO;
use Image;
class LoginController
{
    public  function  login()
    {
//        $path = 'http://appcdn.fanyi.baidu.com/zhdict/gif/b49cdc1cc427711e5876ac8e0eb15ce01.gif';
//        $a = new Image($path,70,70);var_dump($a);
//        $a->toSmImg('b49cdc1cc427711e5876ac8e0eb15ce01.gif');

        //显示登陆页面
        include ('./Views/Admin/Login/login.html');

    }
    public  function  doLogin()
    {

        try{
            $code = $_POST['code'];
            $uname = $_POST['uname'];
            $upwd = md5($_POST['upwd']);
            // 判断输入的验证码和原来系统生成的验证 比较   strtolower 把内容全部转为小写
            if (strtolower($code) !== strtolower($_SESSION['code'])) {
               
                echo '验证码错误'.$code.'===='.$_SESSION['code'];
                header('refresh:2;url=/index.php?m=admin&c=login&f=login'); //url=/index.php/?m=admin&c=login&f=login  ???
                die();
            }
            // 连接数据查询有没有指定名称和密码的人
            $db = new DB('shop_users');
            $user = $db->where("uname='{$uname}'")->where("upwd='{$upwd}'")->first();
            if($user){
                //权限相关：
                // 子查询, 获取该用户所能访问的所有节点信息, 生成一个数组, 存放在 session 中
                $sql = "SELECT * FROM shop_nodes WHERE id in ( select nid from shop_grants where rid={$user->auth} )";
                $nodes = DB::getPDO()->query($sql)->fetchAll(PDO::FETCH_CLASS);
                $_SESSION['node_arr'] = array_column($nodes, 'node');
                echo '登录成功,3秒后跳转.....';
                // 设置登录成功标志
                $_SESSION['adminFlag'] = true;
                $_SESSION['userInfo'] = $user;
                header('refresh:2;url=./index.php?m=admin&c=Login&f=index');
                die;
            }else{
                // 登录失败
                echo '用户名或密码错误!';
                header('refresh:2;url=./index.php?m=admin&c=login&f=login');
                die;
            }
        }catch(\Exception $e){
            var_dump($e->getMessage());die();
        }

    }
    //退出
    public function logOut()
    {
        echo '正在退出。。。';
        $_SESSION['adminFlag'] = false;
        $_SESSION['userInfo'] = null;
        header('refresh:2;url=./index.php?m=admin&c=login&f=login');
        die;
    }

    //后台首页
    public function index()
    {
        include('./Views/Admin/index.html');
    }



}