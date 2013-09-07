<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Observer updater pattern implementation for Joomla
 *
 * @package     Joomla.Platform
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverUpdater
 * @since       3.1.2
 */
interface JObserverUpdaterInterface
{
	/**
	 * Constructor
	 *
	 * @param   JObservableInterface  $observable  The observable subject object
	 *
	 * @since   3.1.2
	 */
	public function __construct(JObservableInterface $observable);

	/**
	 * Adds an observer to the JObservableInterface instance updated by this
	 * This method can be called fron JObservableInterface::attachObserver
	 *
	 * @param   JObserverInterface  $observer  The observer object
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function attachObserver(JObserverInterface $observer);

	/**
	 * Call all observers for $event with $params
	 *
	 * @param   string  $event   Event name (function name in observer)
	 * @param   array   $params  Params of event (params in observer function)
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function update($event, $params);

	/**
	 * Enable/Disable calling of observers (this is useful when calling parent:: function
	 *
	 * @param   boolean  $enabled  Enable (true) or Disable (false) the observer events
	 *
	 * @return  boolean  Returns old state
	 *
	 * @since   3.1.2
	 */
	public function doCallObservers($enabled);
}
