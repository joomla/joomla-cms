<?php

/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2024 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

/**
 * Transitional interface for event subscribers.
 *
 * The interface is need to distinguish plugins with SubscriberInterface that use deprecated registerListeners() method,
 * from plugins with SubscriberInterface and not use registerListeners() method.
 *
 * @TODO: Deprecate at 6.0 and remove at 8.0.
 *
 * @since  __DEPLOY_VERSION__
 */
interface SubscriberInterface extends \Joomla\Event\SubscriberInterface
{
}
