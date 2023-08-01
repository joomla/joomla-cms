<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The FallbackEvent class used for b/c $event->getArgument(0).
 * Is a fallback for CoreEventAware::getEventClassByEventName, when the event class not found.
 * It should be removed in Joomla 6.
 *
 * @since  __DEPLOY_VERSION__
 *
 * @deprecated Use event classes, will be removed in Joomla 6.
 */
final class FallbackEvent extends Event
{
    /**
     * Get an event argument value.
     *
     * @param   string  $name     The argument name.
     * @param   mixed   $default  The default value if not found.
     *
     * @return  mixed  The argument value or the default value.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getArgument($name, $default = null)
    {
        // B/C check for numeric access to named argument, eg $event->getArgument('0').
        if (is_numeric($name)) {
            if (key($this->arguments) != 0) {
                $argNames = \array_keys($this->arguments);
                $name     = $argNames[$name] ?? '';
            }

            @trigger_error(
                sprintf(
                    'Numeric access to named event arguments is deprecated, and will not work in Joomla 6. Event %s argument %s',
                    \get_class($this),
                    $name
                ),
                E_USER_DEPRECATED
            );
        }

        return parent::getArgument($name, $default);
    }

    /**
     * Add argument to event.
     *
     * @param   string  $name   Argument name.
     * @param   mixed   $value  Value.
     *
     * @return  $this
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setArgument($name, $value)
    {
        // B/C check for numeric access to named argument, eg $event->getArgument('0').
        if (is_numeric($name)) {
            if (key($this->arguments) != 0) {
                $argNames = \array_keys($this->arguments);
                $name     = $argNames[$name] ?? '';
            }

            @trigger_error(
                sprintf(
                    'Numeric access to named event arguments is deprecated, and will not work in Joomla 6. Event %s argument %s',
                    \get_class($this),
                    $name
                ),
                E_USER_DEPRECATED
            );
        }

        return parent::setArgument($name, $value);
    }
}
