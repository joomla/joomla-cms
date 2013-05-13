<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

class FOFStringUtils
{

	public static function toSlug($value)
	{
		//remove any '-' from the string they will be used as concatonater
		$value = str_replace('-', ' ', $value);

		//convert to ascii characters
		$value = self::toASCII($value);

		//lowercase and trim
		$value = trim(strtolower($value));

		//remove any duplicate whitespace, and ensure all characters are alphanumeric
		$value = preg_replace(array('/\s+/', '/[^A-Za-z0-9\-]/'), array('-', ''), $value);

		//limit length
		if (strlen($value) > 100)
		{
			$value = substr($value, 0, 100);
		}

		return $value;
	}

	public static function toASCII($value)
	{
		$string = htmlentities(utf8_decode($value));
		$string = preg_replace(
			array('/&szlig;/', '/&(..)lig;/', '/&([aouAOU])uml;/', '/&(.)[^;]*;/'), array('ss', "$1", "$1" . 'e', "$1"), $string);

		return $string;
	}

}