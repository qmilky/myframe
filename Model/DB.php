<?php
//namespace Model;  //为什么DB没有命名空间？？？
use Admin\LogController;
class DB
{
    static private $pdo;  //私有属性，类外以及子类中均无法调用。只能通过getPDO（）方法获取唯一的实例，单例模式。
    public $tableName;
    public $where=[];//为什么会有默认值。
    public $whereIn=[];
    public $orderBy='';
    public $limit='';
    public $nowPage=0;
    public $prevPage=0;
    public $nextPage=0;
    public $maxPage=0;


    static public  function getPDO()
    {
        if(empty(self::$pdo)){
            self::$pdo = new PDO(DSN,USER,UPWD);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    private function __get($name)
    {
        return $this->$name;
    }
/*
 * $tb  数据表的表名
 * */
    public function __construct($tb)
    {
        $this->tableName = $tb;
        if(empty(self::$pdo)){
            self::$pdo = new PDO(DSN,USER,UPWD);
            self::$pdo ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }
    }

    /*
     * where条件
     * */

    public function where($str)
    {
        if(is_array($str)){
            foreach($str as $k=>$v){
                $this->where[]= " {$k}='{$v}' ";
                LogController::log('where条件'." {$k}='{$v}' ");
            }

        }else{
            $this->where[]=$str;
        }

        return $this; //返回调用对象的原因，为了连调，从而可以使用增删改查。
    }
    /*
     * 清空where条件
     * */
    public  function clearWhere()
    {
        $this->where = [];
    }
    /*
     * 将where条件数组中的条件拼接成一个字符串，sql可移执行的
     * */
    public function getWhere()
    {
        $condition = '';
        if(!empty($this->where)){
            $condition = ' where '.implode(' and ',$this->where);
        }
        return $condition;
    }
    /*
     * whereIn,批量删除时使用。
     * */
    public function whereIn($attribute,$ids)
    {
        if(is_array($ids) && count($ids)>0){
            $this->whereIn[] = " where {$attribute} in {$ids}";
            return $this;
        }
        return false;
    }
    /*
     * orderBy
     * @param  $str string,desc倒叙，asc正序
     * return obj ,返回调用的对象。
     * */
    public function orderBy($str)
    {
        $this->orderBy = ' order by '.$str;
        return $this;
    }




    /*
     * 数据插入数据库
     * 返回受影响行数
     * */
    function insert($data)
    {
        //准备SQL语句
        $fields = '';
        $values ='';
        foreach ($data as $k => $v){
            $fields .= $k.',';
            $values .= "'$v',";
        }
        $fields = rtrim($fields,',');
        $values = rtrim($values,',');
        $sql = "insert into {$this->tableName} ({$fields}) values({$values})";
        //返回受影响行数
        return self::$pdo->exec($sql);
    }
    /*
     * 删除数据可批量
     *
     * @param  $attribute 要删除的字段属性 mixed；
     * @param  $ids       符合条件的值，array(批量时)，int（删除单个数据）；
     *
     * @return mixed
     * */
    public function  delete($attribute='',$ids='')
    {
        try{
            if(is_array($ids)){
                $whereIn = $this->whereIn($attribute,$ids);
                if(!empty($whereIn)){
                    $sql = "delete from {$this->tableName} $whereIn";
                }
            }else{
                $sql = "delete from {$this->tableName} {$this->getWhere()}" ;
//                echo $sql;die;
                $this->where = [];
            }
            return self::$pdo->exec($sql);
        }catch(\Exception $e){
            //日志如何记录
            Throw new \Exception('删除失败',1001);
        }

    }

    public function update($data)
    {
        $str = '';
        foreach($data as $k=>$v){
            $str .= "{$k}='{$v}',";
        }
        $str = rtrim($str,',');  //参数位置不可错，否则$str 为空。
//        echo '+++'.$str.'===';
        $sql = "update {$this->tableName} set {$str} ".$this->getWhere();
        $this->where = [];
//        echo $sql;
        // 发送执行
        return self::$pdo -> exec($sql);
    }

    /*查询多条
     * @param $column  array;required false;
     * */
    public  function select($column=[])
    {
        if(count($column)>0){
            $column = implode(',',$column);
        }else{
            $column = '*';
        }
        $sql ="select {$column} from {$this->tableName} ".$this->getWhere().$this->orderBy.$this->limit;
       $log =  LogController::log($sql);
//       var_dump($log) ;die;
        $this->where = [];
        //发送执行，返回预处理对象
        $res = self::$pdo->query($sql);
        $ress = $res ->fetchAll(PDO::FETCH_CLASS);
        return $ress ;//读取出全部记录  千万不可返回$res;
    }

    /*查询单条
     * @param $column  array;required false;
     *
     * */
    public function  first($column=[])
    {
        if(count($column)>0){
            $column = implode(',',$column);
        }else{
            $column = '*';
        }
        $sql = "select {$column} from {$this->tableName} ".$this->getWhere().' limit 1';
//        echo $sql;
        $this->where = [];
        $res = self::$pdo->query($sql);
        return $res ->fetch(PDO::FETCH_OBJ);
    }

    /*统计记录数
     *
     *
     * */

    public function rowCount()
    {
        $sql = "select count(*) num  from {$this->tableName} ".$this->getWhere();  //num 的作用。
        $res = self::$pdo->query($sql);
        $object =  $res ->fetch(PDO::FETCH_OBJ);
        return $object->num;
    }

    /*分页
     *@param $cnt，总条数，int;required  true
     *@param $perPage，每页条数， int;required  true
     *
     * */
    public  function limit($cnt,$perPage)
    {
    //获取当前页码
        $nowPage = empty($_GET['p'])? 1:$_GET['p'];
    //上一页，下一页，最后一页（最大页）
        $prevPage = $nowPage - 1;
        $nextPage = $nowPage + 1;
    //最大页数
        $maxPage = ceil($cnt/$perPage);
    //修正
        if($nowPage<=1){
            $nowPage = 1;
            $prevPage = 1;
        }elseif($nowPage>=$maxPage){
            $nowPage  = $maxPage;
            $nextPage = $maxPage;
        }
    //生成limit=（当前页码-1）*每页记录数，每页记录数；
        $this->limit =' limit '.($nowPage-1)*$perPage . ','.$perPage;
        $this->nowPage = $nowPage;
        $this->prevPage = $prevPage;
        $this->nextPage = $nextPage;
        $this->maxPage = $maxPage;
        return $this;  //为了连调
    }


}