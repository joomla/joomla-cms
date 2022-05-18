<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\TwoFactor;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Concrete event class for the custom events used to notify the User Action Log plugin about Two
 * Factor Authentication actions.
 *
 * @since __DEPLOY_VERSION__
 */
class NotifyActionLog extends AbstractImmutableEvent
{
	private const ACCEPTABLE_EVENTS = [
		'onComUsersCaptiveValidateSuccess',
		'onComUsersViewMethodsAfterDisplay',
		'onComUsersCaptiveShowCaptive',
		'onComUsersCaptiveShowSelect',
		'onComUsersCaptiveValidateFailed',
		'onComUsersCaptiveValidateInvalidMethod',
		'onComUsersCaptiveValidateSuccess',
		'onComUsersControllerMethodAfterRegenbackupcodes',
		'onComUsersControllerMethodBeforeAdd',
		'onComUsersControllerMethodBeforeDelete',
		'onComUsersControllerMethodBeforeEdit',
		'onComUsersControllerMethodBeforeSave',
		'onComUsersControllerMethodsBeforeDisable',
		'onComUsersControllerMethodsBeforeDontshowthisagain',
		'onComUsersControllerConvertAfterConvert',
	];

	/**
	 * Public constructor
	 *
	 * @param   string  $name       Event name. Must belong in self::ACCEPTABLE_EVENTS
	 * @param   array   $arguments  Event arguments (different for each event).
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, array $arguments = [])
	{
		if (!in_array($name, self::ACCEPTABLE_EVENTS))
		{
			throw new \InvalidArgumentException(sprintf('The %s event class does not support the %s event name.', __CLASS__, $name));
		}

		parent::__construct($name, $arguments);
	}
}
