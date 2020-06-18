<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Editor
 *
 * @copyright   © 2015 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Stub observer for the editor class
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 * @since       3.4.4
 */
class EditorObserver extends JEditor
{
	/**
	 * Dummy public method for testing
	 *
	 * @return  string
	 *
	 * @since   3.4.4
	 */
	public function onInit()
	{
		return 'someString';
	}
}
