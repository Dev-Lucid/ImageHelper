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
}

?>