<?php
set_exception_handler('exceptions');
function  exceptions($e)
{
if($e instanceof PDOException){
    if(!in_array($e->getCode(),[['42S22','42S02', 1049,2019,2002,1045,1044]])){
        die($e->getMessage());
    }
    $error = [
        '42S22' => '未知字段,你可能把字段名称写错了',
        '42S02' => '没有这个数据表,你可能写错表名称了',
        1049 => '没有这个数据库名',
        2019 => '字符集不对',
        2002 => '服务器地址可能不对，需要ip',
        1045 => '你写的密码不对',
        1044 => '访问被拒绝,可能是账号写错啦'
    ];
    echo '什么时候走这里啊？？？';
    echo $error[$e->getCode()];
    echo '<pre>';
    print_r($e->gettrace());//[0]['args'][0];
    // $arr = $e->getTraceAsString();
    echo '</pre>';
    die;

    }
    // 恢复由原来的函数处理未被捕获的异常
    restore_exception_handler();

    echo '<pre>走这里就出错啦！！！<br>';
    var_dump($e->getMessage());
    throw $e;
}