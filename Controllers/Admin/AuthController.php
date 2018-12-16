<?php
namespace Admin;
use DB;
class AuthController extends CommonController
{
    public  function addNode()
    {
        if($_SERVER[REQUEST_METHOD]=='POST'){
            $data['node']=$_POST['node'];
            $data['title']=$_POST['title'];
            $res = (new DB('shop_nodes'))->insert($data);
            if($res){
                header('refresh:2;url=./index.php?m=admin&c=auth&f=indexnode');
                die('添加成功');
            };
        }
        include('./Views/Admin/Auth/add_node.html');
    }
    public  function  indexNode()
    {
        // 拿数据
        $nodes = (new DB('shop_nodes'))->select();

        // 引入模板显示数据
        include('./Views/Admin/Auth/index_node.html');
    }
    // 添加角色
    public  function  createRole()
    {
        if($_SERVER[REQUEST_METHOD]=='POST'){
            $rname = $_POST['rname'];
            $res = (new DB('shop_roles'))->insert(['rname'=>$rname]);
            if($res){
                header('refresh:2;url=./index,pho?m=admin&c=auth&f=rolelist');
                die('添加角色成功');
            }
        }
        include('./Views/Admin/Auth/create_role.html');
    }
    public function roleList()
    {
        $roles = (new DB('shop_roles'))->select();
        include('./Views/Admin/Auth/role_list.html');
    }
    //授权
    public  function grant()
    {
        $rid = $_GET['rid'];
        $nid_arr = (new DB('shop_grants'))->where('rid='.$rid)->select();
        $nid_arr = array_column($nid_arr,'nid');
        $nodes = (new DB('shop_nodes'))->select();
//        print_r($nid_arr);die;
        include('./Views/Admin/Auth/list_node.html');

    }
// 接收授权页面的表单数据, 对某个角色的权限进行设置
public   function  setAuth()
{
    $rid = $_GET['rid'];
    $db = (new DB('shop_grants'));

    $auth = $_POST['auth'];
    $db->getPDO()->beginTransaction();
    $db->where('rid='.$rid)->delete();
    foreach($auth as $k => $v){
        $data['rid']=$rid;
        $data['nid']=$v;
        $res = $db->insert($data);
        if(!$res){
            $db->getPDO()->rollBack();
            header('refresh:2;url=./index.php?m=admin&c=auth&f=grant&rid='.$rid);
            die('权限修改失败');
        }
    }
    $db->getPDO()->commit();
    header('refresh:2;url=./index.php?m=admin&c=auth&f=grant&rid='.$rid);
    $nid_arr = $db->where('rid='.$rid)->select();
    $nid_arr = array_column($nid_arr,'nid');
    $nodes = (new DB('shop_nodes'))->select();
    die('权限修改成功');
}



}