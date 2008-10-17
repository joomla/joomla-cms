<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Event
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// No direct access
defined('JPATH_BASE') or die();

jimport( 'joomla.base.observer' );

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
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	protected function __construct(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Method to trigger events
	 *
	 * @access public
	 * @param array Arguments
	 * @return mixed Routine return value
	 * @since 1.5
	 */
	public function update(& $args)
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
			return call_user_func_array ( array($this, $event), $args );
		} else {
			return null;
		}
	}
}
