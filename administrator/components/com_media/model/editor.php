<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component Manager Editor Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaModelEditor extends ConfigModelForm
{
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input = JFactory::getApplication()->input;

			$folder = $input->get('folder', '', 'path');
			$this->state->set('folder', $folder);

			$fieldid = $input->get('fieldid', '');
			$this->state->set('field.id', $fieldid);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->state->set('parent', $parent);
			$set = true;
		}

		if(!$property)
		{

			return parent::getState();
		}
		else
		{

			return parent::getState()->get($property, $default);
		}

	}

	public function getForm($data = array(), $loadData = true)
	{
		return;
	}

	/**
	 * Rename a file.
	 *
	 * @param   string  $file  The name and location of the old file
	 * @param   string  $name  The new name of the file.
	 *
	 * @return  string  Encoded string containing the new file location.
	 *
	 * @since   3.2
	 */
	public function renameFile($file, $name)
	{
		$app         = JFactory::getApplication();

		$fileName    = $file;
		$path_parts  = pathinfo($file);
		$type        = $path_parts['extension'];
		$newFileName = $name . '.' . $type;

		$newName    = $path_parts['dirname'] . '/' . $newFileName;

		if (file_exists($newName))
		{
			$app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_EXISTS'), 'error');

			return false;
		}

		if (!rename($fileName, $newName))
		{
			$app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_RENAME_ERROR'), 'error');

			return false;
		}

		return $newFileName;

	}

	/**
	 * Get an image address, height and width.
	 *
	 * @return  array an associative array containing image address, height and width.
	 *
	 * @since   3.2
	 */
	public function getImage()
	{
		$app      = JFactory::getApplication();
		$fileName = $app->input->get('file');
		$folder   = $app->input->get('folder', '', 'path');
		$path     = JPath::clean(COM_MEDIA_BASE . '/');
		$uri      = COM_MEDIA_BASEURL . '/';

		if(!empty($folder))
		{
			$path     = JPath::clean(COM_MEDIA_BASE . '/' . $folder . '/');
			$uri      = COM_MEDIA_BASEURL . '/' . $folder .'/';
		}

		if (file_exists(JPath::clean($path . $fileName)))
		{
			$JImage = new JImage(JPath::clean($path . $fileName));
			$image['address'] 	= $uri . $fileName;
			$image['path']		= $fileName;
			$image['height'] 	= $JImage->getHeight();
			$image['width']  	= $JImage->getWidth();
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_MEDIA_ERROR_IMAGE_FILE_NOT_FOUND'), 'error');

			return false;
		}

		return $image;

	}

	/**
	 * Crop an image.
	 *
	 * @param   string  $file  The name and location of the file
	 * @param   string  $w     width.
	 * @param   string  $h     height.
	 * @param   string  $x     x-coordinate.
	 * @param   string  $y     y-coordinate.
	 *
	 * @return  boolean     true if image cropped successfully, false otherwise.
	 *
	 * @since   3.3
	 */
	public function cropImage($file, $w, $h, $x, $y)
	{
		$app      = JFactory::getApplication();

		$JImage   = new JImage($file);

		try
		{
			$image = $JImage->crop($w, $h, $x, $y, true);
			$image->toFile($file);

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

	}

	/**
	 * Resize an image.
	 *
	 * @param   string  $file    The name and location of the file
	 * @param   string  $width   The new width of the image.
	 * @param   string  $height  The new height of the image.
	 *
	 * @return   boolean  true if image resize successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function resizeImage($file, $width, $height)
	{
		$app     = JFactory::getApplication();

		$JImage = new JImage($file);

		try
		{
			$image = $JImage->resize($width, $height, true, 1);
			$image->toFile($file);

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

	}

	/**
	 * Rotate an image.
	 *
	 * @param   string  $file    The name and location of the file
	 * @param   string  $angle   The new angle of the image.
	 *
	 * @return   boolean  true if image rotate successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function rotateImage($file, $angle)
	{
		$app     = JFactory::getApplication();

		$JImage = new JImage($file);

		try
		{
			$image = $JImage->rotate($angle, -1, false);
			$image->toFile($file);

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

	}

	public function getFilterList()
	{
		return array("smooth" => "Smooth", "contrast" => "Contrast", "edgedetect" => "Edge Detect", "grayscale" => "Grayscale", "sketchy" => "Sketchy", "emboss" => "Emboss", "brightness" => "Brightness", "negate" => "Negate");
	}

	/**
	 * Filter an image.
	 *
	 * @param   string  $file    The name and location of the file
	 * @param   string  $filter  The new filter for the image.
	 * @param   string  $value   The filter value only use in brightness, contrast and smooth filters.
	 *
	 * @return   boolean  true if image filtering successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function filterImage($file, $filter, $value = null)
	{
		$app     = JFactory::getApplication();
		$options = array_fill(0, 11, 0);

		if(!empty($value))
		{
			$key = constant('IMG_FILTER_' . strtoupper($filter));
			$options[$key]= $value;
		}

		$JImage = new JImage($file);

		try
		{
			$image = $JImage->filter($filter, $options);
			$image->toFile($file);

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

	}
}
