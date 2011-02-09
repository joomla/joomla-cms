<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Event
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.observer');

/**
 * JEvent Class
 *
 * @abstract
 * @package		Joomla.Platform
 * @subpackage	Event
 * @since		1.5
 */
abstract class JEvent extends JObserver
{
	/**
	 * Method to trigger events.
	 *
	 * @access public
	 * @param array Arguments
	 * @return mixed Routine return value
	 * @since 1.5
	 */
	public function update(&$args)
	{
		/*
		 * First lets get the event from the argument array.  Next we will unset the
		 * event argument as it has no bearing on the method to handle the event.
		 */
		$event = $args['event'];
		unset($args['event']);

		/*
		 * If the method to handle an event exists, call it and return its return
		 * value.  If it does not exist, return null.
		 */
		if (method_exists($this, $event)) {
			return call_user_func_array(array($this, $event), $args);
		} else {
			return null;
		}
	}
}