<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// The 3 interfaces and 1 class below could be in their own file but will be used only after
// JObserverMapper is autoloaded, so can stay here for now.

/**
 * Observable Subject pattern interface for Joomla
 *
 * @package     Joomla
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObservableInterface
 * @since       3.1.2
 */
interface JObservableInterface
{
	/**
	 * Adds an observer to this JObservableInterface instance.
	 * Ideally, this method should be called fron the constructor of JObserverInterface
	 * which should be instanciated by JObserverMapper.
	 * The implementation of this function can use JObserverUpdater
	 *
	 * @param    JObserverInterface   $observer
	 *
	 * @return   void
	 */
	public function attachObserver(JObserverInterface $observer);
}

/**
 * Observer pattern interface for Joomla
 *
 * @package     Joomla
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverInterface
 * @since       3.1.2
 */
interface JObserverInterface
{
	/**
	 * Creates the associated observer instance and attaches it to the $observableObject
	 *
	 * @param   JObservableInterface   $observableObject
	 * @param   array                  $params
	 *
	 * @return  JObserverInterface
	 */
	public static function createObserver(JObservableInterface $observableObject, $params = array());
}

/**
 * Observer updater pattern implementation for Joomla
 *
 * @package     Joomla
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverUpdater
 * @since       3.1.2
 */
interface JObserverUpdaterInterface
{
	/**
	 * Constructor
	 *
	 * @param   JObservableInterface   $observable
	 */
	public function __construct(JObservableInterface $observable);
	/**
	 * Adds an observer to the JObservableInterface instance updated by this
	 * This method can be called fron JObservableInterface::attachObserver
	 *
	 * @param    JObserverInterface   $observer
	 *
	 * @return   void
	 */
	public function attachObserver(JObserverInterface $observer);

	/**
	 * Call all observers for $event with $params
	 *
	 * @param   string   $event
	 * @param   array    $params
	 *
	 * @return  void
	 */
	public function update($event, $params);

	/**
	 * Enable/Disable calling of observers (this is useful when calling parent:: function
	 *
	 * @param   boolean   $enabled
	 *
	 * @return  boolean   Returns old state
	 */
	public function doCallObservers($enabled);
}

/**
 * Observer updater pattern implementation for Joomla
 *
 * @package     Joomla
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverUpdater
 * @since       3.1.2
 */
class JObserverUpdater implements JObserverUpdaterInterface
{
	/**
	 * Generic JObserverInterface observers for this JObservableInterface
	 *
	 * @var    JObserverInterface[]
	 */
	protected $observers = array();

	/**
	 * Process observers (useful when a class extends significantly an observerved method, and calls observers itself
	 * @var    boolean
	 */
	protected $doCallObservers = true;

	/**
	 * Constructor
	 *
	 * @param   JObservableInterface   $observable
	 */
	public function __construct(JObservableInterface $observable)
	{
		// Not yet needed, but possible:  $this->observable = $observable;
	}

	/**
	 * Adds an observer to the JObservableInterface instance updated by this
	 * This method can be called fron JObservableInterface::attachObserver
	 *
	 * @param    JObserverInterface   $observer
	 *
	 * @return   void
	 */
	public function attachObserver(JObserverInterface $observer)
	{
		$this->observers[get_class($observer)] = $observer;
	}

	/**
	 * Gets the instance of the observer of class $observerClass
	 *
	 * @param    string          $observerClass
	 *
	 * @return   JTableObserver|null
	 *
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
	 * @param   string   $event
	 * @param   array    $params
	 *
	 * @return  void
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
	 * @param   boolean   $enabled
	 *
	 * @return  boolean   Returns old state
	 */
	public function doCallObservers($enabled)
	{
		$oldState = $this->doCallObservers;
		$this->doCallObservers = $enabled;
		return $oldState;
	}
}

/**
 * Observer mapping pattern implementation for Joomla
 *
 * @package     Joomla
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverMapper
 * @since       3.1.2
 */
class JObserverMapper
{
	/**
	 * Array: array( JObservableInterface_classname => array( JObserverInterface_classname => array( paramname => param, .... ) ) )
	 *
	 * @var array[]
	 */
	protected static $observations = array();

	/**
	 * Adds a mapping to observe $observerClass subjects with $observableClass observer/listener, attaching it on creation with $params
	 * on $observableClass instance creations
	 *
	 * @param   string   $observerClass
	 * @param   string   $observableClass
	 * @param   array    $params
	 *
	 * @return  void
	 */
	public static function addObserverClassToClass($observerClass, $observableClass, $params = array())
	{
		static::$observations[$observableClass][$observerClass] = $params;
	}

	/**
	 * Attaches all applicable observers to an $observableObject
	 *
	 * @param   JObservableInterface   $observableObject
	 *
	 * @return  void
	 */
	public static function attachAllObservers(JObservableInterface $observableObject)
	{
		$observableClass = get_class($observableObject);
		while ($observableClass != false)
		{
			// Attach applicable Observers for the class to the Observable subject:
			if (isset(static::$observations[$observableClass]))
			{
				foreach (static::$observations[$observableClass] as $observerClass => $params)
				{
					// Attach an Observer to the Observable subject:
					/**
					 * @var JObserverInterface $observerClass
					 */
					$observerClass::createObserver($observableObject, $params);
				}
			}
			$observableClass = get_parent_class($observableClass);
		}
	}
}
