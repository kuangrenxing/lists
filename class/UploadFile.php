<?php 
class UploadFile
{//类定义开始

    // 上传文件的最大值
    public $maxSize = -1;

    // 是否支持多文件上传
    public $supportMulti = true;

    // 允许上传的文件后缀
	//  留空不作后缀检查
    public $allowExts = array();

    // 允许上传的文件类型
	// 留空不做检查
    public $allowTypes = array();

	// 使用对上传图片进行缩略图处理
    public $thumb   =  false;
	// 缩略图最大宽度
    public $thumbMaxWidth;
	// 缩略图最大高度
    public $thumbMaxHeight;
	// 缩略图后缀
    public $thumbSuffix   =  '_thumb';
	// 压缩图片文件上传
	public $zipImages = false;
	
	//图片增加水印
	public $water = false;
	//水印图片路径
	public $waterImage = '';

    // 上传文件保存路径
    public $savePath = '';

	// 存在同名是否覆盖
	public $uploadReplace = false;

    // 上传文件命名规则
    // 例如可以是 time uniqid com_create_guid 等
    // 必须是一个无需任何参数的函数名 可以使用自定义函数
    public $saveRule = '';

    // 上传文件Hash规则函数名
    // 例如可以是 md5_file sha1_file 等
    public $hashType = 'md5_file';

    // 错误信息
    private $error = '';

    // 上传成功的文件信息
    private $uploadFileInfo ;

    public function __construct($maxSize='',$allowExts='',$allowTypes='',$savePath='' , $saveRule='')
    {
        if(!empty($maxSize) && is_numeric($maxSize)) {
            $this->maxSize = $maxSize;
        }
        if(!empty($allowExts)) {
            if(is_array($allowExts)) {
            	$this->allowExts = array_map('strtolower',$allowExts);
            }else {
            	$this->allowExts = explode(',',strtolower($allowExts));
            }
        }
        if(!empty($allowTypes)) {
            if(is_array($allowTypes)) {
            	$this->allowTypes = array_map('strtolower',$allowTypes);
            }else {
            	$this->allowTypes = explode(',',strtolower($allowTypes));
            }
        }
        if(!empty($saveRule)) {
            $this->saveRule = $saveRule;
        }else{
			$this->saveRule = $this->timeStr();
		}
        $this->savePath = $savePath;
    }

    private function save($file) 
    {
        $filename = $file['savepath'].$file['savename'];
		if(!$this->uploadReplace && file_exists($filename)) {
			// 不覆盖同名文件
			$this->error	=	'文件已经存在！'.$filename;
			return false;
		}
        if(!move_uploaded_file($file['tmp_name'], $filename)) {
			$this->error = '文件上传保存错误！';
            return false;
        }
        if($this->thumb) {
        	// 生成图像缩略图
            require_once("Image.php");
            $image =  Image::getImageInfo($filename);
            if(false !== $image) {
            	//是图像文件生成缩略图
                $thumbWidth = explode(',',$this->thumbMaxWidth);
                $thumbHeight   =  explode(',',$this->thumbMaxHeight);
                $thumbSuffix = explode(',',$this->thumbSuffix);
                for($i=0,$len=count($thumbWidth); $i<$len; $i++) {
                    $thumbname = Image::thumb($filename,'','',$thumbWidth[$i],$thumbHeight[$i],true,$thumbSuffix[$i]);                	
                }
            }
            
            //使用imageMagick处理图像
//            require_once("ImageMagick.php");
//            $image =  ImageMagick::checkImage($filename);
//            
//            if(false !== $image) {
//            	//是图像文件生成缩略图
//                $thumbWidth = explode(',',$this->thumbMaxWidth);
//                $thumbHeight   =  explode(',',$this->thumbMaxHeight);
//                $thumbSuffix = explode(',',$this->thumbSuffix);
//                for($i=0,$len=count($thumbWidth); $i<$len; $i++) {
//                    $thumbname = ImageMagick::MagickThumb($filename,'',$thumbWidth[$i],$thumbHeight[$i],$thumbSuffix[$i],600);                	
//                }
//            }
            unlink($filename);
        }
		if($this->zipImags) {
			// TODO 对图片压缩包在线解压

		}
		
		if ($this->water){
			// 增加水印
            require_once("Image.php");
            Image::waterMark($filename , $this->waterImage , 6 , 25);
		}
        return true;
    }

    public function timeStr()
    {
    	$code = "";
		$NumArray = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		for($i = 0 ; $i < 5 ; $i ++)
		{
			$code .= $NumArray[rand(0 , 25)];
		}
		$code .= date("YmdHis");
		
		return $code;
    }

    public function upload($savePath ='') 
    {
        //如果不指定保存文件名，则由系统默认
        if(empty($savepath)) {
            $savePath = $this->savePath;
        }
        // 检查上传目录
        if(!is_dir($savePath)) {
			// 检查目录是否编码后的
			if(is_dir(base64_decode($savePath))) {
				$savePath	=	base64_decode($savePath);
			}else{
				$this->error  =  '上传目录'.$savePath.'不存在';
				return false;
			}
        }else {
        	if(!is_writeable($savePath)) {
                $this->error  =  '上传目录'.$savePath.'不可写';
                return false;        		
        	}
        }
        $fileInfo = array();
        $isUpload   = false;

		// 获取上传的文件信息
		// 对$_FILES数组信息处理
		if(!empty($_FILES['name'])) {
			$files	 =	 $this->dealFiles($_FILES);
		}else{
			$files	 =	 $_FILES;
		}
        foreach($files as $key => $file) {
            //过滤无效的上传
            if(!empty($file['name'])) {
                //登记上传文件的扩展信息
                $file['extension']  = $this->getExt($file['name']);
                $file['savepath']   = $savePath;
                $file['savename']   = $this->getSaveName($file);
                if($file['error']!== 0) {
                    //文件上传失败
                    //捕获错误代码
                    $this->error($file['error']);
                    return false;
                }
                //文件上传成功，进行自定义规则检查

                //检查文件大小
                if(!$this->checkSize($file['size'])) {
                    $this->error = '上传文件大小不符！';
                    return false;
                }

                //检查文件Mime类型
                if(!$this->checkType($file['type'])) {
                    $this->error = '上传文件MIME类型不允许！';
                    return false;
                }
                //检查文件类型
                if(!$this->checkExt($file['extension'])) {
                    $this->error = '上传文件类型不允许';
                    return false;
                }

                //检查是否合法上传
                if(!$this->checkUpload($file['tmp_name'])) {
                    $this->error = '非法上传文件！';
                    return false;
                }

                //保存上传文件
                if(!$this->save($file)) {
                    //$this->error = $file['error'];
                    return false;
                }

                //上传成功后保存文件信息，供其他地方调用
                unset($file['tmp_name'],$file['error']);
                $fileInfo[] = $file;
                $isUpload   = true;
            }
        }
        if($isUpload) {
            $this->uploadFileInfo = $fileInfo;    
            return true;
        }else {
            $this->error  =  '没有选择上传文件';       
            return false;
        }
    }

	private function dealFiles(&$files) {
	   $fileArray = array();
	   $count = count($files['name']);
	   $keys = array_keys($files);
	   for ($i=0; $i<$count; $i++) {
		   foreach ($keys as $key) {
			   $fileArray[$i][$key] = $files[$key][$i];
		   }
	   }
	   return $fileArray;
	}

    protected function error($errorNo) 
    {
         switch($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return ;
    }

    private function getSaveName($filename) 
    {
        $rule = $this->saveRule;
        if(empty($rule)) {//没有定义命名规则，则保持文件名不变
            $saveName = $filename['name'];
        }else {
            if(function_exists($rule)) {
                //使用函数生成一个唯一文件标识号
            	$saveName = $rule().".".$filename['extension'];
            }else {
                //使用给定的文件名作为标识号
//            	$saveName = $rule.".".$filename['extension'];
				$saveName = $rule.".".$filename['extension'];
            }
        }
        return $saveName;
    }

    private function checkType($type) 
    {
        if(!empty($this->allowTypes)) {
            return in_array(strtolower($type),$this->allowTypes);
        }
        return true;
    }

    private function checkExt($ext) 
    {
        if(!empty($this->allowExts)) {
            return in_array(strtolower($ext),$this->allowExts);
        }
        return true;
    }

    private function checkSize($size) 
    {
        return !($size > $this->maxSize) || (-1 == $this->maxSize);
    }

    private function checkUpload($filename) 
    {
        return is_uploaded_file($filename);
    }

    private function getExt($filename) 
    {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }

    public function getUploadFileInfo() 
    {
        return $this->uploadFileInfo;
    }

    public function getErrorMsg() 
    {
        return $this->error;
    }

}//类定义结束
?>