<?php
namespace Admin;
class LogController
{


    


    //记录日志；
    public static function Log($data)
    {
//        $dir = __DIR__;return $dir;
        $res = file_put_contents('/var/www/html/myframe/Storage/my.log',$data.'==='.'\n',FILE_APPEND);//绝对路径
//        $res = file_put_contents('../../Storage/my.log',$data,FILE_APPEND/LOCK_EX);  //相对路径无法写入数据。
//        return $data;
        if($res){
            return $data;
        }else{
            return "对不起，文件写入失败！";
        }

    }
    public function getLog()
    {
        file_get_contents();
    }
}