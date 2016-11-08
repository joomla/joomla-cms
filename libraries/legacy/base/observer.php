<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Abstract observer class to implement the observer design pattern
 *
 * @since       1.5
 * @deprecated  2.5
 */
abstract class JObserver extends JObject
{
	/**
	 * Event object to observe.
	 *
	 * @var    object
	 * @since  1.5
	 * @deprecated  2.5
	 */
	protected $_subject = null;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe.
	 *
	 * @since   1.5
	 * @deprecated  2.5
	 */
	public function __construct(&$subject)
	{
		// Register the observer ($this) so we can be notified
		$subject->attach($this);

		// Set the subject to observe
		$this->_subject = &$subject;
	}

	/**
	 * Method to update the state of observable objects
	 *
	 * @param   array  &$args  An array of arguments to pass to the listener.
	 *
	 * @return  mixed
	 *
	 * @since   1.5
	 * @deprecated  2.5
	 */
	abstract public function update(&$args);
}
