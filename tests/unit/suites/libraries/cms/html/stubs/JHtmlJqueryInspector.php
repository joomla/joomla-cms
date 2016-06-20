<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JHtmlJquery
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
abstract class JHtmlJqueryInspector extends JHtmlJquery
{
	/**
	 * Resets the JHtmlJquery::$loaded array
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function resetLoaded()
	{
		static::$loaded = array();
	}
}
