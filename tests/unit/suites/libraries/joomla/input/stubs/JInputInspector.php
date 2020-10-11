<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector class for the JInput library.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Input
 * @since       1.7.0
 */
class JInputInspector extends JInput
{
	public $options;

	public $filter;

	public $data;

	public $inputs;

	public static $registered;

	/**
	 * Test...
	 *
	 * @return void
	 */
	public static function register()
	{
		return parent::register();
	}
}
