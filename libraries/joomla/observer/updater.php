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
class JObserverUpdater implements JObserverUpdaterInterface
{
	/**
	 * Generic JObserverInterface observers for this JObservableInterface
	 *
	 * @var    JObserverInterface
	 * @since  3.1.2
	 */
	protected $observers = array();

	/**
	 * Process observers (useful when a class extends significantly an observerved method, and calls observers itself
	 *
	 * @var    boolean
	 * @since  3.1.2
	 */
	protected $doCallObservers = true;

	/**
	 * Constructor
	 *
	 * @param   JObservableInterface  $observable  The observable subject object
	 *
	 * @since   3.1.2
	 */
	public function __construct(JObservableInterface $observable)
	{
		// Not yet needed, but possible:  $this->observable = $observable;
	}

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
	public function attachObserver(JObserverInterface $observer)
	{
		$this->observers[get_class($observer)] = $observer;
	}

	/**
	 * Gets the instance of the observer of class $observerClass
	 *
	 * @param   string  $observerClass  The class name of the observer
	 *
	 * @return  JTableObserver|null  The observer object of this class if any
	 *
	 * @since   3.1.2
	 */
	public function getObserverOfClass($observerClass)
	{
		if (isset($this->observers[$observerClass]))
		{
			return $this->observers[$observerClass];
		}

		return null;
	}

	/**
	 * Call all observers for $event with $params
	 *
	 * @param   string  $event   Name of the event
	 * @param   array   $params  Params of the event
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function update($event, $params)
	{
		if ($this->doCallObservers)
		{
			foreach ($this->observers as $observer)
			{
				$eventListener = array($observer, $event);

				if (is_callable($eventListener))
				{
					call_user_func_array($eventListener, $params);
				}
			}
		}
	}

	/**
	 * Enable/Disable calling of observers (this is useful when calling parent:: function
	 *
	 * @param   boolean  $enabled  Enable (true) or Disable (false) the observer events
	 *
	 * @return  boolean  Returns old state
	 *
	 * @since   3.1.2
	 */
	public function doCallObservers($enabled)
	{
		$oldState = $this->doCallObservers;
		$this->doCallObservers = $enabled;

		return $oldState;
	}
}
