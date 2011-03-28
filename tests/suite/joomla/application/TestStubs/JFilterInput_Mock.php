<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application
 */
class JFilterInputMock
{

	public function clean($input)
	{
		return $input;
	}
}