<?php
namespace Admin;
use DB;
use UP;
use Image;
use Admin\LogController;
use PDO;
class GoodsController extends CommonController
{
    /*添加商品
     *
     * */
    public function add()
    {
        //分类相关
        $cates = (new DB('shop_cates'))->orderBy(" concat(path,id,',') ")->select();
        //页面中若有子分类，父分类无法被选中，只能在子分类下添加商品
        $pid = array_column($cates, 'pid');// 得到所有父ID ,返回数组
        $pid = array_unique($pid);  //去重。
        include('./Views/Admin/Goods/goods_add.html');
    }

    public function doAdd()
    {
        //       $gname  = $_GET['gname'];
//        $price  = $_GET['price'];
//        $stock  = $_GET['stock'];
//        $gpic   = $_GET['gpic'];
//        $gdesc  = $_GET['gdesc'];
//        $status = $_GET['status'];
        $data = $_POST;
        $data['created_at'] = time();
        $data['price'] = $this->yuanToFen($data['price']);
//        var_dump($data);die;
        //上传文件处理
        $up = new UP(SAVEPATH, TYPE, MAXSIZE);
//        $up =$up->upload($_FILES['gpic']);  //返回的$up 是true 或者false，不是对象。
        $up->upload($_FILES['gpic']);
        $msg = $up->msg;
//        var_dump($_FILES['gpic']);var_dump($msg);var_dump($up);die;
//      压缩上传图片

        $img = new Image(SAVEPATH, 50, 50);
        LogController::log('上传文件新名' . $up->fileName);
        $img->toSmImg($up->fileName);//输出生成缩略图

        //添加图片名
        $data['gpic'] = $up->fileName;

        //执行插入
        $db = new DB('shop_goods');
        $row = $db->insert($data);
        if ($row) {
            echo '添加商品成功';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
            die;
        } else {
            echo '添加商品成功';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=add');
            die;
        }
    }

    /*浏览商品
     *
     * */
    public function index()
    {
        $db = new DB('shop_goods');
        //判断是否传了查询分类的条件
        if (!empty($_GET['cid'])) {
            //查找当前分类id的子级
            $subcid = (new DB('shop_cates'))->where(" path like '%,{$_GET['cid']},%' ")->select(['id']);//二维数组
            $subcid = array_column($subcid, 'id');//一维数组。
//            echo '<pre>';
//            print_r($subcid);die;
            if (!empty($subcid)) {
                $str = " cid in (" . implode(',', $subcid) . ",{$_GET['cid']}) ";
            } else {
                $str = " cid in ({$_GET['cid']}) ";
            }
//            echo '<pre>';
//            print_r($str);die;
            $db->where($str);
        }
        //关键字
        if (!empty($_GET['gname'])) {
            $str = " gname like '%{$_GET['gname']}%' ";
            $db->where($str);
        }
        //时间


        //查询商品
        //方式一
//        $sql = "select
//            g.*,c.cname
//            FROM shop_goods g LEFT JOIN shop_cates c ON g.cid=c.id";
//        $sql = "SELECT
//                        g.*,c.cname
//                    FROM
//                        shop_goods g LEFT JOIN shop_cates c ON g.cid=c.id
//            ";
//
//       $goods =  DB::getPDO()->query($sql)->fetchAll(PDO::FETCH_CLASS);
        //方式二：
        $goods = $db->select();
        foreach ($goods as $k => $v) {
            $v->price = $this->fenToYuan($v->price);
        }
//        echo '<pre>';
//       print_r($goods);die;
        $cates = (new DB('shop_cates'))->orderBy("concat(path,id,',')")->select(['id', 'cname', 'path']);
        $cate = array_column($cates, 'cname', 'id');
//        echo '<pre>';
//       print_r($cates);die;
        include('./Views/Admin/Goods/goods_index.html');

    }

    public function edit()
    {
        $gid = $_GET['gid'];
        $db = new DB('shop_goods');
        $goods = $db->where(" id={$gid} ")->first();
        $goods->price = $this->fenToYuan($goods->price);
        $cates = (new DB('shop_cates'))->orderBy(" concat(path,id,',') ")->select(['id', 'cname', 'path', 'pid']);
        // 生成一个所有父类数组//===============================================================
        $pid = array_column($cates, 'pid');
        $pid = array_unique($pid);
        include('./Views/Admin/Goods/goods_edit.html');
    }

    public function doEdit()
    {
        $gid = $_GET['gid'];
        $data = $_POST;
//        var_dump($data);die;
        //上传文件处理
        if ($_FILES['gpic']['error'] != 4) {
            ($up = new UP(SAVEPATH, TYPE, MAXSIZE))->upload($_FILES['gpic']);
            LogController::log('???' . $up->fileName);
            //压缩
            (new Image(SAVEPATH, 50, 50))->toSmImg($up->fileName);
            //添加文件名
            $data['gpic'] = $up->fileName;
        }
        //插入数据库
        $row = (new DB('shop_goods'))->where(" id={$gid}")->update($data);
        if ($row) {
            echo '修改商品成功';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
            die;
        } else {
            echo '修改商品失败';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=edit&gid=' . $gid);
            die;
        }
    }
    public  function del()
    {
        $gid = $_GET['gid'];
        $db = new DB('shop_goods');
        $good = $db->where( 'id='.$gid)->first();
        //判断库存，状态
        if(!empty($good)){
            if(!empty($good->stock)){
                echo '哈哈，该商品有库存，不能删除！';
                header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
                die;
            }
            if($good->status!=3){
                echo '哈哈，该商品未下架，不能删除！';
                header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
                die;
            }
        }else{
            echo '哈哈，该商品不存在！';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
            die;
        }
       $res = $db->where('id='.$gid)->delete();
        if($res){
            echo '删除成功！';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
            die;
        }else{
            echo '删除失败！';
            header('refresh:2;url=./index.php?m=admin&c=goods&f=index');
            die;
        }

    }

    //上架
    public function upperShelves($status=2)
    {
        $gid = $_GET['gid'];
        $data['status']=$status;
        $row = (new DB('shop_goods'))->where('id='.$gid)->update($data);
//        if($row){
//            echo '上架成功';
//        }else{
//            echo '上架失败';
//        }
        header('location:./index.php?m=admin&c=goods&f=index');
        die;
    }

    //下架
    public function lowerShelves()
    {
        $this->upperShelves(3);
    }

}