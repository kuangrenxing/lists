<?php
class Image
{//类定义开始

    /**
     * 取得图像信息
     */
    function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if( $imageInfo!== false) {
            if(function_exists('image_type_to_extension')){
                $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            }else{
                $extArray = array ( 1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp' ,
            		'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc',
            		'aiff', 'wbmp', 'xbm');
                $imageType = $extArray[$imageInfo[2]];
            }
            $imageSize = filesize($img);
            $info = array(
                "width"=>$imageInfo[0],
                "height"=>$imageInfo[1],
                "type"=>$imageType,
                "size"=>$imageSize,
                "mime"=>$imageInfo['mime']
            );
            return $info;
        }else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * 显示服务器图像文件
     * 支持URL方式
     +----------------------------------------------------------
     */
    function showImg($imgFile,$text='',$width=80,$height=30) {
        //获取图像文件信息
        $info = Image::getImageInfo($imgFile);
        if($info !== false) {
            $createFun  =   str_replace('/','createfrom',$info['mime']);
            $im = $createFun($imgFile); 
            if($im) {
                $ImageFun= str_replace('/','',$info['mime']);
                if(!empty($text)) {
                    $tc  = imagecolorallocate($im, 0, 0, 0);
                    imagestring($im, 3, 5, 5, $text, $tc);
                }
                if($info['type']=='png' || $info['type']=='gif') {
                	imagealphablending($im, false);//取消默认的混色模式
                	imagesavealpha($im,true);//设定保存完整的 alpha 通道信息                	
                }
                Header("Content-type: ".$info['mime']);
                $ImageFun($im);        	            	
                @ImageDestroy($im);
                return ;
            }
        }
        //获取或者创建图像文件失败则生成空白PNG图片
        $im  = imagecreatetruecolor($width, $height); 
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        imagestring($im, 4, 5, 5, "NO PIC", $tc);
        Image::output($im);
        return ;
    }

    /**
     +----------------------------------------------------------
     * 生成缩略图
     +----------------------------------------------------------
     */
    function thumb($image,$type='',$filename='',$maxWidth=200,$maxHeight=50,$interlace=true,$suffix='_thumb',$options=array()) 
    {
        // 获取原图信息
        $info  = Image::getImageInfo($image); 
         if($info !== false) {
            $srcWidth  = $info['width'];
            $srcHeight = $info['height'];
            $pathinfo = pathinfo($image);
            $type =  $pathinfo['extension'];
            $type = empty($type)?$info['type']:$type;
			$type = strtolower($type);
            $interlace  =  $interlace? 1:0;
            unset($info);
           
            if ($maxWidth == 600)
            {
            	$scale = min($maxWidth/$srcWidth, $maxHeight/$srcHeight); // 计算缩放比例
	            if($scale>=1) {
	                // 超过原图大小不再缩略
	                $width   =  $srcWidth;
	                $height  =  $srcHeight;
	            }else{
	                // 缩略图尺寸
	                $width  = (int)($srcWidth*$scale);
	                $height = (int)($srcHeight*$scale);
	            }
            }else{
	            $width  = (int)($maxWidth);
            	$height = (int)($maxHeight);
            }
            

            // 载入原图
            $createFun = 'ImageCreateFrom'.($type=='jpg'?'jpeg':$type);
            $srcImg     = $createFun($image); 

            $default_options = array(
	            'fullimage' => false,
	            'pos'       => 'center',
	            'bgcolor'   => '0xfff',
	            'enlarge'   => false,
	            'reduce'    => true,
	        );
        	$options = array_merge($default_options, $options);

       	 	// 创建目标图像
            if($type!='gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height); 
            else
                $thumbImg = imagecreate($width, $height);

	        // 根据源图计算长宽比
	        $ratio_w = doubleval($width) / doubleval($srcWidth);
	        $ratio_h = doubleval($height) / doubleval($srcHeight);

	        if ($options['fullimage'])
	        {
	            // 如果要保持完整图像，则选择最小的比率
	            $ratio = $ratio_w < $ratio_h ? $ratio_w : $ratio_h;
	        }
	        else
	        {
	            // 否则选择最大的比率
	            $ratio = $ratio_w > $ratio_h ? $ratio_w : $ratio_h;
	        }

        	if (!$options['enlarge'] && $ratio > 1) $ratio = 1;
        	if (!$options['reduce'] && $ratio < 1) $ratio = 1;

	        // 计算目标区域的宽高、位置
	        $dst_w = $srcWidth * $ratio;
	        $dst_h = $srcHeight * $ratio;

	        // 根据 pos 属性来决定如何定位
	        switch (strtolower($options['pos']))
	        {
	        case 'left':
	            $dst_x = 0;
	            $dst_y = ($height - $dst_h) / 2;
	            break;
	        case 'right':
	            $dst_x = $width - $dst_w;
	            $dst_y = ($height - $dst_h) / 2;
	            break;
	        case 'top':
	            $dst_x = ($width - $dst_w) / 2;
	            $dst_y = 0;
	            break;
	        case 'bottom':
	            $dst_x = ($width - $dst_w) / 2;
	            $dst_y = $height - $dst_h;
	            break;
	        case 'top-left':
	        case 'left-top':
	            $dst_x = $dst_y = 0;
	            break;
	        case 'top-right':
	        case 'right-top':
	            $dst_x = $width - $dst_w;
	            $dst_y = 0;
	            break;
	        case 'bottom-left':
	        case 'left-bottom':
	            $dst_x = 0;
	            $dst_y = $height - $dst_h;
	            break;
	        case 'bottom-right':
	        case 'right-bottom':
	            $dst_x = $width - $dst_w;
	            $dst_y = $height - $dst_h;
	            break;
	        case 'center':
	        default:
	            $dst_x = ($width - $dst_w) / 2;
	            $dst_y = ($height - $dst_h) / 2;
	        }

            // 复制图片
            if(function_exists("ImageCopyResampled"))
                ImageCopyResampled($thumbImg, $srcImg, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $srcWidth,$srcHeight); 
            else
                ImageCopyResized($thumbImg, $srcImg, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h,  $srcWidth,$srcHeight); 
            
            if('gif'==$type || 'png'==$type) {
                //imagealphablending($thumbImg, false);//取消默认的混色模式
                //imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                // 填充背景色
        		list ($r, $g, $b) = Image::hex2rgb($options['bgcolor'], '0xffffff');
                $background_color  =  imagecolorallocate($thumbImg,  $r,$g,$b);  //  指派一个绿色  
				imagecolortransparent($thumbImg,$background_color);  //  设置为透明色，若注释掉该行则输出绿色的图 
            }

            // 对jpeg图形设置隔行扫描
            if('jpg'==$type || 'jpeg'==$type) 	imageinterlace($thumbImg,$interlace);

            // 生成图片
            $imageFun = 'image'.($type=='jpg'?'jpeg':$type);
            $filename  = empty($filename)? substr($image,0,strrpos($image, '.')).$suffix.'.'.$type : $filename;

            $imageFun($thumbImg,$filename); 
            ImageDestroy($thumbImg);
            ImageDestroy($srcImg);
            return $filename;
         }
         return false;
    }
    
    /**
	 * 将 16 进制颜色值转换为 rgb 值
     *
     * 用法：
     * @code php
     * $color = '#369';
     * list($r, $g, $b) = Helper_Image::hex2rgb($color);
     * echo "red: {$r}, green: {$g}, blue: {$b}";
     * @endcode
     *
     * @param string $color 颜色值
     * @param string $default 使用无效颜色值时返回的默认颜色
	 *
	 * @return array 由 RGB 三色组成的数组
	 */
	function hex2rgb($color, $default = 'ffffff')
	{
        $hex = trim($color, '#&Hh');
        $len = strlen($hex);
        if ($len == 3)
        {
            $hex = "{$hex[0]}{$hex[0]}{$hex[1]}{$hex[1]}{$hex[2]}{$hex[2]}";
        }
        elseif ($len < 6)
        {
            $hex = $default;
        }
        $dec = hexdec($hex);
        return array(($dec >> 16) & 0xff, ($dec >> 8) & 0xff, $dec & 0xff);
	}
	
    /**
     +----------------------------------------------------------
     * 生成水印
     +----------------------------------------------------------
     */
    function waterMark($destination , $waterfilename  , $pos = 5 , $transparent = 25)
    {
        //$destination：图片地址
        //$waterfilename：水印图片地址
        //$pos：水印位置（1 左上 2 右上 3 左下 4 右下）
        //$transparent：透明度 默认 20

        $imagetype = array(1=>"gif",2=>"jpeg",3=>"png",6=>"wbmp");//图片类型
        $image_size = getimagesize($destination); //取得图片大小 list($width, $height, $type, $attr) 
        if($image_size[0] <30 && $image_size[0] > 1024)
        {
            return false;
        } 
        else 
        {
            $iinfo=getimagesize($destination,$iinfo);    
            $f ="imagecreatefrom".$imagetype[$iinfo[2]];//新建图像函数   $iinfo[2]为$type
            $simage = $f($destination);   
            $imagesize_mark = getimagesize($waterfilename);   
            $f ="imagecreatefrom".$imagetype[$imagesize_mark[2]];   
            $simage1 = $f($waterfilename); // 水印文件   
            // 合并2个文件   
            switch($pos)   
            {   
                case 1:  // 左上 
                    $x = 0;
                    $y = 0;
                    break;
                case 2:   // 右上 
                    $x = $image_size[0]-$imagesize_mark[0];
                    $y = 0;
                    break;      
                case 3: // 左下
                    $x = 0;
                    $y = $image_size[1]-$imagesize_mark[1];
                    break;   
                case 4:   // 右下
                    $x = $image_size[0]-$imagesize_mark[0]-13;
                    $y = $image_size[1]-$imagesize_mark[1]-13;
                    break;   
                case 5:  //居中靠下 3/4位置
                    $x = ($image_size[0]-$imagesize_mark[0])/2;
                    $y = ($image_size[1]-$imagesize_mark[1])*5/6;
                    break;
                case 6:  //居中 
                    $x = ($image_size[0]-$imagesize_mark[0])/2;
                    $y = ($image_size[1]-$imagesize_mark[1])/2;
                    break;
            }
            if($imagesize_mark[2] == 3)
            {
                imagecopy($simage,$simage1,$x,$y,0,0,$imagesize_mark[0],$imagesize_mark[1]);
            }
            else
            {
                imagecopymerge($simage,$simage1,$x,$y,0,0,$imagesize_mark[0],$imagesize_mark[1],$transparent);
            }
            // 输出   
            $f ="image".$imagetype[$iinfo[2]];   
            $f($simage,$destination,100);   
            imagedestroy($simage);    
            imagedestroy($simage1);
            return true; 
        }
    }
    
    /**
     +----------------------------------------------------------
     * 生成图像验证码
     +----------------------------------------------------------
     */
    function buildImageVerify($randval='',$length=4,$mode=1,$type='png',$width=48,$height=22,$verifyName='verify') 
    {
        if ("" == $randval)
        {
    		$randval = build_verify($length,$mode);
        	$_SESSION[$verifyName]= md5($randval);
        }
        $width = ($length*9+10)>$width?$length*9+10:$width;
        if ( $type!='gif' && function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor($width,$height);
        }else {
            $im = @imagecreate($width,$height);
        }
        $r = Array(225,255,255,223);
        $g = Array(225,236,237,255);
        $b = Array(225,236,166,125);
        $key = mt_rand(0,3);

        $backColor = imagecolorallocate($im, $r[$key],$g[$key],$b[$key]);    //背景色（随机）
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));                 //点颜色

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
        $stringColor = imagecolorallocate($im,mt_rand(0,200),mt_rand(0,120),mt_rand(0,120));
		// 干扰
		for($i=0;$i<10;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
		}
		for($i=0;$i<25;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
		}

        @imagestring($im, 5, 5, 3, $randval, $stringColor);
        Image::output($im,$type);
    }

    /**
     +----------------------------------------------------------
     * 生成高级图像验证码
     +----------------------------------------------------------
     */
    function showAdvVerify($type='png',$width=180,$height=40) 
    {
		$rand	=	range('a','z');
		shuffle($rand);
		$verifyCode	=	array_slice($rand,0,10);
        $letter = implode(" ",$verifyCode);
        $_SESSION['verifyCode'] = $verifyCode;
        $im = imagecreate($width,$height);
        $r = array(225,255,255,223);
        $g = array(225,236,237,255);
        $b = array(225,236,166,125);
        $key = mt_rand(0,3);
        $backColor = imagecolorallocate($im, $r[$key],$g[$key],$b[$key]); 
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
        $numberColor = imagecolorallocate($im, 255,rand(0,100), rand(0,100));
        $stringColor = imagecolorallocate($im, rand(0,100), rand(0,100), 255);
		// 添加干扰
		for($i=0;$i<10;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
		}
		for($i=0;$i<255;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$fontcolor);
		}
        imagestring($im, 5, 5, 1, "0 1 2 3 4 5 6 7 8 9", $numberColor);
        imagestring($im, 5, 5, 20, $letter, $stringColor);
        Image::output($im,$type);
    }

    /**
     +----------------------------------------------------------
     * 生成UPC-A条形码
     +----------------------------------------------------------
     */
    function UPCA($code,$type='png',$lw=2,$hi=100) { 
        static $Lencode = array('0001101','0011001','0010011','0111101','0100011', 
                         '0110001','0101111','0111011','0110111','0001011'); 
        static $Rencode = array('1110010','1100110','1101100','1000010','1011100', 
                         '1001110','1010000','1000100','1001000','1110100'); 
        $ends = '101'; 
        $center = '01010'; 
        /* UPC-A Must be 11 digits, we compute the checksum. */ 
        if ( strlen($code) != 11 ) { die("UPC-A Must be 11 digits."); } 
        /* Compute the EAN-13 Checksum digit */ 
        $ncode = '0'.$code; 
        $even = 0; $odd = 0; 
        for ($x=0;$x<12;$x++) { 
          if ($x % 2) { $odd += $ncode[$x]; } else { $even += $ncode[$x]; } 
        } 
        $code.=(10 - (($odd * 3 + $even) % 10)) % 10; 
        /* Create the bar encoding using a binary string */ 
        $bars=$ends; 
        $bars.=$Lencode[$code[0]]; 
        for($x=1;$x<6;$x++) { 
          $bars.=$Lencode[$code[$x]]; 
        } 
        $bars.=$center; 
        for($x=6;$x<12;$x++) { 
          $bars.=$Rencode[$code[$x]]; 
        } 
        $bars.=$ends; 
        /* Generate the Barcode Image */ 
        if ( $type!='gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($lw*95+30,$hi+30);
        }else {
            $im = imagecreate($lw*95+30,$hi+30);
        }
        $fg = ImageColorAllocate($im, 0, 0, 0); 
        $bg = ImageColorAllocate($im, 255, 255, 255); 
        ImageFilledRectangle($im, 0, 0, $lw*95+30, $hi+30, $bg); 
        $shift=10; 
        for ($x=0;$x<strlen($bars);$x++) { 
          if (($x<10) || ($x>=45 && $x<50) || ($x >=85)) { $sh=10; } else { $sh=0; } 
          if ($bars[$x] == '1') { $color = $fg; } else { $color = $bg; } 
          ImageFilledRectangle($im, ($x*$lw)+15,5,($x+1)*$lw+14,$hi+5+$sh,$color); 
        } 
        /* Add the Human Readable Label */ 
        ImageString($im,4,5,$hi-5,$code[0],$fg); 
        for ($x=0;$x<5;$x++) { 
          ImageString($im,5,$lw*(13+$x*6)+15,$hi+5,$code[$x+1],$fg); 
          ImageString($im,5,$lw*(53+$x*6)+15,$hi+5,$code[$x+6],$fg); 
        } 
        ImageString($im,4,$lw*95+17,$hi-5,$code[11],$fg); 
        /* Output the Header and Content. */ 
        Image::output($im,$type);
    } 

    function output($im,$type='png') 
    {
        header("Content-type: image/".$type);
        $ImageFun='Image'.$type;
        $ImageFun($im);
        imagedestroy($im);  	
    }

}//类定义结束
?>