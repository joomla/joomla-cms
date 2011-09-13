<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/event/dispatcher.php';

/**
 * Inspector JContentHelperTest class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JDispatcherInspector extends JDispatcher
{
	/**
	 * Allows the internal singleton to be set and mocked.
	 *
	 * @param   JDispatcher  $instance  A dispatcher object.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setInstance($instance)
	{
		self::$instance = $instance;
	}
}