<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

\defined('_JEXEC') or die;

/**
 * Class for User delete event.
 * Example:
 *  new BeforeDeleteEvent('onEventName', ['subject' => $userArray]);
 *
 * @since  5.0.0
 */
class BeforeDeleteEvent extends AbstractDeleteEvent
{
}
