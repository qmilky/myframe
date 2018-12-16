<?php
use Admin\LogController;
class UP
{
    private $maxSize;    // 控制大小
    private $types;      // 允许类型
    private $saveDir;    // 保存位置
    private $file;    // 上传的文件
    public $msg;    // 保存上传消息
    public $fileName;    // 保存上传之后的随机文件名

    //保存位置  允许类型  大小限制
    public  function __construct($saveDir,$types,$maxSize)
    {
        $this->saveDir = $saveDir;
        $this->types   = $types;
        $this->maxSize = $maxSize;
    }
//检查错误号：
    public function checkError()
    {
        switch($this->file['error']){
            case 0: $this->msg = '上传成功';return true;//return 后面的代码不执行。
            case 1: $this -> msg = '文件过大'; break;  //文件大小比php.ini中upload_max_filesize指定值要大
            case 2: $this -> msg = '文件过大'; break;//文件的小比表单的MAX_FILE_SIZE指定的值大
            case 3: $this -> msg = '部分文件过大'; break;
            case 4: $this -> msg = '没有文件被上传'; break;
            case 6: $this -> msg = '找不到临时目录'; break;//在php.ini中没有指定临时文件夹
            case 7: $this -> msg = '写入文件失败'; break;
            default:
                $this -> msg = '未知错误';
        }
        return false;
    }

/*
 * 检测文件的大小
 *
 * */
public  function checkSize()
{
    if($this->file['maxSize'] > $this->maxSize){
        $this->msg = '文件过大';
        return false;
    }
    return true;
}

/*
 * 检测文件类型
 * */
public function checkType()
{
    if(in_array($this->file['type'],$this->types)){
        return true;
    };
    $this->msg = '文件类型不符合要求';
    return false;
}
/*判断是否为合法上传
 *
 * */
public function checkHf()
{
   if( is_uploaded_file($this->file['tmp_name'])){
        return true;
    };
   $this->msg = '不是合法上传的文件';
   return false;
}
/*
 * 生成随机文件名
 *
 * */
public function reName()
{
    //获取后缀名
    $ext = '.'.pathinfo($this->file['name'],PATHINFO_EXTENSION);
    do{
        $fileName = date('YmdHis').mt_rand(10000,99999).$ext;
        LogController::log($fileName.'====>'.$ext);
    }while(file_exists($this->saveDir.$fileName));
    $this->fileName = $fileName;
}




/* 功能: 执行上传处理
 * 参数: $file 相当于 $_FILES['gpic']
 * 返回: 成功返回true 失败返回false
 *
 *
 * */
public function upload($file)
{
    $this->file = $file;
    $flag = $this->checkError() && $this->checkSize() && $this->checkType() && $this->checkHf();
    if($flag){
        $this->reName();
        LogController::log($this->saveDir.'/'.$this->fileName);
        return move_uploaded_file($this->file['tmp_name'],($this->saveDir.$this->fileName));
    }
    return false;
}


}