<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

defined('_JEXEC') or die();

use Joomla\Event\Event;

trait EventReturnAware
{
	/**
	 * Adds a result value to an event
	 *
	 * @param   Event   $event  The event we were processing
	 * @param   mixed   $value  The value to append to the event's results
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	private function returnFromEvent(Event $event, $value = null): void
	{
		$result = $event->getArgument('result') ?: [];

		if (!is_array($result))
		{
			$result = [$result];
		}

		$result[] = $value;

		$event->setArgument('result', $result);
	}
}
