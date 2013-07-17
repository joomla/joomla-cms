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

