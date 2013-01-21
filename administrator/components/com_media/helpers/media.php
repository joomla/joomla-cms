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
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.5
 */
abstract class MediaHelper
{
	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return  boolean
	 */
	public static function isImage($fileName)
	{
		static $imageTypes = 'xcf|odg|gif|jpg|png|bmp';
		return preg_match("/\.(?:$imageTypes)$/i", $fileName);
	}

	/**
	 * Checks if the file is an image
	 * @param string The filename
	 * @return  boolean
	 */
	public static function getTypeIcon($fileName)
	{
		// Get file extension
		return strtolower(substr($fileName, strrpos($fileName, '.') + 1));
	}

	/**
	 * Checks if the file can be uploaded
	 *
	 * @param array File information
	 * @param string An error message to be returned
	 * @return  boolean
	 */
	public static function canUpload($file, &$err)
	{
		$params = JComponentHelper::getParams('com_media');

		if (empty($file['name']))
		{
			$err = 'COM_MEDIA_ERROR_UPLOAD_INPUT';
			return false;
		}

		jimport('joomla.filesystem.file');
		if ($file['name'] !== JFile::makesafe($file['name']))
		{
			$err = 'COM_MEDIA_ERROR_WARNFILENAME';
			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));

		$allowable = explode(',', $params->get('upload_extensions'));
		$ignored = explode(',', $params->get('ignore_extensions'));
		if (!in_array($format, $allowable) && !in_array($format, $ignored))
		{
			$err = 'COM_MEDIA_ERROR_WARNFILETYPE';
			return false;
		}

		$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);
		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$err = 'COM_MEDIA_ERROR_WARNFILETOOLARGE';
			return false;
		}

		$user = JFactory::getUser();
		$imginfo = null;
		if ($params->get('restrict_uploads', 1))
		{
			$images = explode(',', $params->get('image_extensions'));
			if (in_array($format, $images)) { // if its an image run it through getimagesize
				// if tmp_name is empty, then the file was bigger than the PHP limit
				if (!empty($file['tmp_name']))
				{
					if (($imginfo = getimagesize($file['tmp_name'])) === false)
					{
						$err = 'COM_MEDIA_ERROR_WARNINVALID_IMG';
						return false;
					}
				} else {
					$err = 'COM_MEDIA_ERROR_WARNFILETOOLARGE';
					return false;
				}
			} elseif (!in_array($format, $ignored))
			{
				// if its not an image...and we're not ignoring it
				$allowed_mime = explode(',', $params->get('upload_mime'));
				$illegal_mime = explode(',', $params->get('upload_mime_illegal'));
				if (function_exists('finfo_open') && $params->get('check_mime', 1))
				{
					// We have fileinfo
					$finfo = finfo_open(FILEINFO_MIME);
					$type = finfo_file($finfo, $file['tmp_name']);
					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
					{
						$err = 'COM_MEDIA_ERROR_WARNINVALID_MIME';
						return false;
					}
					finfo_close($finfo);
				} elseif (function_exists('mime_content_type') && $params->get('check_mime', 1))
				{
					// we have mime magic
					$type = mime_content_type($file['tmp_name']);
					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
					{
						$err = 'COM_MEDIA_ERROR_WARNINVALID_MIME';
						return false;
					}
				} elseif (!$user->authorise('core.manage'))
				{
					$err = 'COM_MEDIA_ERROR_WARNNOTADMIN';
					return false;
				}
			}
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');

		foreach ($html_tags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>'))
			{
				$err = 'COM_MEDIA_ERROR_WARNIEXSS';
				return false;
			}
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
	 * @since   1.6
	 * @deprecated  4.0  Use JHtmlNumber::bytes() instead
	 */
	public static function parseSize($size)
	{
		JLog::add('MediaHelper::parseSize() is deprecated. Use JHtmlNumber::bytes() instead.', JLog::WARNING, 'deprecated');
		return JHtml::_('number.bytes', $size);
	}

	public static function imageResize($width, $height, $target)
	{
		//takes the larger size of the width and height and applies the
		//formula accordingly...this is so this script will work
		//dynamically with any size image
		if ($width > $height)
		{
			$percentage = ($target / $width);
		}
		else
		{
			$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		return array($width, $height);
	}

	public static function countFiles($dir)
	{
		$total_file = 0;
		$total_dir = 0;

		if (is_dir($dir))
		{
			$d = dir($dir);

			while (false !== ($entry = $d->read()))
			{
				if (substr($entry, 0, 1) != '.' && is_file($dir . DIRECTORY_SEPARATOR . $entry) && strpos($entry, '.html') === false && strpos($entry, '.php') === false)
				{
					$total_file++;
				}
				if (substr($entry, 0, 1) != '.' && is_dir($dir . DIRECTORY_SEPARATOR . $entry))
				{
					$total_dir++;
				}
			}

			$d->close();
		}

		return array ($total_file, $total_dir);
	}

}
