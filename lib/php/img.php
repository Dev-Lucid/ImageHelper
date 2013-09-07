<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

global $__img;
$__img=array(
	'hooks'=>array(),
);

class img
{
	function call_hook($hook,$p0=null,$p1=null,$p2=null,$p3=null,$p4=null,$p5=null,$p6=null)
	{
		global $__img;
		if(isset($__img['hooks'][$hook]))
			$__img['hooks'][$hook]($p0,$p1,$p2,$p3,$p4,$p5,$p6);
	}
	
	function log($to_write)
	{
		global $__img;		
		if(isset($__img['hooks']['log']))
		{
			$to_write=(is_object($to_write) || is_array($to_write))?print_r($to_write,true):$to_write;
			$__img['hooks']['log']('IMG: '.$to_write);
		}
	}
	
	function init($config = array())
	{
		global $__img;
		foreach($config as $key=>$value)
		{
			if(is_array($value))
			{
				foreach($value as $subkey=>$subvalue)
				{
					if(is_numeric($subkey))
						$__img[$key][] = $subvalue;
					else
						$__img[$key][$subkey] = $subvalue;
				}

			}
			else
				$__img[$key] = $value;
		}	
	}
	
	function __construct($_files_name)
	{
		$this->files_name = $_files_name;
		$this->data = $_FILES[$this->files_name];
		
		img::log('image data: '.print_r($this->data,true));
		
		list(
			$this->width,
			$this->height,
			$this->type,
			$this->html_attributes,
			$this->bits,
			$this->mime
		) = getimagesize($this->data['tmp_name']);
		$this->size = $this->data['size'];
		$this->mime = image_type_to_mime_type($this->type);
		$this->extension = str_replace('.','',image_type_to_extension($this->type));
		if($this->extension == 'jpeg')
			$this->extension = 'jpg';
			
	}
	
	function scale($old_x,$old_y,$max_x,$max_y)
	{
		$final_x = 0;
		$final_y = 0;
				
		return array($final_x,$final_y);
	}
	
	function move($new_path)
	{
		move_uploaded_file($this->data['tmp_name'],$new_path);
		return $this;
	}
		
	public static function deinit()
	{
	}
	
	function make_cached_thumb($orig_path,$cache_path,$width,$height,$maxwidth,$maxheight,$ext,$request_path)
	{
		if ($height > $width) 
		{   
			$ratio = $maxheight / $height;  
			$newheight = $maxheight;
			$newwidth = $width * $ratio; 
			$writex = round(($maxwidth - $newwidth) / 2);
			$writey = 0;
		}
		else 
		{
			$ratio = $maxwidth / $width;   
			$newwidth = $maxwidth;  
			$newheight = $height * $ratio;   
			$writex = 0;
			$writey = round(($maxheight - $newheight) / 2);
		}

		# load the image, using the right format
		$newimg = imagecreatetruecolor($newwidth,$newheight);
		$types = array(
			'gif'=>IMG_GIF,
			'jpg'=>IMG_JPG,
			'png'=>IMG_PNG,
		);
		$mime   = image_type_to_mime_type($types[$ext]);
		switch($ext)
		{
			case 'jpg':
				$img = imagecreatefromjpeg($orig_path);
				imagecopyresampled($newimg,$img,0,0,0,0,$newwidth,$newheight, $width, $height);
				imagejpeg($newimg,$cache_path,95);
				break;
			case 'gif':
				$img = imagecreatefromgif($orig_path);
				imagecopyresampled($newimg,$img,0,0,0,0,$newwidth,$newheight, $width, $height);
				imagegif($newimg,$cache_path);
				break;
			case 'png':
				$img = imagecreatefrompng($orig_path);
				imagecopyresampled($newimg,$img,0,0,0,0,$newwidth,$newheight, $width, $height);
				imagepng($newimg,$cache_path);
				break;
			default:
				exit('unknown format');	
				break;
		}

		//$output file is the path/filename where you wish to save the file.  
		header('Pragma: no-cache');
		header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header("Content-Type: $mime"); 
		header("Content-Disposition: inline; filename=\"".$_REQUEST['thumb']."\";" ); 
		header("Content-Transfer-Encoding: binary"); 
		echo(file_get_contents($cache_path));
	}
}

?>