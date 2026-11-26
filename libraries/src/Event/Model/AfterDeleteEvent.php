<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

\defined('_JEXEC') or die;

/**
 * Class for Model event.
 * Example:
 *  new AfterDeleteEvent('onEventName', ['context' => 'com_example.example', 'subject' => $itemObjectToDelete]);
 *
 * @since  5.0.0
 */
class AfterDeleteEvent extends DeleteEvent
{
}
