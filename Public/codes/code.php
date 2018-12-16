<?php
  // 实例化  字体文件  宽  高  长度  字母/汉字
	new code('./msyhbd.ttf', 120, 40, 1);
	class code
	{
		private $hImg;
		private $fontFile;
		private $length;
		private $mb;
		private $w;
		private $h;
		private $code;
		
		// 
		public function __construct( $fontFile='./msyhbd.ttf', $w=120, $h=40, $length=4, $mb=0)
		{
			session_start();
			$this->fontFile = $fontFile;
			$this->length = $length;
			$this->mb = $mb;
			$this->w = $w;
			$this->h = $h;
			$this->hImg = imagecreatetruecolor($w, $h);  // 创建画布
			imagefill($this->hImg,0,0,$this->rndColor(true));
			$this->drawCode();
			$this->drawOther();
			header('content-type:image/jpeg');
			imagejpeg($this->hImg);
		}
		
		
		// 画验证码字符
		private function drawCode()
		{
			if ($this->mb) {
			   $str = '中华人民共和国北京市昌平区育荣教育园区兄弟连教学部景水赵小刚大坏蛋';
			   $arr = str_split($str, 3);
			} else {
			   $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			   $arr = str_split($str, 4);  //显示验证码中数字或者字母的个数
			}
			
			shuffle($arr);

			for($i = 0; $i < $this->length; $i++){
				$this -> code .= $arr[$i];
				imagettftext($this->hImg, 18, rand(-30,30), $i*30+10, 30, $this->rndColor(), $this->fontFile, $arr[$i]);
			}
			
			$_SESSION['code'] = $this->code;
		}
		
		// 干扰元素
		private function drawOther()
		{
			// 画干扰点
			for($i = 0; $i < 100; $i++){
				imagesetpixel($this->hImg, rand(0,$this->w), rand(0,$this->h), $this->rndColor(true));
			}
			
			// 画干扰线
			for($i = 0; $i < 13; $i++){
				imageline($this->hImg, rand(0,$this->w), rand(0,$this->h), rand(0,$this->w), rand(0,$this->h), $this->rndColor(true));
			}
		}
		
		// 随机颜色
		private function rndColor($flag=false)
		{
			if ($flag) {
				return imagecolorallocate($this->hImg, rand(128, 255), rand(128, 255), rand(128, 255));
			} else {
				return imagecolorallocate($this->hImg, rand(0, 127), rand(0, 127), rand(0, 127));
			}
		}
	}
	
	
	



?>