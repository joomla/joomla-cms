<?php
/**
 * @package     Joomla.UnitTest
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

abstract class JUnitHelper
{
	public static function normalize($path)
	{
		return strtr($path, '\\', '/');
	}
}
