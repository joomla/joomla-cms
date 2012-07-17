<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_media
 * @since       1.5
 */
class MediaHelper
{
	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return boolean
	 *
	 * @since  1.5
	 */
	public function isImage($fileName)
	{
		static $imageTypes = 'xcf|odg|gif|jpg|png|bmp';

		return preg_match("/$imageTypes/i", $fileName);
	}

	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return boolean
	 *
	 * @since  1.5
	 */
	public function getTypeIcon($fileName)
	{
		// Get file extension
		return strtolower(substr($fileName, strrpos($fileName, '.') + 1));
	}

	/**
	 * Checks if the file can be uploaded
	 * @param array File information
	 * @param string An error message to be returned
	 * @return boolean
	 *
	 * @since  1.5
	 */
	public function canUpload($file, &$err)
	{
		$params = JComponentHelper::getParams('com_media');

		jimport('joomla.filesystem.file');
		$format = JFile::getExt($file['name']);

		$allowable = explode(',', $params->get('upload_extensions'));

		if (!in_array($format, $allowable)) {
			$err = JText('COM_MEDIA_ERROR_WARNFILETYPE');
			return false;
		}

		$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize) {
			$err = JText('COM_MEDIA_ERROR_WARNFILETOOLARGE');

			return false;
		}

		return true;
	}

	/**
	 * Method to parse a file size
	 *
	 * @param   integer  $size  The file size in bytes
	 *
	 * @return  string  The converted file size
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use JHtmlNumber::bytes() instead
	 */
	public static function parseSize($size)
	{
		JLog::add('MediaHelper::parseSize() is deprecated. Use JHtmlNumber::bytes() instead.', JLog::WARNING, 'deprecated');
		return JHtml::_('number.bytes', $size);
	}

	/**
	 * @since  1.5
	 */
	public function imageResize($width, $height, $target)
	{
		//takes the larger size of the width and height and applies the
		//formula accordingly...this is so this script will work
		//dynamically with any size image
		if ($width > $height) {
			$percentage = ($target / $width);
		}
		else {
			$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		//returns the new sizes in html image tag format...this is so you
		//can plug this function inside an image tag and just get the
		return "width=\"$width\" height=\"$height\"";
	}

	/**
	 * @since  1.5
	 */
	public function countFiles($dir)
	{
		$total_file = 0;
		$total_dir = 0;

		if (is_dir($dir)) {
			$d = dir($dir);

			while (false !== ($entry = $d->read()))
			{
				if (substr($entry, 0, 1) != '.' && is_file($dir . DIRECTORY_SEPARATOR . $entry) && strpos($entry, '.html') === false && strpos($entry, '.php') === false) {
					$total_file++;
				}

				if (substr($entry, 0, 1) != '.' && is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
					$total_dir++;
				}
			}

			$d->close();
		}

		return array ($total_file, $total_dir);
	}
}
