<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

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
	 * Adds an observer to this JTable instance.
	 * Ideally, this method should be called fron the constructor of JTableObserver
	 * which should be instanciated by the constructor of $this.
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
	 * The observed table
	 *
	 * @var   JTable
	 */
	protected $table;

	protected static $observations = array();

	/**
	 * Constructor: Associates to $table $this observer
	 *
	 * @param   JTable   $table
	 */
	public function __construct(JTable $table)
	{
		$table->attachObserver($this);
		$this->table = $table;
	}

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
			static::attachObserversToAnObservable($observableClass, $observableObject);
			$observableClass = get_parent_class($observableClass);
		}
	}

	/**
	 * Internal method
	 * Goes through mappings for a given observable class and attaches all corresponding observer classes to it.
	 * It does check also for all parent classes as well.
	 *
	 * @param   string                 $observableClass
	 * @param   JObservableInterface   $observableObject
	 *
	 * @return  void
	 */
	protected static function attachObserversToAnObservable($observableClass, JObservableInterface $observableObject)
	{
		if (isset(static::$observations[$observableClass]))
		{
			foreach (static::$observations[$observableClass] as $observerClass => $params)
			{
				static::attachObserverToObservable($observerClass, $observableObject, $params);
			}
		}
	}

	/**
	 * Internal method
	 * Attaches a observer to an observable subject
	 *
	 * @param   string                 $observerClass     (of type JObserverInterface)
	 * @param   JObservableInterface   $observableObject
	 * @param   array                  $params
	 *
	 * @return  void
	 */
	protected static function attachObserverToObservable($observerClass, JObservableInterface $observableObject, $params)
	{
		/**
		 * @var JObserverInterface $observerClass
		 */
		$observerClass::createObserver($observableObject, $params);
	}
}

