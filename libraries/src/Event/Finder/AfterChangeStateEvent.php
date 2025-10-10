<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Finder;

use Joomla\CMS\Event\Model\ChangeStateEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event.
 * Example:
 *  new AfterChangeStateEvent('onEventName', ['context' => 'com_example.example', 'subject' => $primaryKeys, 'value' => $newState]);
 *
 * @since  5.0.0
 */
class AfterChangeStateEvent extends ChangeStateEvent implements FinderEventInterface
{
}
