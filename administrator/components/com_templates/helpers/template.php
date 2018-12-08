<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Template Helper class.
 *
 * @since  3.2
 */
abstract class TemplateHelper
{
	/**
	 * Checks if the file is an image
	 *
	 * @param   string  $fileName  The filename
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function getTypeIcon($fileName)
	{
		// Get file extension
		return strtolower(substr($fileName, strrpos($fileName, '.') + 1));
	}

	/**
	 * Checks if the file can be uploaded
	 *
	 * @param   array   $file  File information
	 * @param   string  $err   An error message to be returned
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function canUpload($file, $err = '')
	{
		$params = JComponentHelper::getParams('com_templates');

		if (empty($file['name']))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_UPLOAD_INPUT'), 'error');

			return false;
		}

		// Media file names should never have executable extensions buried in them.
		$executable = array(
			'exe', 'phtml','java', 'perl', 'py', 'asp','dll', 'go', 'jar',
			'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
			'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb',
			'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		);
		$explodedFileName = explode('.', $file['name']);

		if (count($explodedFileName) > 2)
		{
			foreach ($executable as $extensionName)
			{
				if (in_array($extensionName, $explodedFileName))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_EXECUTABLE'), 'error');

					return false;
				}
			}
		}

		jimport('joomla.filesystem.file');

		if ($file['name'] !== JFile::makeSafe($file['name']) || preg_match('/\s/', JFile::makeSafe($file['name'])))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNFILENAME'), 'error');

			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));

		$imageTypes   = explode(',', $params->get('image_formats'));
		$sourceTypes  = explode(',', $params->get('source_formats'));
		$fontTypes    = explode(',', $params->get('font_formats'));
		$archiveTypes = explode(',', $params->get('compressed_formats'));

		$allowable = array_merge($imageTypes, $sourceTypes, $fontTypes, $archiveTypes);

		if ($format == '' || $format == false || (!in_array($format, $allowable)))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

		if (in_array($format, $archiveTypes))
		{
			$zip = new ZipArchive;

			if ($zip->open($file['tmp_name']) === true)
			{
				for ($i = 0; $i < $zip->numFiles; $i++)
				{
					$entry     = $zip->getNameIndex($i);
					$endString = substr($entry, -1);

					if ($endString != DIRECTORY_SEPARATOR)
					{
						$explodeArray = explode('.', $entry);
						$ext          = end($explodeArray);

						if (!in_array($ext, $allowable))
						{
							$app = JFactory::getApplication();
							$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_UNSUPPORTED_ARCHIVE'), 'error');

							return false;
						}
					}
				}
			}
			else
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_OPEN_FAIL'), 'error');

				return false;
			}
		}

		// Max upload size set to 2 MB for Template Manager
		$maxSize = (int) ($params->get('upload_limit') * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNFILETOOLARGE'), 'error');

			return false;
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array(
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote',
			'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div',
			'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html',
			'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing',
			'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option',
			'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike',
			'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml',
			'xmp', '!DOCTYPE', '!--'
		);

		foreach ($html_tags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNIEXSS'), 'error');

				return false;
			}
		}

		return true;
	}
}
