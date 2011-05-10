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
 * @subpackage  Application
 */
class JFilterInputMockTracker
{
	public $calls = array();

	public function __call($name, $arguments)
	{
		if (!isset($this->calls[$name])) {
			$this->calls[$name] = array();
		}
		$this->calls[$name][] = $arguments;
		return $arguments[0];
	}

}
