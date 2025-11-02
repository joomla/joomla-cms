<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Module;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Module events.
 * Example:
 *  new AfterCleanModuleListEvent('onEventName', ['subject' => $modules]);
 *
 * @since  5.0.0
 */
class AfterCleanModuleListEvent extends ModuleListEvent
{
}
