<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event.
 * Example:
 *  new AfterDeleteEvent('onEventName', ['context' => 'com_example.example', 'subject' => $itemObjectToDelete]);
 *
 * @since  5.0.0
 */
class AfterDeleteEvent extends DeleteEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        // A backward compatibility check for onUserAfterDeleteGroup
        if ($name === 'onUserAfterDeleteGroup') {
            // @TODO: In Joomla 6 the event should use 'context', 'subject' only
            $this->legacyArgumentsOrder = ['data', 'deletingResult', 'errorMessage', 'context', 'subject'];
        }

        parent::__construct($name, $arguments);
    }
}
