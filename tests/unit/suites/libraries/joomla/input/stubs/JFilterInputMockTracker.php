<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JFilterInputMockTracker test class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Input
 * @since       11.1
 */
class JFilterInputMockTracker
{
	public $calls = array();

	/**
	 * Test __call
	 *
	 * @param   string  $name       @todo
	 * @param   mixed   $arguments  @todo
	 *
	 * @return void
	 */
	public function __call($name, $arguments)
	{
		if (!isset($this->calls[$name]))
		{
			$this->calls[$name] = array();
		}
		$this->calls[$name][] = $arguments;

		return $arguments[0];
	}
}
