<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
class JHtmlInspector
{
	public static $arguments = array();

	public static $returnValue;

	/**
	 * Stores the arguments passed to the method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function method1()
	{
		if (!isset(self::$arguments)) {
			self::$arguments = array(func_get_args());
		}
		else {
			self::$arguments[] = func_get_args();
		}

		if (isset(self::$returnValue)) {
			return self::$returnValue;
		}
		else {
			return 'JHtmlInspector::method1';
		}
	}
}
