<?php
/**
 * @version $Id: admin.media.php 3621 2006-05-24 08:21:25Z webImagery $
 * @package Joomla
 * @subpackage Media
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package    Joomla
 * @subpackage Media
 */
class JMediaHelper
{
	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return boolean
	 */
	function isImage( $fileName )
	{
		static $imageTypes = 'xcf|odg|gif|jpg|png|bmp';
		return preg_match("/$imageTypes/i",$fileName);
	}

	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return boolean
	 */
	function getTypeIcon( $fileName )
	{
		// Get file extension
		$ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));

		switch ($ext) {
			case 'xcf':
			case 'odg':
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'odi':
			case 'pbm':
			case 'bmp':
				$icon = 'image';
				break;

			case 'tar':
			case 'gz':
			case 'zip':
			case 'rar':
			case 'b2z':
			case 'jar':
				$icon = 'archive';
				break;

			case 'mp3':
			case 'wav':
			case 'ogg':
			case 'au3':
			case 'wma':
			case 'aac':
			case 'ram':
			case 'aif':
			case 'aiff':
				$icon = 'audio';
				break;

			case 'odb':
				$icon = 'db';
				break;

			case 'pdf':
				$icon = 'pdf';
				break;

			case 'ppt':
			case 'odp':
				$icon = 'presentation';
				break;

			case 'ods':
			case 'xls':
				$icon = 'spreadsheet';
				break;

			case 'txt':
			case 'odm':
				$icon = 'text';
				break;

			case 'svg':
				$icon = 'svg';
				break;

			case 'swf':
				$icon = 'swf';
				break;

			case 'rm':
			case 'wmv':
			case 'mov':
			case 'avi':
			case 'asf':
			case 'ogm':
			case 'mpeg':
			case 'divx':
				$icon = 'video';
				break;

			case 'doc':
			case 'odt':
				$icon = 'word-processor';
				break;

			default:
				$icon = 'unknown';
				break;
		}		
		return $icon;
	}

	/**
	 * Checks if the file can be uploaded
	 * @param array File information
	 * @param string An error message to be returned
	 * @return boolean
	 */
	function canUpload( $file, &$err )
	{
		$params = &JComponentHelper::getParams( 'com_media' );

		jimport('joomla.filesystem.file');
		$format = JFile::getExt($file['name']);

		$allowable = implode( ',', (array) $params->get( 'upload_extensions' ));

		if (!in_array($format, $allowable))
		{
			$err = 'This file type is not supported';				
			return false;
		}
		$maxSize = (int) $params->get( 'upload_maxsize', 0 );
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
	
	function countFiles( $dir ) 
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

		return array ( $total_file, $total_dir );
	}

}
?>