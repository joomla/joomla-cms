<?php
/**
 * Part of the Joomla Framework Utilities Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Utilities;

/**
 * Dom Helper.
 *
 * @since  3.8
 */
class DomHelper
{
	public static $dom = null;

	private static function getDomInstance()
	{
		return self::$dom ? self::$dom : self::$dom = new DOMDocument();
	}

	/**
	 * Function to fix Html errors like missing end tags.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public static function fixHtml($html)
	{
		$dom = self::getDomInstance();
		@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NODEFDTD);
		// Fix the html errors
		$value = $dom->saveHtml($dom->getElementsByTagName('body')->item(0));

		// Remove body tag
		return mb_strimwidth($value, 6, mb_strwidth($value, 'UTF-8') - 13, '', 'UTF-8');
	}
}
