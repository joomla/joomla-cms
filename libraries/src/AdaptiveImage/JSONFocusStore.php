<?php
/**
 * @package     Joomla
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\AdaptiveImage;

defined('_JEXEC') or die;

use Joomla\CMS\AdaptiveImage\FocusStoreInterface;

/**
 * Focus Store Class
 *
 * Used to set the focus point and save it into filesystem
 *
 * Used to get the focus point while rendering the page on the frontend
 *
 * @since  4.0.0
 */
class JSONFocusStore implements FocusStoreInterface
{
	/**
	 * Location for file storing the data focus point.
	 *
	 * @var string
	 *
	 * @since 4.0.0
	 */
	protected static $dataLocation = JPATH_PLUGINS . '/media-action/smartcrop/focus.json';
	/**
	 * Checks the storage at the initilization of the class
	 * 
	 * @since 4.0.0
	 */
	public function __construct()
	{
		$this->checkStorage(static::$dataLocation);
	}
	/**
	 * Function to set the focus point
	 *
	 * index.php?option=com_media&task=adaptiveimage.setfocus&path=/images/sampledata/fruitshop/bananas_1.jpg
	 *
	 * @param   array   $dataFocus  Array of the values of diffrent focus point
	 *
	 * @param   string  $imgPath    Full path for the file
	 *
	 * @return  boolean
	 *
	 * @since 4.0.0
	 */
	public function setFocus($dataFocus,$imgPath)
	{
		$newEntry = array(
			$imgPath => array(
				"box-left"			=> $dataFocus['box-left'],
				"box-top"			=> $dataFocus['box-top'],
				"box-width"			=> $dataFocus['box-width'],
				"box-height"		=> $dataFocus['box-height']
			)
		);

		if (filesize(static::$dataLocation))
		{
			$prevData = file_get_contents(static::$dataLocation);

			$prevData = json_decode($prevData, true);

			$prevData[$imgPath]["box-left"] 			= $dataFocus['box-left'];
			$prevData[$imgPath]["box-top"] 				= $dataFocus['box-top'];
			$prevData[$imgPath]["box-width"] 			= $dataFocus['box-width'];
			$prevData[$imgPath]["box-height"] 			= $dataFocus['box-height'];

			file_put_contents(static::$dataLocation, json_encode($prevData));
		}
		else
		{
			file_put_contents(static::$dataLocation, json_encode($newEntry));
		}

		return true;

	}

	/**
	 * Function to get the focus point
	 *
	 * @param   string  $imgPath  Image Path
	 *
	 * @return  array
	 *
	 * @since 4.0.0
	 */
	public function getFocus($imgPath)
	{
		if (!filesize(static::$dataLocation))
		{
			return false;
		}

		$prevData = file_get_contents(static::$dataLocation);

		$prevData = json_decode($prevData, true);

		if (array_key_exists($imgPath, $prevData))
		{
			return json_encode($prevData[$imgPath]);
		}
		else
		{
			return false;
		}
	}
	/**
	 * Check whether the file exist
	 *
	 * @param   string  $dataLocation  location of storage file
	 * 
	 * @return  boolean
	 *
	 * @since 4.0.0
	 */
	private function checkStorage($dataLocation)
	{
		if (!file_exists($dataLocation))
		{
			touch($dataLocation);
		}

		return true;
	}
}
