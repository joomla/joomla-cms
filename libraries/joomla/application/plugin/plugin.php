<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.base.observer' );

/**
 * JPlugin Class
 *
 * @abstract
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JPlugin extends JObserver 
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	function JPlugin(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Method to trigger events based upon the JAuthenticate object
	 *
	 * @access public
	 * @param array Arguments
	 * @return mixed Routine return value
	 * @since 1.5
	 */
	function update(& $args)
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

?>