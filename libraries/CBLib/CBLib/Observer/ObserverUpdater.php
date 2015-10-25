<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/19/13 2:01 PM $
* @package CBLib\Observer
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Observer;

defined('CBLIB') or die();

/**
 * CBLib\Observer\ObserverUpdater Class Observer updater pattern implementation
 *
 * @package CBLib\Observer
 */
class ObserverUpdater implements ObserverUpdaterInterface {
	/**
	 * Generic ObserverInterface observers for this ObservableInterface
	 *
	 * @var    ObserverInterface[]
	 */
	protected $observers = array();

	/**
	 * Process observers (useful when a class extends significantly an observerved method, and calls observers itself
	 *
	 * @var    boolean
	 */
	protected $doCallObservers = true;

	/**
	 * Constructor
	 *
	 * @param   ObservableInterface  $observable  The observable subject object
	 */
	public function __construct(ObservableInterface $observable)
	{
		// Not yet needed, but possible:  $this->observable = $observable;
	}

	/**
	 * Adds an observer to the ObservableInterface instance updated by this
	 * This method can be called fron ObservableInterface::attachObserver
	 *
	 * @param   ObserverInterface  $observer  The observer object
	 * @return  void
	 */
	public function attachObserver(ObserverInterface $observer)
	{
		$this->observers[get_class($observer)] = $observer;
	}

	/**
	 * Removes $observer from the ObservableInterface instance updated by this
	 *
	 * @param   ObserverInterface  $observer  The observer object
	 * @return  void
	 */
	public function detachObserver(ObserverInterface $observer)
	{
		unset( $this->observers[get_class($observer)] );
	}

	/**
	 * Gets the instance of the observer of class $observerClass
	 *
	 * @param   string  $observerClass  The class name of the observer
	 * @return  ObserverInterface|null  The observer object of this class if any
	 */
	public function getObserverOfClass($observerClass)
	{
		if (isset($this->observers[$observerClass])) {
			return $this->observers[$observerClass];
		}

		return null;
	}

	/**
	 * Call all observers for $event with $params
	 *
	 * @param   string  $event   Name of the event
	 * @param   array   $params  Params of the event
	 * @return  void
	 */
	public function update($event, $params)
	{
		if ($this->doCallObservers) {
			foreach ($this->observers as $observer) {
				$eventListener = array($observer, $event);

				if (is_callable($eventListener)) {
					call_user_func_array($eventListener, $params);
				}
			}
		}
	}

	/**
	 * Enable/Disable calling of observers (this is useful when calling parent:: function
	 *
	 * @param   boolean  $enabled  Enable (true) or Disable (false) the observer events
	 * @return  boolean  Returns old state
	 */
	public function doCallObservers($enabled)
	{
		$oldState = $this->doCallObservers;
		$this->doCallObservers = $enabled;

		return $oldState;
	}

}