<?php
/**
 * @file ImageDef.php
 * @date 2011/06/19 11:06:03
 * @version $Revision$ 
 * @brief 
 *  
 **/
final class Image
{
	//watermark default VALUES
	const DEFAULT_WATERMARK_POSITION=imagick::GRAVITY_SOUTHEAST; //该值为9，默认为图片的右下方
	const DEFAULT_WATERMARK_TRANSPARENT=0.8; //水印图片的透明度
	const DEFAULT_WATERMARK_ROTATE=-30; //水印的旋转度，逆时针30度
	const DEFAULT_WATERMARK_CONTENT='YOUA-LIFE'; //默认的文字水印内容
	const DEFAULT_WATERMARK_HEIGHT=60; //默认的水印图片高度
	const DEFAULT_WATERMARK_WIDTH=180; //默认的水印图片宽度
	const DEFAULT_WATERMARK_FONT=NULL; //默认的水印文字字体
	const DEFAULT_WATERMARK_COLOR='BLUE'; //默认的水印文字颜色
	const DEFAULT_WATERMARK_FONTSIZE=25; //默认的水印文字大小
	const DEFAULT_WATERMARK_LOGO_PATH="/home/mall/youa-php/lib/mcutil/logo.png";
	const DEFAULT_WATERMARK_WIDTH_RATE=0.18; //全屏打水印时，水印间宽度间隔参数
	const DEFAULT_WATERMARK_HEIGHT_RATE=0.42;//全屏打水印时，水印间高度间隔参数
	//Reserved field
	const DEFAULT_WATERMARK_TEXT_ID=1; //预留的id值，该值是针对 YOUA-LIFE 这部分文字
	const DEFAULT_WATERMARK_IMAGE_ID=2; //预留的图片id值，该值针对 有啊logo图片
	const DEFAULT_WATERMARK_TEXTIMAGE_ID=3;//预留的文字图片id值，该值针对 YOUA-LIFE生成的图片
	const DEFAULT_IMAGE_ERROR_ID=4; //预留的错误图片id值
	//compress default values
	const DEFAULT_COMPRESS_TYPE='JPEG'; //默认的压缩格式
	const DEFAULT_COMPRESS_QUALITY=80; //默认的压缩质量

	//cropthumbnail default values
	const CROPTHUMBNAIL_POSITION_NORTHWEST=1; //剪切时，截取图片的左上角
	const CROPTHUMBNAIL_POSITION_CENTER=2; //截取图片的中间
	const CROPTHUMBNAIL_POSITION_SOUTHEAST=3; //截取图片的右下角
	const DEFAULT_CROPTHUMBNAIL_BESTFIT=false; //默认不按照原图等比例缩放

	//encrypt keys
	const ENCRYPT_KEY_GENERAL='Eb'; //通用密钥
	const ENCRYPT_KEY_WATERMARK='EB_WATERMARK_KEY@!~'; //用于加密watid的水印密钥

	//some stuff for image class
	const WATERMARK_TYPE_TEXT=1;  //水印方式为文字
	const WATERMARK_TYPE_IMAGE=2; //水印方式为图片
	const WATERMARK_TYPE_TEXTIMAGE=3; //水印方式为文字图片

}


/**
 * @file Image.php
 * @author litianyi(litianyi@)
 * @date 2011/06/19 11:06:03
 * @version $Revision$ 
 * @brief 
 *  
 **/

class Image {
	var $imagick;
	static $defaultWatermarkValues=array(
		'position'=>ImageDef::DEFAULT_WATERMARK_POSITION,
		'transparent'=>ImageDef::DEFAULT_WATERMARK_TRANSPARENT,
		'rotate'=>ImageDef::DEFAULT_WATERMARK_ROTATE
	);
	static $defaultWatermarkTextValues=array(
		'font'=>ImageDef::DEFAULT_WATERMARK_FONT,
		'fontsize'=>ImageDef::DEFAULT_WATERMARK_FONTSIZE,
		'fontcolor'=>ImageDef::DEFAULT_WATERMARK_COLOR);
	static $defaultWatermarkTextImageValues=array(
		'fontsize'=>ImageDef::DEFAULT_WATERMARK_FONTSIZE,
		'font'=>ImageDef::DEFAULT_WATERMARK_FONT,
		'fontcolor'=>ImageDef::DEFAULT_WATERMARK_COLOR,
		'bgcolor'=>'transparent',
		'width'=>ImageDef::DEFAULT_WATERMARK_WIDTH,
		'height'=>ImageDef::DEFAULT_WATERMARK_HEIGHT
	);

	static $defaultWatermarkImageValues=array('width'=>0,'height'=>0);

	/**
	 * 根据图片信息生成一个imagick实例  
	 * @param $image 表示图片的信息，可能是图片的地址信息，也可能是图片的二进制信息
	 * @param $isfileBlob表示是否传递的值为图片的二进制内容，默认为否
	 */
	function __construct($image,$isfileBlob=false){
		//	parent::__construct($image);
		try{
			if(empty($image)){
				throw new Exception("GPS.u_arg image should not be empty!");
			}
			elseif(!$isfileBlob) {
				//检查图片是否正确
				if(!$this->isImageFile($image)){
					throw new Exception("GPS.u_arg $image is not a Image File or image format is not ".print_r(pi::get('support_formats'),true));

				}
				$this->imagick=new Imagick($image);
			}	
			else {
				$this->imagick=new Imagick();
				$this->imagick->readImageBlob($image);
			}
		}
		catch (Exception $e){
			throw new Exception("GPS.u_arg not a right format image while construct the image class! it can be only image address or image Blob");	
		}
		//判断传入图片的格式和大小
		$this->isAvailableFormat($this->imagick->getImageFormat());
		$uploadParams=pi::get('upload_params');
		if($this->imagick->getImageLength()>$uploadParams['max_length'] ){
			throw new Exception("GPS.u_arg sorry, this image length=". $this->imagick->getImageLength()." is not suportted by GPS right now!");
		}
	}

	/**
	 * watermark 
	 * 为图片打水印
	 * @param mixed $watermark  水印的内容
	 * @param 	 $type 水印的类型
	 * @param array $extValues=array(      
		 'type'=>type,表示水印的类型，该值可以是text或1、image或2、textimage或3;分别表示打文字水>
		 印，图片水印，文字图片水印。
		 'watid'=>watid,表示水印内容的id值。若给定了watid，则水印内容将通过watid到ttserver中获取>
		 相应的值。若watid没有给定则使用content参数指定的值、或者使用默认值
		 'content'=>content,表示水印内容。当watid值存在时，使用watid对应在ttserver中的值，该值>
		 无效。若watid不存在，则使用该值作为水印的内容。若两者都不存在，则使用默认值。
		 'position'=>position,表示水印在图片的位置，该值可以是[0——9]之间。0表示满屏的打水印1-9则分别对应着图片被分成的3*3大小的小格子。
		 'transparent'=>transparent,表示打水印时的不透明度。该值0-1，其中1表示完全不透明，0表示完>
		 全透明。
		 'rotate'=>rotate，表示水印图片的旋转度，该值可以是-360——360度。其中为正表示顺时针旋转，
		 为负表示逆时针旋转。
		 'font'=>font,'fontsize'=>fontsize,'fontcolor'=>fontcolor，这三个参数在type=text或textimage时有效，表示文字的字体，字体大小，字体颜色等。
		 'width'=>width,'height'=>height, 这两个参数在type= image或者textimage时有效，表示水印图片
		 的宽度和高度。
		 'bgcolor'=>bgcolor，该参数在type=textimage时有效，表示生成文字图片时，图片的背景色 
	 )
	 * @access public
	 * @return void
	 */
	function watermark(	$watermark,	$type=Image::DEFAULT_WATERMARK_TEXT,$extValues=array()){
		$values=array_merge(Image::$defaultWatermarkValues,Image::$defaultWatermarkTextImageValues,Image::$defaultWatermarkTextValues,Image::$defaultWatermarkImageValues);
		if(is_array($extValues)) {//若外部参数传入值为 $extValues=1,此时表示使用默认的值打水印
			$values=array_merge($values,$extValues);
		}
		//参数检查
		if(empty($type)){
			$type=ImageDef::WATERMARK_TYPE_TEXT;
		}
		//以下是打水印的处理逻辑
		switch(strtolower($type)){
		case (ImageDef::WATERMARK_TYPE_TEXT) :
		case 'text':
			$this->watermarkText($watermark,$extValues);
			break;
		case (ImageDef::WATERMARK_TYPE_TEXTIMAGE) :
		case 'textimage':
			$this->watermarkTextImage($watermark,$extValues);
			break;
		case (ImageDef::WATERMARK_TYPE_IMAGE) :
		case 'image':
			$this->watermarkImage($watermark,$extValues);
			break;
		default :
			throw new Exception("GPS.u_arg watermark type $type is not in  {'text',1,'textimage',2}!");
		}

	}

	/**
	 * watermarkImage 
	 * 为图片打上水印，该水印内容只能是图片。该方法将图片先按照指定的大小切割，然后放置到相应的位置 
	 * @param mixed $watermark  可以是图片的地址，也可以是图片的二进制信息。
	 * @param mixed $extvalues 表示打水印时传入的外部参数
	 array=( 
		 'position'=>position,表示水印在图片的位置，该值可以是[0——9]之间。0表示满屏的打水印，1-99
		 则分别对应着图片被分成的3*3大小的小格子。
		 'transparent'=>transparent,表示打水印时的不透明度。该值0-1，其中1表示完全不透明，0表示完>
		 全透明。
		 'rotate'=>rotate，表示水印图片的旋转度，该值可以是-360——360度。其中为正表示顺时针旋转，
		 为负表示逆时针旋转。
		 'width'=>width,'height'=>height) 这两个参数指定水印图片的宽度和高度
		 * @access private
		 * @return void
	 */
	function watermarkImage(
		$watermark=ImageDef::DEFAULT_WATERMARK_LOGO_PATH,
		$extValues=array()){
			$values=Image::$defaultWatermarkImageValues;
			$values=array_merge(Image::$defaultWatermarkValues,Image::$defaultWatermarkImageValues,$extValues);

			foreach(Image::$defaultWatermarkValues as $watermarkParam=>$watermarkValue){//处理通用的变量$position,$transparent,$rotate
				$$watermarkParam=$values[$watermarkParam];     
				if(empty($$watermarkParam)) {
					$$watermarkParam=$watermarkValue;
				} 
			}       
			//处理$font,$fontsize,$fontcolor这几个变量  
			foreach(Image::$defaultWatermarkImageValues as $watermarkImageParam=>$watermarkImageValue){
				$$watermarkImageParam=$values[$watermarkImageParam];
				if(empty($$watermarkImageParam)) {
					$$watermarkImageParam=$watermarkImageValue;
				}
			}           


			try {
				if($this->isImageFile($watermark)){//当传入的是图片地址时，直接生成imagick对象
					$mark=new Imagick($watermark);
				}
				else{//否则传入的值为图片的二进制内容，此时通过readImageBlob生成imagick对象
					$mark=new Imagick();
					$mark->readImageBlob($watermark);
				}
			}
			catch(Exception $e){
				throw new Exception("GPS.u_arg the arg watermark is not a right image!");
			}
			$mark->setFormat('png');
			$canvasWidth=$this->imagick->getImageWidth();
			$canvasHeight=$this->imagick->getImageHeight();
			if(empty($width)||!is_int($width)||$width>$mark->getImageWidth()){
				$width=$mark->getImageWidth();
			}
			if(empty($height)||!is_int($height)||$height>$mark->getImageHeight()){
				$height=$mark->getImageHeight();
			}
			$mark->cropThumbnailImage($width,$height);
			$mark->setImageOpacity(floatval($transparent));
			$mark->rotateImage('transparent',floatval($rotate));
			if(0==$position){
				$distanceWidth=intval(ImageDef::DEFAULT_WATERMARK_WIDTH_RATE * $canvasWidth);
				$distanceHeight=intval(ImageDef::DEFAULT_WATERMARK_HEIGHT_RATE * $canvasHeight);
				for($j=0;$j<$canvasWidth+$canvasHeight;$j+=$distanceHeight)
					for($i=0;$i<$canvasWidth;$i+=$distanceWidth){
						$this->imagick->compositeImage($mark,imagick::COMPOSITE_SATURATE,$i,$j);
					}
				return;
			}
			$midPoint=$this->getMidPointCoordinate($this->imagick->getImageWidth(),$this->imagick->getImageHeight(),$position);
			$xCoordinate=$midPoint['x']-($mark->getImageWidth()/2);
			$yCoordinate=$midPoint['y']-($mark->getImageHeight()/2);
			$this->imagick->compositeImage($mark, imagick::COMPOSITE_SATURATE,$xCoordinate,$yCoordinate);
		}
	// fontcolor是一个string可以是 "blue", "#0000ff", "rgb(0,0,255)", "cmyk(100,100,100,10)"
	/**
	 *watermarkText
	 * 为图片打上文字水印
	 * @param  $text 表示打水印的文字内容
	 * @param  $extvalues 
	 array(
		 'position'=>position,表示水印在图片的位置，该值可以是[0——9]之间。0表示满屏的打水印，1-99
		 则分别对应着图片被分成的3*3大小的小格子。
		 'transparent'=>transparent,表示打水印时的不透明度。该值0-1，其中1表示完全不透明，0表示完>
		 全透明。
		 'rotate'=>rotate，表示水印图片的旋转度，该值可以是-360——360度。其中为正表示顺时针旋转，
		 为负表示逆时针旋转。
		 'font'=>font, 字体
		 'fontsize'=>fontsize, 字体大小
		 'fontcolor'=>fontcolor 一个string可以是 "blue", "#0000ff", "rgb(0,0,255)", "cmyk(100,100,100,10)等



	 )
	 * @access public
	 * @return viod
	 */
	function watermarkText(
		$text=ImageDef::DEFAULT_WATERMARK_CONTENT,
		$extValues=array()){
			$values=array_merge(Image::$defaultWatermarkValues,Image::$defaultWatermarkTextValues,$extValues);
			foreach(Image::$defaultWatermarkValues as $watermarkParam=>$watermarkValue){//处理通用的变量$position,$transparent,$rotate
				$$watermarkParam=$values[$watermarkParam];		
				if(empty($$watermarkParam)) {
					$$watermarkParam=$watermarkValue;
				}
			}		

			//处理$font,$fontsize,$fontcolor这几个变量	
			foreach(Image::$defaultWatermarkTextValues as $watermarkTextParam=>$watermarkTextValue){
				$$watermarkTextParam=$values[$watermarkTextParam];
				if(empty($$watermarkTextParam)) {
					$$watermarkTextParam=$watermarkTextValue;
				}
			}			
			$draw=new ImagickDraw();
			$height=$this->imagick->getImageHeight();
			$width=$this->imagick->getImageWidth();
			if(!empty($transparent)){
				$draw->setFillOpacity($transparent);
			}
			if(!empty($fontsize)){
				$draw->setFontSize(floatval($fontsize));
			}
			if(null!=($font)){
				$draw->setFont($font);
			}
			if(!empty($fontcolor)){
				$pixel=new ImagickPixel();
				$setSuccess=$pixel->setColor($fontcolor);
				if($setSuccess){
					$draw->setFillColor($pixel);
				}
			}
			$draw->setGravity(imagick::GRAVITY_CENTER);
			if($position>9||$position<0){
				$position=ImageDef::DEFAULT_WATERMARK_POSITION;
			}
			//整个页面都打水印
			if(0==$position){
				$distanceWidth=intval(ImageDef::DEFAULT_WATERMARK_WIDTH_RATE * $width);
				$distanceHeight=intval(ImageDef::DEFAULT_WATERMARK_HEIGHT_RATE * $height);
				for($j=0;$j<$width+$height;$j+=$distanceHeight)
					for($i=0;$i<$width;$i+=$distanceWidth){
						$this->imagick->annotateImage($draw,$i-$width/2,$j-$height/2,floatval($rotate),$text);
					}
				return;
			}
			$mid=$this->getMidPointCoordinate($width,$height,$position);
			$pos['x']=$mid['x']-($width/2);
			$pos['y']=$mid['y']-($height/2);	
			$this->imagick->annotateImage($draw,$pos['x'],$pos['y'],$rotate,$text);

		}
	/**
	 * annotate 
	 * 为图片注释，相当于打文字水印 
	 * @param mixed $text 文本内容
	 * @param mixed $px 注释起点位置的x坐标
	 * @param mixed $py 注释起点位置的y坐标
	 * @param array $extValues 外部参数，设置透明度，旋转度以及文本的颜色，字体，大小等
     array(
         'transparent'=>transparent,表示打水印时的不透明度。该值0-1，其中1表示完全不透明，0表示完>
         全透明。
         'rotate'=>rotate，表示水印图片的旋转度，该值可以是-360——360度。其中为正表示顺时针旋转，  
         为负表示逆时针旋转。
         'font'=>font,'fontsize'=>fontsize,'fontcolor'=>fontcolor 详见watermarkText
         'width'=>width,'height'=>height, 参数详见watermarkImage
         'bgcolor'=>bgcolor，表示生成文字图片时，图片的背景色 

     ) 
	 * @access public
	 * @return void
	 */
	public function annotate($text,$px,$py,$extValues=array()){

		$values=array_merge(Image::$defaultWatermarkValues,Image::$defaultWatermarkTextValues,$extValues);
		foreach(Image::$defaultWatermarkValues as $watermarkParam=>$watermarkValue){//处理通用的变量$transparent,$rotate
			$$watermarkParam=$values[$watermarkParam];      
			if(empty($$watermarkParam)) {
				$$watermarkParam=$watermarkValue;
			}
		}       
		if(isset($position)) {//在上部初始化时，已经初始化了position参数，但是该函数不需要。故释放掉
			unset($position);
		}
		//处理$font,$fontsize,$fontcolor这几个变量  
		foreach(Image::$defaultWatermarkTextValues as $watermarkTextParam=>$watermarkTextValue){
			$$watermarkTextParam=$values[$watermarkTextParam];
			if(empty($$watermarkTextParam)) {
				$$watermarkTextParam=$watermarkTextValue;
			}
		}       
		if(!is_numeric($px)||!is_numeric($py)){
			throw new Exception("GPS.u_arg  when call the annotate funciton,the coordinate params px=$px or py=$py passed in is not a numeric!");
		}

		$draw=new ImagickDraw();
		if(!empty($transparent)){
			$draw->setFillOpacity($transparent);
		}
		if(!empty($fontsize)){
			$draw->setFontSize(floatval($fontsize));
		}
		if(null!=($font)){
			$draw->setFont($font);
		}
		if(!empty($fontcolor)){
			$pixel=new ImagickPixel();
			$setSuccess=$pixel->setColor($fontcolor);
			if($setSuccess){
				$draw->setFillColor($pixel);
			}
		}
		$this->imagick->annotateImage($draw,$px,$py,$rotate,$text);

	}

	/**
	 * watermarkTextImage
	 * 为图片打上文字图片的水印，即是先将文字生成图片，然后将该文字图片打在背景图上。
	 * @param $text  打水印的文字内容
	 * @param $extValues 
	 array(
		 'position'=>position,表示水印在图片的位置，该值可以是[0——9]之间。0表示满屏的打水印，1-99
		 则分别对应着图片被分成的3*3大小的小格子。
		 'transparent'=>transparent,表示打水印时的不透明度。该值0-1，其中1表示完全不透明，0表示完>
		 全透明。
		 'rotate'=>rotate，表示水印图片的旋转度，该值可以是-360——360度。其中为正表示顺时针旋转，
		 为负表示逆时针旋转。
		 'font'=>font,'fontsize'=>fontsize,'fontcolor'=>fontcolor 详见watermarkText
		 'width'=>width,'height'=>height, 参数详见watermarkImage
		 'bgcolor'=>bgcolor，表示生成文字图片时，图片的背景色 

	 )
	 * @access public
	 * @return void
	 */
	function watermarkTextImage(
		$text=ImageDef::DEFAULT_WATERMARK_CONTENT,
		$extValues=array()){
			$values=array_merge(Image::$defaultWatermarkValues,Image::$defaultWatermarkTextImageValues,$extValues);
			$textImageBlob= self::text2Image($text,$values);

			$this->watermarkImage($textImageBlob,$values);

		}
	/**
	 * compress 对图片进行压缩
	 * @param $compression string 表示压缩的格式。当前系统支持的压缩格式为bzip，jpeg，jpeg2000，zip。
	 * @param $quality int 表示压缩后的质量。范围为0-100,其中0表示完全失真，100表示完全不失真
	 */
	function compress(
		$compression=ImageDef::DEFAULT_COMPRESS_TYPE,
		$quality=ImageDef::DEFAULT_COMPRESS_QUALITY
	){//参数检查
		$paramsArray=array(
			'compression'=>ImageDef::DEFAULT_COMPRESS_TYPE,
			'quality'=>ImageDef::DEFAULT_COMPRESS_QUALITY
		);
		foreach($paramsArray as $param=>$defaultValue){
			if(empty($$param)) {
				$$param=$defaultValue;
			}
		}
		$compress_format=pi::get('compress_format');
		$compression=strtolower($compression);
		if(!in_array($compression,array_keys($compress_format))){
			throw new Exception("GPS.u_arg the type $compression is not support in".print_r($compress_format,true));

		}   
		if('gif'!=strtolower($this->imagick->getImageFormat())) {
			$this->imagick->setImageFormat('jpg');
		}
		$this->imagick->setImageCompression($compress_format[$compression]);
		$this->imagick->setImageCompressionQuality($quality);
		$this->imagick->stripImage();

	}



	/**
	 *  cropTrumbnail 对图片进行剪切缩放
	 *  @param $width 表示缩放后图片的宽度
	 *  @param $height 表示缩放后图片的高度
	 *  @param $position 表示剪切缩放的位置，范围为1——3，其1表示左上方，2表示中间，3表示右下方
	 *  @param $bestfit 是否按照原有大小，进行等比例缩放。true表示等比例缩放，false表示不进行等比例缩放，默认为false
	 *  @access public
	 *  @return void
	 *
	 */
	function cropThumbnail($width,$height,
		$position=ImageDef::CROPTHUMBNAIL_POSITION_CENTER,
		$bestfit=ImageDef::DEFAULT_CROPTHUMBNAIL_BESTFIT
	){
		//参数判断
		if(empty($width)&&empty($height)){
			//表示无须剪切，则直接返回
			return ;
		}
		if(empty($width)||!is_numeric($width)){
			$width=$this->imagick->getImageWidth();
		}
		if(empty($height)||!is_numeric($height)){
			$height=$this->imagick->getImageHeight();
		}
		if(empty($position)){
			$position=ImageDef::CROPTHUMBNAIL_POSITION_CENTER;
		}
		if(empty($bestfit)){
			$bestfit=ImageDef::DEFAULT_CROPTHUMBNAIL_BESTFIT;
		}

		//以下是对图片的剪切缩放的处理过程
		if($bestfit) {
			$result=$this->imagick->resizeImage($width,$height,imagick::FILTER_CATROM,1,$bestfit);
		}//居中剪切并缩放
		elseif($position==ImageDef::CROPTHUMBNAIL_POSITION_CENTER){
			$result=$this->imagick->cropThumbnailImage($width,$height);
		}   
		else{
			$xOld=$this->imagick->getImageWidth();
			$yOld=$this->imagick->getImageHeight();
			//剪切Y轴
			if($width/$height>$xOld/$yOld){
				if($position==ImageDef::CROPTHUMBNAIL_POSITION_NORTHWEST) {//提取上部
					$this->imagick->cropImage($xOld,$xOld*$height/$width, 0,0);
				}
				elseif($position==ImageDef::CROPTHUMBNAIL_POSITION_SOUTHEAST){//提取下部
					$this->imagick->cropImage($xOld,$xOld*$height/$width,0,($yOld-$xOld*$height/$width));
				}
				else{
					throw new Exception("GPS.u_arg position $position is not allowed while calling the cropthumbnail!");
				}                      

			}//剪切X轴
			elseif($width/$height<$xOld/$yOld){
				if($position==ImageDef::CROPTHUMBNAIL_POSITION_NORTHWEST){//提取左部
					$this->imagick->cropImage($width*$yOld/$height,$yOld,0,0);
				}
				elseif($position==ImageDef::CROPTHUMBNAIL_POSITION_SOUTHEAST){//提取右部
					$this->imagick->cropImage($width*$yOld/$height,$yOld,$xOld-$yOld*$width/$height,0);
				}
				else{
					throw new Exception("GPS.u_arg position $position is not allowed while calling the cropthumbnail method!");
				}
			}
			$this->imagick->thumbnailImage($width,$height);
		}
	}

	/**
	 * writeFormatImage 
	 * 按照一定的格式将图片写到文件系统
	 * @param mixed $fileName 文件名
	 * @param mixed $format 文件格式
	 * @access public
	 * @return void
	 */
	function writeFormatImage($fileName,$format){
		if(!$this->isAvailableFormat($format)){
			throw new Exception("GPS.u_arg sorry,the format $format is not supported by the GPS right now!");
		}
		$this->imagick->writeImage($fileName.".".$format);

	}

	/**
	 * changeFormat 
	 * 更改图片的格式 
	 * @param mixed $to 表示需要转成的格式 
	 * @access public
	 * @return void
	 */
	function changeFormat($to){
		if(!$this->isAvailableFormat($to)){
			throw new Exception("GPS.u_arg $to is not supported by the GPS!");
		}

		$this->imagick->setFormat($to);

	}

	/**
	 * isAvailableFormat 
	 * 查询当前的图片格式是否是系统支持的格式
	 * @param mixed $format 当前图片的格式
	 * @access private
	 * @return void
	 */
	private function isAvailableFormat($format){
		if(!empty($format)&&in_array(strtolower($format),pi::get('support_formats'))) 
		{
			return true;
		}
		return false;

	}

	/**
	 * isImageFile 
	 * 查看传入的文件内容是否是图片文件
	 * @param mixed $input 输入的文件名
	 * @access private
	 * @return void
	 */
	function isImageFile($input){
		if(file_exists($input)){
			$pathInfo=pathinfo($input);
			$fileExtension=$pathInfo["extension"];
			if(in_array(strtolower($fileExtension),pi::get('support_formats'))){
				return true;
			}
		}
		return false;

	}

	/**
	 * getMidPointCoordinate 
	 * 指定图片某一位置的中点值 
	 * @param mixed $width 图片的宽
	 * @param mixed $height 图片的高
	 * @param mixed $position 指定的图片位置，1-9，详见watermar方法中$position
	 * @access public
	 * @return array{
		 "x"  表示x坐标
			 "y"  表示y坐标
}
	 */
	function getMidPointCoordinate($width,$height,$position){
		$x=($position%3==0?3:$position%3)*$width/3-$width/6;
		$y=(intval(($position>1?($position-1):$position)/3)*$height/3)+$height/6;
		return array('x'=>$x,'y'=>$y);
	}

	/**
	 * getImageImagick 
	 * 获取属性值imagick的实例
	 * @access public
	 * @return void
	 */
	function getImagick(){
		return $this->imagick;
	}	

	/**
	 * getImageBlob 
	 * 获取图片的二进制文件 
	 * @access public
	 * @return void
	 */
	function getImageBlob(){
		return $this->imagick->getImageBlob();
	}

	/**
	 * text2Image 
	 * 将文字生成图片  
	 * @param $watermark 表示文字的内容
	 * @param $extValues 
	 array(
		 'width'=>$width, 表示生成图片的宽度
		 'height'=>$height， 表示生成图片的高度
		 'font'=>$font,  表示文字的字体
		 'fontsize'=>$fontsize，表示文字的大小
		 'color'=>$fontcolor, 表示文字的颜色
		 'bgcolor'=>$bgcolor,表示背景的颜色
	 )
	 * @access public static
	 * @return 生成图片的二进制信息 
	 */
	static function text2Image($watermark=ImageDef::DEFAULT_WATERMARK_CONTENT,
		$extValues=array() )
		//		$width=ImageDef::DEFAULT_WATERMARK_WIDTH,
		//		$height=ImageDef::DEFAULT_WATERMARK_HEIGHT,
		//		$font=null,
		//		$fontsize=ImageDef::DEFAULT_WATERMARK_FONTSIZE,
		//		$color=ImageDef::DEFAULT_WATERMARK_COLOR,
		//		$bgcolor='transparent'
		//	){    
	{                                       
		//参数检查，并初始化	
		$paramsArray=array('width'=>ImageDef::DEFAULT_WATERMARK_WIDTH,
			'height'=>ImageDef::DEFAULT_WATERMARK_HEIGHT,
			'font'=>ImageDef::DEFAULT_WATERMARK_FONT,
			'fontsize'=>ImageDef::DEFAULT_WATERMARK_FONTSIZE,
			'fontcolor'=>ImageDef::DEFAULT_WATERMARK_COLOR,
			'bgcolor'=>"transparent");
		foreach($paramsArray as $param=>$value){
			$$param=isset($extValues[$param])?$extValues[$param]:'';
			if(empty($$param)) {	
				$$param=$value;
			}
		}

		$draw=new ImagickDraw();
		$draw->setFontSize($fontsize);
		$draw->setGravity(5);
		if(null!=$font) {
			$draw->setFont($font);
		}
		$pixel=new ImagickPixel();
		$pixel->setColor($fontcolor);

		$draw->setFillColor($pixel);
		$draw->annotation(0,0,$watermark);
		$imagick=new Imagick();
		$imagick->newImage($width,$height,new ImagickPixel($bgcolor));
		$imagick->setImageFormat('png');
		$imagick->drawImage($draw);
		return $imagick->getImageBlob();
	}


}
/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */

