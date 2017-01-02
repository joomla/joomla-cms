<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JUtility is a utility functions class
 *
 * @since  11.1
 */
class JUtility
{
	/**
	 * Method to extract key/value pairs out of a string with XML style attributes
	 *
	 * @param   string  $string  String containing XML style attributes
	 *
	 * @return  array  Key/Value pairs for the attributes
	 *
	 * @since   11.1
	 */
	public static function parseAttributes($string)
	{
		$attr = array();
		$retarray = array();

		// Let's grab all the key/value pairs using a regular expression
		preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

		if (is_array($attr))
		{
			$numPairs = count($attr[1]);

			for ($i = 0; $i < $numPairs; $i++)
			{
				$retarray[$attr[1][$i]] = $attr[2][$i];
			}
		}

		return $retarray;
	}

	/**
	 * Method to get the maximum allowed file size for the HTTP uploads based on the active PHP configuration
	 *
	 * @param   mixed  $custom  A custom upper limit, if the PHP settings are all above this then this will be used
	 *
	 * @return  int  Size in number of bytes
	 *
	 * @since   3.7.0
	 */
	public static function getMaxUploadSize($custom = null)
	{
		if ($custom)
		{
			$custom = JHtml::_('number.bytes', $custom, '');

			if ($custom > 0)
			{
				$sizes[] = $custom;
			}
		}

		/*
		 * Read INI settings which affects upload size limits
		 * and Convert each into number of bytes so that we can compare
		 */
		$sizes[] = JHtml::_('number.bytes', ini_get('memory_limit'), '');
		$sizes[] = JHtml::_('number.bytes', ini_get('post_max_size'), '');
		$sizes[] = JHtml::_('number.bytes', ini_get('upload_max_filesize'), '');

		// The minimum of these is the limiting factor
		return min($sizes);
	}
}
