<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\AdaptiveImage;
defined('_JEXEC') or die;

use Joomla\Image\Image;
/**
 * Used for cropping of the images around
 * the focus points.
 *
 * @since  4.0.0
 */
class SmartCrop
{
	// Location for storing cache images
	public $dataLocation = JPATH_SITE . "/images/.cache";

	// Absolute image path
	public $imgPath;

	// Image object
	public $image;
	/**
	 * Initilize parent image class
	 * 
	 * @param   string  $imgPath  Image path
	 *
	 * @since 4.0.0
	 */
	public function __construct($imgPath)
	{
		ini_set('memory_limit', '-1');
		$this->image = new Image($imgPath);
		$this->imgPath = $imgPath;
		$this->checkDir();
	}
	/**
	 * Crop the image around focus point and save it
	 * 
	 * @param   array    $dataFocus   Array of data focus points
	 * @param   integer  $finalWidth  Disired width
	 *
	 * @return boolean
	 *
	 * @since 4.0.0
	 */
	public function compute($dataFocus, $finalWidth)
	{
		$fx = $dataFocus["box-left"];
		$fy = $dataFocus["box-top"];
		$fwidth = $dataFocus["box-width"];
		$fheight = $dataFocus["box-height"];

		// Max-Width and Max-Height of the images
		$mwidth = $this->image->getWidth();
		$mheight = $this->image->getHeight();

		// Final width and height of the required image
		$twidth = $finalWidth;
		$theight = $twidth*$mheight/$mwidth;

		if ($twidth<$fwidth || $theight<$fheight)
		{
			// Scale down the selection.
			$finalImage = $this->image->crop($fwidth, $fheight, $fx, $fy);
			$finalImage = $finalImage->resize($twidth, $theight);

		}
		elseif ($twidth>=$mwidth || $theight>=$mheight)
		{
			// Show original Image do nothing
			return true;
		}
		else
		{
			$diff_x = ($twidth - $fwidth) / 2;
			$fx = $fx - $diff_x;
			$x2 = $fx + $twidth;

			if ($x2>$mwidth)
			{
				$fx = $fx - ($x2-$mwidth);
			}
			elseif ($fx<0)
			{
				$fx=0;
			}

			$diff_y = ($theight - $fheight)/2;
			$fy = $fy - $diff_y;
			$y2 = $fy + $theight;

			if ($y2>$mheight)
			{
				$fy = $fy - ($y2-$mheight);
			}
			elseif ($fy<0)
			{
				$fy=0;
			}

			$finalImage = $this->image->crop($twidth, $theight, $fx, $fy);
			
		}
		$imgPath = explode('.', $this->imgPath);
		$imgName = "/" . $twidth . "_" . base64_encode($imgPath[2]) . "." . $imgPath[3];
		$path = $this->dataLocation . $imgName;

		$finalImage->toFile($path);

		$finalImage->destroy();
		
		return true;
	}
	/**
	 * Check if the cache directory is present or not
	 * 
	 * @return  boolean
	 * 
	 * @since 4.0.0
	 */
	public function checkDir()
	{
		if (!is_dir($this->dataLocation))
		{
			mkdir($this->dataLocation, 0777);
		}
		return true;
	}
}
