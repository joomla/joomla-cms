<?php
/**
 * @version		$Id:observer.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Abstract observer class to implement the observer design pattern
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
abstract class JObserver extends JObject
{
	/**
	 * Event object to observe.
	 *
	 * @var		object
	 */
	protected $_subject = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object		$subject	The object to observe.
	 * @return	void
	 */
	function __construct(&$subject)
	{
		// Register the observer ($this) so we can be notified
		$subject->attach($this);

		// Set the subject to observe
		$this->_subject = &$subject;
	}

	/**
	 * Method to update the state of observable objects
	 *
	 * @abstract	Implement in child classes
	 * @access		public
	 * @param		array		$args		An array of arguments to pass to the listener.
	 * @return		mixed
	 */
	abstract public function update(&$args);
}