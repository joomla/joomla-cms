<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Some class.
 *
 * @package  SomePackage
 *
 * @since    0
 */
class BogusLoad
{
	public $someMethodCalled = false;

	/**
	 * Some method.
	 *
	 * @return void
	 */
	public function someMethod ()
	{
		$this->someMethodCalled = true;
	}
}
