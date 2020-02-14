<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderHelperImage {

	public $height;
	public $width;
	private $src;

	public function __construct($src = '') {
		$this->src = $src;
		list($this->width, $this->height) = getimagesize($src);
	}

	public function createThumb($size = array(), $folder, $base_name, $ext) {

		switch($ext) {
			case 'bmp': $img = imagecreatefromwbmp($this->src); break;
			case 'gif': $img = imagecreatefromgif($this->src); break;
			case 'jpg': $img = imagecreatefromjpeg($this->src); break;
			case 'jpeg': $img = imagecreatefromjpeg($this->src); break;
			case 'png': $img = imagecreatefrompng($this->src); break;
		}

		if(count((array) $size)) {
			$targetWidth = $size[0];
			$targetHeight = $size[1];
			$ratio_thumb = $targetWidth/$targetHeight;
			$ratio_original = $this->width/$this->height;

			if ($ratio_original >= $ratio_thumb) {
				$height = $this->height;
				$width = ceil(($height*$targetWidth)/$targetHeight);
				$x = ceil(($this->width-$width)/2);
				$y = 0;
			} else {
				$width = $this->width;
				$height = ceil(($width*$targetHeight)/$targetWidth);
				$y = ceil(($this->height-$height)/2);
				$x = 0;
			}

			$new = imagecreatetruecolor($targetWidth, $targetHeight);

			if($ext == "gif" or $ext == "png") {
				imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
				imagealphablending($new, false);
				imagesavealpha($new, true);
			}

			imagecopyresampled($new, $img, 0, 0, $x, $y, $targetWidth, $targetHeight, $width, $height);

			$dest = dirname($this->src) . '/' . $folder . '/' . $base_name . '.' . $ext;

			switch($ext) {
				case 'bmp': imagewbmp($new, $dest); break;
				case 'gif': imagegif($new, $dest); break;
				case 'jpg': imagejpeg($new, $dest, 100); break;
				case 'jpeg': imagejpeg($new, $dest, 100); break;
				case 'png': imagepng($new, $dest); break;
			}
		}

		return false;
	}
}
