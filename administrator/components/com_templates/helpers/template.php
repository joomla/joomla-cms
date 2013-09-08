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
 * @subpackage  com_templates
 * @since       1.5
 */
abstract class TemplateHelper
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
	public static function canUpload($file, $err = '')
	{

		if (empty($file['name']))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_UPLOAD_INPUT'), 'error');

			return false;
		}

		// Media file names should never have executable extensions buried in them.
		$executable = array('exe', 'phtml','java', 'perl', 'py', 'asp','dll', 'go', 'jar',
								'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
								'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb',
								'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh');
		$explodedFileName = explode('.', $file['name']);

		if (count($explodedFileName > 2))
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

		$allowable = array('jpg', 'jpeg', 'png', 'gif', 'php', 'js', 'less', 'ini', 'css', 'xml', 'eot', 'ttf', 'otf', 'woff');

		if ($format == '' || $format == false || (!in_array($format, $allowable)))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

        // Max upload size set to 2 MB for Template Manager
		$maxSize = (int) (2 * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNFILETOOLARGE'), 'error');

			return false;
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');

		foreach ($html_tags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>'))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_WARNIEXSS'), 'error');

				return false;
			}
		}

		return true;
	}

}
