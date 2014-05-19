<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests\Stubs;

use Joomla\Event\Event;

/**
 * A listener used to test the triggerEvent method in the dispatcher.
 * It will be added in third position.
 *
 * @since  1.0
 */
class ThirdListener
{
	/**
	 * Listen to onSomething.
	 *
	 * @param   Event  $event  The event.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onSomething(Event $event)
	{
		$listeners = $event->getArgument('listeners');

		$listeners[] = 'third';

		$event->setArgument('listeners', $listeners);
	}
}
