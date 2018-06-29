<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\AdaptiveImage\JSONFocusStore;
use Joomla\CMS\AdaptiveImage\SmartCrop;
use Joomla\CMS\Uri\Uri;

/**
 * Adaptive Image Controller Class
 *
 * Used to set the focus point and save it into filesystem
 *
 * Used to get the focus point while rendering the page on the frontend
 *
 * @since  4.0.0
 */
class AdaptiveImageController extends BaseController
{
	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @param   string  $task  The task to perform.
	 *
	 * @return  mixed
	 *
	 * @since   4.0.0
	 */
	public function execute($task)
	{
		switch ($task)
		{
			case "setfocus" :
				$imgPath = $this->input->getString('path');
				$dataFocus = array (
					"box-left"			=> $this->input->getInt('box-left'),
					"box-top"			=> $this->input->getInt('box-top'),
					"box-width"			=> $this->input->getInt('box-width'),
					"box-height"		=> $this->input->getInt('box-height')
				);
				$storage = new JSONFocusStore;
				$storage->setFocus($dataFocus, $imgPath);
				$this->cropImage($imgPath);
				return true;
				break;
			case "cropBoxData" :
				$this->app->setHeader('Content-Type', 'application/json');
				$imgPath = $this->input->getString('path');
				$storage = new JSONFocusStore;
				echo $storage->getFocus($imgPath);
				$this->app->close();
				return true;
				break;
			case "cropImage" :
				// @TODO Resize image to any aspect ratio.
				$imgPath = "/images/" . $this->input->getString('path');
				
				// $finalWidth = $this->input->getFloat('width');
				$this->cropImage($imgPath);
				return true;
				break;
			default :
				return false;
		}
	}
	/**
	 * Crop the images around the focus area
	 * 
	 * @param   string  $imgPath  image path
	 * 
	 * @return  boolean
	 * 
	 * @since 4.0.0 
	 */
	public function cropImage($imgPath)
	{
		$storage = new JSONFocusStore;
		$dataFocus = json_decode($storage->getFocus($imgPath), true);
		$width = array(240, 360, 480, 768, 940, 1024);
		foreach ($width as $finalWidth)
		{
			$image = new SmartCrop(".." . $imgPath);
			$image->compute($dataFocus, $finalWidth);
		}
		return true;
	}
}
