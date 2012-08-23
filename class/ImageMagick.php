<?php
/**
* 基于MagickWand的图片缩略图程序， php magick thumb
* Author: smallchicken
* E-mail: microji@163.com
* Version: 1.0
* 用法：直接调用 magick_thumb函数，传入参数：
* magick_thumb($src_image_dir.$src_image,$dst_thumb_dir.$dst_image,100,100,1);
* $src_file ： 原文件路径 , $dst_file ：保存文件路径 , $dst_width ：生成文件宽 , $dst_height：生成文件高，$mode=1 裁剪方式
* GIF动画缩略到不大于传入的高宽值，并且保留动画效果，GIF非动画格式和其他格式一样处理。
* 参数 $mode 说明：
* $mode=1 ：生成固定高宽的图像，图片缩放后铺满，为了铺满，会有部分图像裁剪掉。
* $mode=2 ：生成固定高宽的图像，图片缩放后不一定铺满，但保留全部图像信息，即不裁剪，添加补白。
* $mode=3 ：生成图像高宽不大于给定高宽，并且以缩放后的实际大小保持。
*
*
function magick_thumb($src_file,$dst_file,$dst_width,$dst_height,$mode=1) {
	$mp = new ImageMagick($src_file);
	return $mp->MagickThumb($dst_width,$dst_height,$dst_file,$suffix,$mode);
}
*/

class ImageMagick 
{
	function __construct() {
		//
	}
	
	function getImageInfo($src_file)
	{
		$imageInfo['file']	= $src_file;
		$imageInfo['mw']   	= NewMagickWand();
		$imageInfo['is_img']		= MagickReadImage($imageInfo['mw'],$imageInfo['file']);
		if(!$imageInfo['is_img']) return false;
		$imageInfo['format'] 		= MagickGetImageFormat($imageInfo['mw']);
		$imageInfo['src_width'] 	= MagickGetImageWidth($imageInfo['mw']);
		$imageInfo['src_height'] 	= MagickGetImageHeight($imageInfo['mw']);
		
		if ($imageInfo['format'] == "JPEG")
			$imageInfo['src_ext'] = "jpg";
		elseif ($imageInfo['format'] == "GIF")
			$imageInfo['src_ext'] = "gif";
		elseif ($imageInfo['format'] == "PNG")
			$imageInfo['src_ext'] = "png";
		else
			return false;
		return $imageInfo;
	}
	
	function checkImage($img)
	{
		$imageInfo = getimagesize($img);
		if ($imageInfo === false)
			return false;
	}
	
	function IsAnimation($src_file)
	{
		$fp		= fopen($src_file, 'rb');
		$image_head = fread($fp,1024);
		fclose($fp);
		return preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head) ? true : false;
	}
	
	function MagickThumb($src_file , $dst_file = '' , $dst_width , $dst_height , $suffix='_thumb' , $bigWidth = 600)
	{
		$info	= ImageMagick::getImageInfo($src_file);
		if(!$info['is_img']) return false;
		$src_width 	= $info['src_width'];
		$src_height	= $info['src_height'];
		$ratio_w	= 1.0 * $dst_width / $src_width;
		$ratio_h	= 1.0 * $dst_height / $src_height;
		$ratio		= 1.0;
		$type 		= $info['src_ext'];
		$dst_file  	= empty($dst_file)? substr($src_file,0,strrpos($src_file, '.')).$suffix.'.'.$type : $dst_file;
		if(strtolower($info['format'])=='gif') {
			if (ImageMagick::IsAnimation($src_file)) {
				ImageMagick::ResizeGif($info['mw'],$dst_file, $dst_width, $dst_height);return;
			}
		}
				
		if ($dst_width == $bigWidth)
		{
			if($ratio_w > 1 && $ratio_h > 1) {
				ImageMagick::SaveImage($info['mw'], $dst_file,$info['format']);
			}else {
				$ratio = $ratio_w > $ratio_h ? $ratio_h : $ratio_w;
				$tmp_w = (int)($src_width * $ratio);
				$tmp_h = (int)($src_height * $ratio);
				ImageMagick::ResizeImage($info['mw'], $tmp_w, $tmp_h);
				ImageMagick::SaveImage($info['mw'], $dst_file,$info['format']);
			}
		}else{
			
			if($ratio_w < 1 && $ratio_h < 1) { // 都缩小
				$ratio = $ratio_w < $ratio_h ? $ratio_h : $ratio_w;
				$crop_width = (int)($dst_width / $ratio);
				$crop_height = (int)($dst_height / $ratio);
				$crop_x = (int) ($src_width-$crop_width)/2 ;
				$crop_y = (int) ($src_height-$crop_height)/2 ;
				MagickCropImage($info['mw'], $crop_width, $crop_height, $crop_x, $crop_y);
				ImageMagick::ResizeImage($info['mw'], $dst_width, $dst_height);
				ImageMagick::SaveImage($info['mw'], $dst_file,$info['format']);
			}elseif($ratio_w > 1 && $ratio_h > 1) {
				$dst_mkwd=NewMagickWand();
				MagickNewImage($dst_mkwd,$dst_width,$dst_height,'#FFFFFF');
				$dst_x = (int) abs($dst_width - $src_width) / 2 ;
				$dst_y = (int) abs($dst_height -$src_height) / 2;
				MagickCompositeImage($dst_mkwd ,$info['mw'], MW_OverCompositeOp, $dst_x, $dst_y ) ; // 合并图像，拷贝到目标图像的x,y坐标点
				ImageMagick::SaveImage($dst_mkwd, $dst_file,$info['format']);
				DestroyMagickWand($info['mw']);
			}else {
				// 一边长，一边短，先裁去长的边。
				$crop_x=$crop_y=0;
				$crop_width  = min($src_width , $dst_width);
				$crop_height = min($src_height, $dst_height);
				$crop_x = (int) ($src_width-$crop_width)/2 ;
				$crop_y = (int) ($src_height-$crop_height)/2 ;

				MagickCropImage($info['mw'], $crop_width, $crop_height, $crop_x, $crop_y);
				$dst_mkwd=NewMagickWand();
				MagickNewImage($dst_mkwd,$dst_width,$dst_height,'#FFFFFF');

				$dst_x = (int) ($dst_width-$crop_width)/2 ;
				$dst_y = (int) ($dst_height-$crop_height)/2 ;
				MagickCompositeImage($dst_mkwd ,$info['mw'], MW_OverCompositeOp, $dst_x, $dst_y ) ; // 合并图像，拷贝到目标图像的x,y坐标点
				ImageMagick::SaveImage($dst_mkwd, $dst_file,$info['format']);
				DestroyMagickWand($info['mw']);
			}
		}
		return true;
	}
	
	function ResizeGif($src_mw , $dst_file , $dst_width, $dst_height) 
	{
		MagickResetIterator($src_mw);
		do {
			$iw = MagickGetImageWidth($src_mw);
			$ih = MagickGetImageHeight($src_mw);
			$iratio=1.0;
			$iratio_w = doubleval($iw) / doubleval($dst_width);
			$iratio_h = doubleval($ih) / doubleval($dst_height);
			$iratio   = $iratio_w > $iratio_h ? $iratio_w : $iratio_h;
			$tmp_w = floor( $iw / $iratio);
			$tmp_h = floor( $ih / $iratio);
			ImageMagick::ResizeImage($src_mw, $tmp_w, $tmp_h);
		} while(MagickNextImage($src_mw));
		ImageMagick::SaveImage($src_mw, $dst_file, $info['format']);
	}

	function ResizeImage($mgk, $dst_width, $dst_height) 
	{
		MagickResizeImage( $mgk, $dst_width, $dst_height, MW_LanczosFilter, 1.0);
		//MagickSampleImage($mw, $dst_width, $dst_height); // Resize的效果好于Sample
		//MagickScaleImage($mw, $dst_width, $dst_height);
		/*
		*
		MW_PointFilter       MW_BoxFilter
		MW_TriangleFilter    MW_HermiteFilter
		MW_HanningFilter     MW_HammingFilter
		MW_BlackmanFilter    MW_GaussianFilter
		MW_QuadraticFilter   MW_CubicFilter
		MW_CatromFilter      MW_MitchellFilter
		MW_LanczosFilter     MW_BesselFilter
		MW_SincFilter
		*/
	}
	function SaveImage($mgk,$dst_file,$format) 
	{
		MagickSetFormat($mgk, $format);
		MagickWriteImage($mgk, $dst_file);
		DestroyMagickWand($mgk);
		unset($mgk);
	}
}// end class
?>