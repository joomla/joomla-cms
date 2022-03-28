<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector for the JApplicationCli class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @since       1.7.0
 */
class JApplicationCliInspector extends JApplicationCli
{
	/**
	 * The exit code if the application was closed otherwise null.
	 *
	 * @var     integer
	 * @since   1.7.3
	 */
	public $closed;

	/**
	 * Mimic exiting the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	public function close($code = 0)
	{
		$this->closed = $code;
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	public function doExecute()
	{
		$this->triggerEvent('JWebDoExecute');
	}
}
