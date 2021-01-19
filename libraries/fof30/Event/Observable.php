<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Event;

defined('_JEXEC') || die;

/**
 * Interface Observable
 *
 * @codeCoverageIgnore
 */
interface Observable
{
	/**
	 * Attaches an observer to the object
	 *
	 * @param   Observer  $observer  The observer to attach
	 *
	 * @return  Observable  Ourselves, for chaining
	 */
	public function attach(Observer $observer);

	/**
	 * Detaches an observer from the object
	 *
	 * @param   Observer  $observer  The observer to detach
	 *
	 * @return  Observable  Ourselves, for chaining
	 */
	public function detach(Observer $observer);

	/**
	 * Triggers an event in the attached observers
	 *
	 * @param   string  $event  The event to attach
	 * @param   array   $args   Arguments to the event handler
	 *
	 * @return  array
	 */
	public function trigger($event, array $args = []);
}
