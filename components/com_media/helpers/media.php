<?php
/**
 * @version		$Id: media.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/**
 * @package		Joomla.Site
 * @subpackage	Media
 */
class MediaHelper
{
	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return boolean
	 */
	function isImage($fileName)
	{
		static $imageTypes = 'xcf|odg|gif|jpg|png|bmp';
		return preg_match("/$imageTypes/i",$fileName);
	}

	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return boolean
	 */
	function getTypeIcon($fileName)
	{
		// Get file extension
		return strtolower(substr($fileName, strrpos($fileName, '.') + 1));
	}

	/**
	 * Checks if the file can be uploaded
	 * @param array File information
	 * @param string An error message to be returned
	 * @return boolean
	 */
	function canUpload($file, &$err)
	{
		$params = &JComponentHelper::getParams('com_media');

		jimport('joomla.filesystem.file');
		$format = JFile::getExt($file['name']);

		$allowable = explode(',', $params->get('upload_extensions'));

		if (!in_array($format, $allowable))
		{
			$err = 'This file type is not supported';
			return false;
		}
		$maxSize = (int) $params->get('upload_maxsize', 0);
		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$err = 'This file is too large to upload';
			return false;
		}
		return true;
	}

	function parseSize($size)
	{
		if ($size < 1024) {
			return $size . ' bytes';
		}
		else
		{
			if ($size >= 1024 && $size < 1024 * 1024) {
				return sprintf('%01.2f', $size / 1024.0) . ' Kb';
			} else {
				return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' Mb';
			}
		}
	}

	function imageResize($width, $height, $target)
	{
		//takes the larger size of the width and height and applies the
		//formula accordingly...this is so this script will work
		//dynamically with any size image
		if ($width > $height) {
			$percentage = ($target / $width);
		} else {
			$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		//returns the new sizes in html image tag format...this is so you
		//can plug this function inside an image tag and just get the
		return "width=\"$width\" height=\"$height\"";
	}

	function countFiles($dir)
	{
		$total_file = 0;
		$total_dir = 0;

		if (is_dir($dir)) {
			$d = dir($dir);

			while (false !== ($entry = $d->read())) {
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
?>