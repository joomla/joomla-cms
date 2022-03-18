<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       1.7.0
 */
class FooFormFieldModal_Bar extends JFormField
{
	/**
	 * Method to get the field input.
	 *
	 * @return  string        The field input.
	 */
	protected function getInput()
	{
		return 'Modal';
	}
}
