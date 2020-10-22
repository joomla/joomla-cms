<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use enshrined\svgSanitize\Sanitizer;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use RuntimeException;

/**
 * Media helper class
 *
 * @since  3.2
 */
class MediaHelper
{
	/**
	 * Image extensions in regular expression format, e.g. "bmp|gif|jpg|png"
	 *
	 * @var  string
	 */
	private static $imageTypes;

	/**
	 * Checks if the file is an image
	 *
	 * @param   string  $fileName  The filename
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function isImage($fileName)
	{
		// Retrieve the image types from the com_media configuration
		if (empty(self::$imageTypes))
		{
			self::$imageTypes = implode('|', $this->getImageExtensions());
		}

		$imageTypes = self::$imageTypes;

		return preg_match("/\.(?:$imageTypes)$/i", $fileName);
	}

	/**
	 * Returns the configured image extensions in com_media Options
	 *
	 * @return  string[]
	 */
	public function getImageExtensions()
	{
		$params          = ComponentHelper::getParams('com_media');
		$configuredExts  = $params->get('image_extensions', 'bmp,gif,jpg,png');
		$configuredExts  = empty($configuredExts) ? 'bmp,gif,jpg,png' : $configuredExts;
		$imageExtensions = explode(',', $configuredExts);
		$imageExtensions = array_map('trim', $imageExtensions);
		$imageExtensions = array_map('strtolower', $imageExtensions);
		$filterClosure   = function ($extension) {
			return !empty($extension);
		};
		$imageExtensions = array_filter($imageExtensions, $filterClosure);
		$imageExtensions = array_unique($imageExtensions);

		return $imageExtensions;
	}

	/**
	 * Gets the file extension for purposed of using an icon
	 *
	 * @param   string  $fileName  The filename
	 *
	 * @return  string  File extension to determine icon
	 *
	 * @since   3.2
	 */
	public static function getTypeIcon($fileName)
	{
		return strtolower(substr($fileName, strrpos($fileName, '.') + 1));
	}

	/**
	 * Get the Mime type
	 *
	 * @param   string   $file     The link to the file to be checked
	 * @param   boolean  $isImage  True if the passed file is an image else false
	 *
	 * @return  mixed    the mime type detected false on error
	 *
	 * @since   3.7.2
	 */
	private function getMimeType($file, $isImage = false)
	{
		// If we can't detect anything mime is false
		$mime = false;

		try
		{
			if ($isImage && function_exists('exif_imagetype'))
			{
				$mime = image_type_to_mime_type(exif_imagetype($file));
			}
			elseif ($isImage && function_exists('getimagesize'))
			{
				$imagesize = getimagesize($file);
				$mime      = isset($imagesize['mime']) ? $imagesize['mime'] : false;
			}
			elseif (function_exists('mime_content_type'))
			{
				// We have mime magic.
				$mime = mime_content_type($file);
			}
			elseif (function_exists('finfo_open'))
			{
				// We have fileinfo
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime  = finfo_file($finfo, $file);
				finfo_close($finfo);
			}
		}
		catch (Exception $e)
		{
			// If we have any kind of error here => false;
			return false;
		}

		// If we can't detect the mime try it again
		if ($mime === 'application/octet-stream' && $isImage === true)
		{
			$mime = $this->getMimeType($file, false);
		}

		// We have a mime here
		return $mime;
	}

	/**
	 * Checks the Mime type
	 *
	 * @param   string  $mime       The mime to be checked
	 * @param   string  $component  The optional name for the component storing the parameters
	 *
	 * @return  boolean  true if mime type checking is disabled or it passes the checks else false
	 *
	 * @since   3.7
	 */
	private function checkMimeType($mime, $component = 'com_media')
	{
		$params = ComponentHelper::getParams($component);

		if ($params->get('check_mime', 1))
		{
			// Get the mime type configuration
			$allowedMime = array_map('trim', explode(',', $params->get('upload_mime')));

			// Mime should be available and in the whitelist
			return !empty($mime) && in_array($mime, $allowedMime);
		}

		// We don't check mime at all or it passes the checks
		return true;
	}

	/**
	 * Checks if the file can be uploaded
	 *
	 * @param   array   $file       File information
	 * @param   string  $component  The option name for the component storing the parameters
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 * @since   3.2
	 */
	public function canUpload($file, $component = 'com_media')
	{
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams($component);

		if (empty($file['name']))
		{
			$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'), 'error');

			return false;
		}

		jimport('joomla.filesystem.file');

		if (str_replace(' ', '', $file['name']) !== $file['name'] || $file['name'] !== File::makeSafe($file['name']))
		{
			$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILENAME'), 'error');

			return false;
		}

		$filetypes = explode('.', $file['name']);

		if (count($filetypes) < 2)
		{
			// There seems to be no extension
			$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

		array_shift($filetypes);

		// Media file names should never have executable extensions buried in them.
		$executable = array(
			'php', 'js', 'exe', 'phtml', 'java', 'perl', 'py', 'asp', 'dll', 'go', 'ade', 'adp', 'bat', 'chm', 'cmd',
			'com', 'cpl', 'hta', 'ins', 'isp', 'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb',
			'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh',
		);

		$check = array_intersect($filetypes, $executable);

		if (!empty($check))
		{
			$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

		$filetype  = array_pop($filetypes);
		$allowable = array_map('trim', explode(',', $params->get('upload_extensions')));
		$ignored   = array_map('trim', explode(',', $params->get('ignore_extensions')));

		if ($filetype == '' || $filetype == false || (!in_array($filetype, $allowable) && !in_array($filetype, $ignored)))
		{
			$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

		$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETOOLARGE'), 'error');

			return false;
		}

		if ($params->get('restrict_uploads', 1))
		{
			$images = array_map('trim', explode(',', $params->get('image_extensions')));

			if (in_array($filetype, $images))
			{
				// If tmp_name is empty, then the file was bigger than the PHP limit
				if (!empty($file['tmp_name']))
				{
					// Get the mime type this is an image file
					$mime = $this->getMimeType($file['tmp_name'], true);

					// Did we get anything useful?
					if ($mime != false)
					{
						$result = $this->checkMimeType($mime, $component);

						// If the mime type is not allowed we don't upload it and show the mime code error to the user
						if ($result === false)
						{
							$app->enqueueMessage(Text::sprintf('JLIB_MEDIA_ERROR_WARNINVALID_MIMETYPE', $mime), 'error');

							return false;
						}
					}
					// We can't detect the mime type so it looks like an invalid image
					else
					{
						$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNINVALID_IMG'), 'error');

						return false;
					}
				}
				else
				{
					$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETOOLARGE'), 'error');

					return false;
				}

				// If this is an SVG file (by name or MIME type) we need to sanitize it
				if ((strtolower(substr($file['name'], -4)) === '.svg')
					|| in_array($mime, array('image/svg+xml', 'application/svg+xml')))
				{
					try
					{
						$this->sanitizeSVG($file['tmp_name']);
					}
					catch (RuntimeException $e)
					{
						$app->enqueueMessage($e->getMessage(), 'error');

						return false;
					}
				}
			}
			elseif (!in_array($filetype, $ignored))
			{
				// Get the mime type this is not an image file
				$mime = $this->getMimeType($file['tmp_name'], false);

				// Did we get anything useful?
				if ($mime != false)
				{
					$result = $this->checkMimeType($mime, $component);

					// If the mime type is not allowed we don't upload it and show the mime code error to the user
					if ($result === false)
					{
						$app->enqueueMessage(Text::sprintf('JLIB_MEDIA_ERROR_WARNINVALID_MIMETYPE', $mime), 'error');

						return false;
					}
				}
				// We can't detect the mime type so it looks like an invalid file
				else
				{
					$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNINVALID_MIME'), 'error');

					return false;
				}

				if (!Factory::getUser()->authorise('core.manage', $component))
				{
					$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNNOTADMIN'), 'error');

					return false;
				}
			}
		}

		$xssCheck = file_get_contents($file['tmp_name'], false, null, -1, 256);

		$htmlTags = array(
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big',
			'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col',
			'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset',
			'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe',
			'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
			'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript',
			'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp',
			'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style',
			'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',  'tr', 'tt', 'ul', 'var',
			'wbr', 'xml', 'xmp', '!DOCTYPE', '!--',
		);

		foreach ($htmlTags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stripos($xssCheck, '<' . $tag . ' ') !== false || stripos($xssCheck, '<' . $tag . '>') !== false)
			{
				$app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNIEXSS'), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Calculate the size of a resized image
	 *
	 * @param   integer  $width   Image width
	 * @param   integer  $height  Image height
	 * @param   integer  $target  Target size
	 *
	 * @return  array  The new width and height
	 *
	 * @since   3.2
	 */
	public static function imageResize($width, $height, $target)
	{
		/*
		 * Takes the larger size of the width and height and applies the
		 * formula accordingly. This is so this script will work
		 * dynamically with any size image
		 */
		if ($width > $height)
		{
			$percentage = ($target / $width);
		}
		else
		{
			$percentage = ($target / $height);
		}

		// Gets the new value and applies the percentage, then rounds the value
		$width  = round($width * $percentage);
		$height = round($height * $percentage);

		return array($width, $height);
	}

	/**
	 * Counts the files and directories in a directory that are not php or html files.
	 *
	 * @param   string  $dir  Directory name
	 *
	 * @return  array  The number of media files and directories in the given directory
	 *
	 * @since   3.2
	 */
	public function countFiles($dir)
	{
		$totalFile = 0;
		$totalDir  = 0;

		if (is_dir($dir))
		{
			$d = dir($dir);

			while (($entry = $d->read()) !== false)
			{
				if (($entry[0] !== '.')
					&& (strpos($entry, '.html') === false)
					&& (strpos($entry, '.php') === false)
					&& is_file($dir . DIRECTORY_SEPARATOR . $entry))
				{
					$totalFile++;
				}

				if ($entry[0] !== '.' && is_dir($dir . DIRECTORY_SEPARATOR . $entry))
				{
					$totalDir++;
				}
			}

			$d->close();
		}

		return array($totalFile, $totalDir);
	}

	/**
	 * Small helper function that properly converts any
	 * configuration options to their byte representation.
	 *
	 * @param   string|integer  $val  The value to be converted to bytes.
	 *
	 * @return integer The calculated bytes value from the input.
	 *
	 * @since 3.3
	 */
	public function toBytes($val)
	{
		switch ($val[strlen($val) - 1])
		{
			case 'M':
			case 'm':
				return (int) $val * 1048576;
			case 'K':
			case 'k':
				return (int) $val * 1024;
			case 'G':
			case 'g':
				return (int) $val * 1073741824;
			default:
				return $val;
		}
	}

	/**
	 * Tries to sanitize an uploaded file if it's an SVG file (by extension or MIME type)
	 *
	 * @param   string  $tempName  The temporary uploaded file to sanitize
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  In case of an error
	 * @since   3.10.0
	 *
	 */
	public function sanitizeSVG($tempName)
	{
		$sanitizer = new Sanitizer;
		$sanitizer->removeRemoteReferences(true);
		$sanitizer->minify(true);

		// Load the dirty svg
		$dirtySVG = @file_get_contents($tempName);

		if ($dirtySVG === false)
		{
			throw new RuntimeException(Text::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'));
		}

		// Pass it to the sanitizer and get it back clean
		$cleanSVG = $sanitizer->sanitize($dirtySVG);

		if ($cleanSVG === false)
		{
			throw new RuntimeException(Text::_('JLIB_MEDIA_ERROR_INVALID_SVG'));
		}

		// Save the sanitized file
		if (@file_put_contents($tempName, $cleanSVG) === false)
		{
			throw new RuntimeException(Text::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'));
		}
	}
}
