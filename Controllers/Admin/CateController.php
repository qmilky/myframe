<?php
namespace  Admin;
use DB;
class CateController extends CommonController
{
    //添加类别
    public function add()
    {
        $cid = empty($_GET['id']) ? 0:$_GET['id'];
        // 获取之前已有的类别信息
        // $sql = "select * from shop_cate order by concat(path,cid,',')";
        $cates = (new DB('shop_cates'))
        ->  orderBy(" concat(path,id,',') ")
        ->  select();
        include('Views/Admin/Cate/add_cate.html');
    }
    public  function  doAdd()
    {
        $data['cname']=$_POST ['cname'];
        $pid = $data['pid']=$_POST['pid'];
//        var_dump($pid);die;
        $db = new DB('shop_cates');
        if($pid == '0'){
            $data['path']=$pid.',';
        }else{
            $cateInfo     = $db->where(" id={$pid} ") ->first();
//            var_dump($cateInfo->path.$pid.',');die;
            $data['path'] = $cateInfo->path.$pid.',';
        }

        $res = $db -> insert($data);
        if($res){
            echo '添加类别成功';
            header('refresh:2;url=./index.php?m=admin&c=cate&f=index');
        }else{
            echo '添加类别失败';
            header('refresh:2;url=./index.php?m=admin&c=cate&f=add');
        }
        die;

    }


    public  function index()
    {
        $db = new DB('shop_cates');
        $column=[
            'id',
            'cname',
            'pid',
            'path'
        ];
        $cnt = $db->rowCount();
       $cates =  $db
//           ->limit($cnt,5)
               ->orderBy(" concat(path,id,',') ")
           ->select($column);
        include('./Views/Admin/Cate/index_cate.html');
    }

public  function del()
{
    $cid = $_GET['cid'];
    $db = new DB('shop_cates');
    //判断是否有子级，若有无法删除。
//    $sub = $db->where('pid='.$cid)->first();
    $sub = $db->where('pid='.$cid)->rowCount();
    $db->clearWhere();//清除查询条件
    if($sub){
        header('refresh:2;url=./index.php?m=admin&c=cate&f=index');
        die('存在子类，无法删除，哈哈！');
    }
    //判断是否存在商品
    //删除
    $res = $db->where('id='.$cid)->delete();
    if($res){
        echo '删除成功！';
    }else{
        echo '删除类别失败';
    }
    header('refresh:2;url=./index.php?m=admin&c=cate&f=index');die;
}


public function edit()
{
    $cid = $_GET['cid'];
    $cate = (new DB('shop_cates'))->where('id='.$cid)->first();
    
    include('./Views/Admin/Cate/edit_cate.html');
}
public  function doEdit()
{
    $data['cname']=$_POST['cname'];
    $cid = $_GET['cid'];
    $db = new DB('shop_cates');
    $row = $db->where('id='.$cid)->update($data);
    if($row){
        echo '修改成功';
        header('refresh:2;url=./index.php?m=admin&c=cate&f=index');
        die;
    }else{
        echo '修改失败';
        header('refresh:2;url=./index.php?m=admin&c=cate&f=edit&cid='.$cid);
        die;
    }
}


     /*无限极分类：子分类相关，父子分类数据结构处理
     * @param $cates  所有分类，数据库中取出，array，required：true；
     * @param $pid    父id，int;required：true；
     *
     *
     * */

    static public function getLevelCate($cates, $pid=0)
    {
        $sub = [];
        foreach($cates as $k=>$v){
            if ($v->pid==$pid){
                $v->sub_level = substr_count($v->path,',')-1;  //判断是几级子分类，如，php为根分类显示为0，Laravel为1级子分类显示为1。
                $v->sub = self::getLevelCate($cates, $v->id);  //对象赋值sub属性。
                $sub[] = $v;
            }
        }
        return $sub;
    }


    static public function getCates()
    {
        // 1.得到类别数组
        $cates = (new DB('shop_cates'))->select();
        // 2.生成层级关系数组
        return self::getLevelCate($cates, 0);
    }


}