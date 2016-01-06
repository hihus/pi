<?php
/**
 * @date 2012-2-15
 * @version 1.0
 * @brief
 *
 **/
class ImgCode { 
	//图片对象、宽度、高度、验证码长度 
	private $img; 
	private $img_width; 
	private $img_height; 
	private $len;
	//随机字符串、y轴坐标值、随机颜色 
	private $randnum; 
	private $y; 
	private $randcolor; 
	//背景色的红绿蓝，默认是浅灰色 
	public $red=238; 
	public $green=238; 
	public $blue=238; 
	/** 
	 * 可选设置：验证码类型、干扰点、干扰线、Y轴随机 
	 * 设为 false 表示不启用 
	 **/ 
	//默认是大小写数字混合型，1 2 3 分别表示 小写、大写、数字型 
	public $ext_num_type=''; 
	public $ext_pixel = false; //干扰点 
	public $ext_line = false; //干扰线 
	public $ext_rand_y= true; //Y轴随机 
	function __construct ($len=4,$im_height=25) { 
		// 验证码长度、图片宽度、高度是实例化类时必需的数据 
		$this->len = $len;
	    $im_width = $len * 15; 
		$this->img_width = $im_width; 
		$this->img_height= $im_height; 
		$this->img = imagecreate($im_width,$im_height); 
		$this->ext_num_type=''; 
		$this->ext_pixel = true; //干扰点 
		$this->ext_line = true; //干扰线 
		$this->ext_rand_y= true; //Y轴随机 
	} 
	// 设置图片背景颜色，默认是浅灰色背景 
	function setBGColor () { 
		imagecolorallocate($this->img,$this->red,$this->green,$this->blue); 
	} 
	// 获得任意位数的随机码 
	function getRandnum () { 
		$randnum = '';
		$an1 = 'abcdefghkmnpstuwxy'; 
		$an2 = 'ABCDEFGHJKMNPSTUWXY'; 
		$an3 = '23456789'; 
		if ($this->ext_num_type == '') $str = $an1.$an2.$an3; 
		if ($this->ext_num_type == 1) $str = $an1; 
		if ($this->ext_num_type == 2) $str = $an2; 
		if ($this->ext_num_type == 3) $str = $an3; 
		for ($i = 0; $i < $this->len; $i++) { 
			$start = rand(1,strlen($str) - 1); 
			$randnum .= substr($str,$start,1); 
		} 
		$this->randnum = $randnum; 
		return $randnum;
	} 
	// 获得验证码图片Y轴 
	function getY () { 
		if ($this->ext_rand_y) $this->y = rand(5, $this->img_height/5); 
		else $this->y = $this->img_height / 4 ; 
	} 
	// 获得随机色 
	function getRandColor () { 
		$this->randcolor = imagecolorallocate($this->img,rand(0,100),rand(0,150),rand(0,200)); 
	} 
	// 添加干扰点 
	function setExtPixel () { 
		if ($this->ext_pixel) { 
			for($i = 0; $i < 100; $i++){ 
				$this->getRandColor(); 
				imagesetpixel($this->img, rand()%100, rand()%100, $this->randcolor); 
			} 
		} 
	} 
	// 添加干扰线 
	function setExtLine () { 
		if ($this->ext_line) { 
			for($j = 0; $j < 2; $j++){ 
				$rand_x = rand(2, $this->img_width); 
				$rand_y = rand(2, $this->img_height); 
				$rand_x2 = rand(2, $this->img_width); 
				$rand_y2 = rand(2, $this->img_height); 
				$this->getRandColor(); 
				imageline($this->img, $rand_x, $rand_y, $rand_x2, $rand_y2, $this->randcolor); 
			} 
		} 
	} 
	/**创建验证码图像： 
	 * 建立画布（__construct函数） 
	 * 设置画布背景（$this->setBGColor();） 
	 * 获取随机字符串（$this->getRandnum ();） 
	 * 文字写到图片上（imagestring函数） 
	 * 添加干扰点/线（$this->setExtLine(); $this->setExtPixel();） 
	 * 输出图片 
	 **/ 
	function create ($randnum) { 
		$this->setBGColor(); 
		for($i = 0; $i < $this->len; $i++){ 
			$x = $i/$this->len * $this->img_width + rand(1, $this->len); 
			$this->getY(); 
			$this->getRandColor(); 
			imagestring($this->img, 6, $x, $this->y, substr($randnum, $i ,1), $this->randcolor); 
		} 
		$this->setExtLine(); 
		$this->setExtPixel(); 
		imagepng($this->img); 
		imagedestroy($this->img); //释放图像资源 
	} 
}
