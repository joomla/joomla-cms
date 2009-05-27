<?php
/**
 * @version		$Id: event.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Event
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.base.observer');

/**
 * JEvent Class
 *
 * @abstract
 * @package		Joomla.Framework
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