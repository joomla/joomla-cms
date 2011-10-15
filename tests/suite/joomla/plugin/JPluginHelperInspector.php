<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/plugin/helper.php';

/**
 * Inspector for the JPluginHelper class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JPluginHelperInspector extends JPluginHelper
{
	/**
	 * Allows the internal plugins store to be set and mocked.
	 *
	 * @param   mixed  $plugins  The value to set.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setPlugins($plugins)
	{
		self::$plugins = $plugins;
	}
}