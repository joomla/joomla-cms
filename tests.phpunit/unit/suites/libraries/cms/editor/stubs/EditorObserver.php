<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
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
