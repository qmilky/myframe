<?php
namespace Admin;
use DB;
class UserController extends CommonController
{
    public  function add()
    {
        include('./Views/Admin/User/insert.html');
    }
    public function doAdd()
    {
       $uname =  $_POST['uname'];
       $upwd =  $_POST['upwd'];
       $reupwd =  $_POST['reupwd'];
        if(empty($upwd) || empty($reupwd)){
            header('refresh:2;url=./index.php?m=admin&c=user&f=add');
            die('密码不能为空');
        }
        if($upwd != $reupwd ){
            header('refresh:2;url=./index.php?m=admin&c=user&f=add');
            die('两次密码不一致');
        }
        $data['uname'] = $uname;
        $data['upwd'] = md5($upwd);
        $data['sex'] = $_POST['sex'];
        $data['tel'] = $_POST['tel'];
        $data['auth'] = $_POST['auth'];
        $data['regtime'] = time();
//        var_dump($data);
        $row = (new DB('shop_users'))->insert($data);
        if($row){
            echo '用户添加成功';
            header('refresh:2;url=./index.php?m=admin&c=user&f=index');
        }else{
            echo '添加用户失败';
            header('refresh:2;url=./index.php?m=admin&c=user&f=add');
        }

    }
    public  function  index()
    {
        $column = [
            'id',
            'uname',
            'sex',
            'tel',
            'regtime',
        ];
        $db = new DB('shop_users');
        //条件查询
        $tj = '';
        // 性别条件
        if (!empty($_GET['sex'])) {
            $db->where(" sex='{$_GET['sex']}' ");
            $tj .= '&sex='.$_GET['sex'];
        }
        //姓名条件
        if (!empty($_GET['uname'])){
            $db -> where(" uname like '%{$_GET['uname']}%' ");
            $tj .= "&uname={$_GET['uname']}";
        }

        //分页
        $cnt = $db -> rowCount();  //统计符合条件的记录总数
        $db->limit($cnt,5);
        $users =  $db->select($column);
//        print_r($users);die;
        include('./Views/Admin/User/user_list.html');  //引入，可以直接使用上面的变量数据
    }

//删除用户
    public function del()
    {
        $id = $_GET['id'];
        $db = new DB('shop_users');
        $res = $db->where("id={$id}")->delete();
        if($res){
            echo "删除成功";
        }else{
            echo '删除失败';
        }
        header('refresh:2;url=./index.php?m=admin&c=user&f=index');die;
    }

//修改
public function edit()
{
    $id = $_GET['id'];
    $db=new DB('shop_users');
    $user = $db->where("id={$id}")->first();
//    var_dump($user);die;
    include('./Views/Admin/User/edit.html');
}

public  function doedit()
{
    $id = $_GET['id'];
    $data['uname']=$_POST['uname'];
    $data['auth']=$_POST['auth'];
    $data['sex']=$_POST['sex'];
    $data['tel']=$_POST['tel'];
    $db = new DB('shop_users');
    $res = $db->where("id={$id}")->update($data);
    if ($res){
        echo '修改成功';
        header('refresh:2;url=./index.php?m=admin&c=user&f=index');
        die;
    } else {
        echo '修改失败';
        header('refresh:2;url=./index.php?m=admin&c=user&f=edit&id='.$id);
        die;
    }
}















}