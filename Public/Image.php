<?php
use Admin\LogController;
	class Image
	{
		private $file;   // 文件
		private $path;   // 保存路径
		private $ext;
		
		// 根据文件后缀名, 生成处理函数文件名
		private function getFuncName()
		{
        
            // 1.获取基本文件名
            if ( $pos = strpos($this->file,'?') ){
				$this->file = substr_replace($this->file,'',$pos);  //去掉?号后面的字符
			}
			$this->file = pathinfo($this->file, PATHINFO_BASENAME); 
            
            
            // 2.通过后缀名生成函数名称
			$ext = pathinfo($this->file,PATHINFO_EXTENSION);
			$ext = strtolower($ext);
			LogController::log('Image文件后缀名'.$ext.'+++'.$this->file);
			// 如果不是允许的图片格式就退出
			if  (!in_array($ext,['gif','png','jpg','jpeg'])){
				echo '图片格式不符合要求'.$ext;
			    return false;
			}
			
			// 如果文件扩展名是jpg 修正为jpeg
			if ($ext=='jpg') {
				$ext = 'jpeg';
			}
	
			$this->ext = $ext;
			
			
		}
		
        // 构造方法  要压缩文件的位置, 压缩后的宽, 高
        public function __construct($path, $w=100, $h=100)
        {
            $this->path = rtrim($path,'/').'/';     // 修正路径;
            $this->w = $w;
            $this->h = $h;
        }
		
		// 输出生成缩略图  文件名
		public function toSmImg($file)
		{
			$this->file = $file;

			$this->getFuncName(); // 获取基本文件名+部分函数名
			
		    // 1.读入原文件到画布
			$createName = 'imagecreatefrom'.$this->ext;
			$srcImg = $createName($this->path.$this->file);
			$width  = imagesX($srcImg);
			$height = imagesY($srcImg);
			
			// 2.创建新画布
			$dstImg = imagecreatetruecolor($this->w, $this->h);
			
			// 防止png图黑
			$color = imagecolorallocate($dstImg,255,255,255); //分配颜色 
			imagecolortransparent($dstImg,$color);     //把这个颜色设置透明色
            imagefill($dstImg,0,0,$color);    //用颜色填充新画布
			
			// 3.复制
			imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $this->w, $this->h, $width, $height);
			
			// 4.输出成新文件
			$outName = 'image'.$this->ext;
			$outName($dstImg,$this->path.'sm_'.$this->file);
			
			// 5.销毁画布
			imagedestroy($srcImg);
			imagedestroy($dstImg);
		}
	
	
	
	}