<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/19/13 2:03 PM $
* @package CBLib\Observer
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Observer;

defined('CBLIB') or die();

/**
 * CBLib\Observer\ObserverMapper Class Observer mapping pattern implementation
 *
 * @package CBLib\Observer
 */
class ObserverMapper {
	/**
	 * Array: array( ObservableInterface_classname => array( ObserverInterface_classname => array( paramname => param, .... ) ) )
	 *
	 * @var    array
	 */
	protected static $observations = array();

	/**
	 * Adds a mapping to observe $observerClass subjects with $observableClass observer/listener, attaching it on creation with $params
	 * on $observableClass instance creations
	 *
	 * @param   string         $observerClass    The name of the observer class (implementing ObserverInterface)
	 * @param   string         $observableClass  The name of the observable class (implementing ObservableInterface)
	 * @param   array|boolean  $params           The params to give to the ObserverInterface::createObserver() function, or false to remove mapping
	 * @return  void
	 */
	public static function addObserverClassToClass($observerClass, $observableClass, $params = array())
	{
		if ($params !== false) {
			static::$observations[$observableClass][$observerClass] = $params;
		} else {
			unset(static::$observations[$observableClass][$observerClass]);
		}
	}

	/**
	 * Attaches all applicable observers to an $observableObject
	 *
	 * @param   ObservableInterface  $observableObject  The observable subject object
	 * @return  void
	 */
	public static function attachAllObservers(ObservableInterface $observableObject)
	{
		$observableClass = get_class($observableObject);

		while ($observableClass != false) {
			// Attach applicable Observers for the class to the Observable subject:
			if (isset(static::$observations[$observableClass])) {
				foreach (static::$observations[$observableClass] as $observerClass => $params) {
					/**
					 * Attach an Observer to the Observable subject:
					 * @var ObserverInterface $observerClass
					 */
					$observerClass::createObserver($observableObject, $params);
				}
			}

			// Get parent class name (or false if none), and redo the above on it:
			$observableClass = get_parent_class($observableClass);
		}
	}
}
