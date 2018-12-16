<?php
function __autoload($className)
{
    $className = str_replace('\\','/',$className);  //必须有，否则找不到类，因为文件之间用正斜杠'/'区分，。
    $c = './Controllers/'.$className.'.php';
//    var_dump($c);
    if(file_exists($c)){
        include($c);
         return;  //必须有return，return后面的代码不执行。
    }
    $m = './Model/'.$className.'.php';
    if(file_exists($m)){
        include($m);
        return;
    }
    $p = './Public/'.$className.'.php';  //linux 中类名区分大小写如Public/Image.php 而不是image.php
    if(file_exists($p)){
        include($p);
        return;
    }


}