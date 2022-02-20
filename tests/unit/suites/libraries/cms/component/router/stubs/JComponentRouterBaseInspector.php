<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector for JComponentRouterBase
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterBaseInspector extends JComponentRouterBase
{
	/**
	 * Runs the protected createURI() method
	 *
	 * @param   array   $url  valid inputs to the createURI() method
	 *
	 * @return  object  JUri object from the given parameters
	 *
	 * @since   3.4
	 */
	public function get($key)
	{
		return $this->$key;
	}

	public function build(&$query)
	{
	}

	public function parse(&$segments)
	{

	}
}
