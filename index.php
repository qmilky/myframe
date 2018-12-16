<?php

    session_start();
    //接收命令
    $m = !empty($_GET['m'])?$_GET['m']:'home';  //不可为$m =!empty($_GET['m'])?:'home';
    $c = !empty($_GET['c'])?$_GET['c']:'first';
    $f = !empty($_GET['f'])?$_GET['f']:'first';

    //引入异常处理
    include('./Public/doexception.php');

    //引入配置文件
    include('./Config/databases.php');
    include('./Config/upload.php');
    //引入自动加载处理函数
    include('./Public/autoload.php');
    //拼接类名，加命名空间的类名；
    $className = '\\'.ucfirst($m).'\\'.ucfirst($c).'Controller';  //Admin\UserController,此处$m拼接的是命名空间，因为前后台会有相同的类名，以此区分，命名空间用反斜杠'\'区分。
    //实例化，调用相应的成员方法

    (new $className)->$f();  //此处若为new($className)->$f(); 会报错500
