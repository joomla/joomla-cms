<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JHtmlBootstrap
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
abstract class JHtmlBootstrapInspector extends JHtmlBootstrap
{
	/**
	 * Resets the JHtmlBootstrap::$loaded array
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
