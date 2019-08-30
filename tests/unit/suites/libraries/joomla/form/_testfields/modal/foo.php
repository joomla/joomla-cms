<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       1.7.0
 */
class JFormFieldModal_Foo extends JFormField
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
