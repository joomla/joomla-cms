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
 * To make a class and its inheriting classes observable:
 * 1) add: implements JObservableInterface
 *    to its class
 *
 * 2) at the end of the constructor, add:
 * // Create observer updater and attaches all observers interested by $this class:
 * $this->_observers = new JObserverUpdater($this);
 * JObserverMapper::attachAllObservers($this);
 *
 * 3) add the function attachObserver below to your class to add observers using the JObserverUpdater class:
 * 	public function attachObserver(JObserverInterface $observer)
 * 	{
 * 		$this->_observers->attachObserver($observer);
 * 	}
 *
 * 4) in the methods that need to be observed, add, e.g. (name of event, params of event):
 * 		$this->_observers->update('onBeforeLoad', array($keys, $reset));
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
	 * @param   JObserverInterface  $observer  The observer to attach to $this observable subject
	 *
	 * @return  void
	 */
	public function attachObserver(JObserverInterface $observer);
}

/**
 * Observer pattern interface for Joomla
 *
 * A class that wants to observe another class must:
 *
 * 1) Add: implements JObserverInterface
 *    to its class
 *
 * 2) Implement a constructor, that can look like this:
 * 	public function __construct(JObservableInterface $observableObject)
 * 	{
 * 	    $observableObject->attachObserver($this);
 *  	$this->observableObject = $observableObject;
 * 	}
 *
 * 3) and must implement the instanciator function createObserver() below, e.g. as follows:
 * 	public static function createObserver(JObservableInterface $observableObject, $params = array())
 * 	{
 * 	    $observer = new self($observableObject);
 *      $observer->... = $params['...']; ...
 * 	    return $observer;
 * 	}
 *
 * 4) Then add functions corresponding to the events to be observed,
 *    E.g. to respond to event: $this->_observers->update('onBeforeLoad', array($keys, $reset));
 *    following function is needed in the obser:
 *  public function onBeforeLoad($keys, $reset) { ... }
 *
 * 5) Finally, the binding is made outside the observable and observer classes, using:
 * JObserverMapper::addObserverClassToClass('ObserverClassname', 'ObservableClassname', array('paramName' => 'paramValue'));
 * where the last array will be provided to the observer instanciator function createObserver.
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
	 * @param   JObservableInterface  $observableObject  The observable subject object
	 * @param   array                 $params            Params for this observer
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
	 * @param   JObservableInterface  $observable  The observable subject object
	 */
	public function __construct(JObservableInterface $observable);

	/**
	 * Adds an observer to the JObservableInterface instance updated by this
	 * This method can be called fron JObservableInterface::attachObserver
	 *
	 * @param   JObserverInterface  $observer  The observer object
	 *
	 * @return   void
	 */
	public function attachObserver(JObserverInterface $observer);

	/**
	 * Call all observers for $event with $params
	 *
	 * @param   string  $event   Event name (function name in observer)
	 * @param   array   $params  Params of event (params in observer function)
	 *
	 * @return  void
	 */
	public function update($event, $params);

	/**
	 * Enable/Disable calling of observers (this is useful when calling parent:: function
	 *
	 * @param   boolean  $enabled  Enable (true) or Disable (false) the observer events
	 *
	 * @return  boolean  Returns old state
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
	 * @param   JObservableInterface  $observable  The observable subject object
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
	 * @param   string         $observerClass    The name of the observer class (implementing JObserverInterface)
	 * @param   string         $observableClass  The name of the observable class (implementing JObservableInterface)
	 * @param   array|boolean  $params           The params to give to the JObserverInterface::createObserver() function, or false to remove mapping
	 *
	 * @return  void
	 */
	public static function addObserverClassToClass($observerClass, $observableClass, $params = array())
	{
		if ($params !== false)
		{
			static::$observations[$observableClass][$observerClass] = $params;
		}
		else
		{
			unset(static::$observations[$observableClass][$observerClass]);
		}
	}

	/**
	 * Attaches all applicable observers to an $observableObject
	 *
	 * @param   JObservableInterface  $observableObject  The observable subject object
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

			// Get parent class name (or false if none), and redo the above on it:
			$observableClass = get_parent_class($observableClass);
		}
	}
}
