<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Observer updater pattern implementation for Joomla
 *
 * @since  3.1.2
 */
class JObserverUpdater implements JObserverUpdaterInterface
{
	/**
	 * Holds the key aliases for observers.
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $aliases = array();

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
	 * This method can be called from JObservableInterface::attachObserver
	 *
	 * @param   JObserverInterface  $observer  The observer object
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function attachObserver(JObserverInterface $observer)
	{
		$class = get_class($observer);

		// Also register the alias if exists
		foreach (JLoader::getDeprecatedAliases() as $alias)
		{
			$realClass  = trim($alias['new'], '\\');

			// Check if we have an alias for the observer class
			if ($realClass === $class)
			{
				$aliasClass = trim($alias['old'], '\\');

				// Add an alias to known aliases
				$this->aliases[$aliasClass] = $class;
			}
		}

		// Register the real class
		$this->observers[$class] = $observer;
	}

	/**
	 * Removes an observer from the JObservableInterface instance updated by this
	 * This method can be called from JObservableInterface::attachObserver
	 *
	 * @param   String  $observer  The observer class name
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function detachObserver($observer)
	{
		$observer = trim($observer, '\\');

		if (isset($this->aliases[$observer]))
		{
			$observer = $this->aliases[$observer];
		}

		if (isset($this->observers[$observer]))
		{
			unset($this->observers[$observer]);
		}
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
		$observerClass = trim($observerClass, '\\');

		if (isset($this->aliases[$observerClass]))
		{
			$observerClass = $this->aliases[$observerClass];
		}

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
