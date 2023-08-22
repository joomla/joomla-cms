<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Finder;

use Joomla\CMS\Event\Model\SaveEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event.
 * Example:
 *  new AfterSaveEvent('onEventName', ['context' => 'com_example.example', 'subject' => $itemObjectToSave, 'isNew' => $isNew]);
 *
 * @since  5.0.0
 */
class AfterSaveEvent extends SaveEvent implements FinderEventInterface
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'isNew'];
}
