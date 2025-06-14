<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Application;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Application events
 *
 * @since  5.0.0
 */
abstract class ApplicationEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument.
     *
     * @param   AbstractApplication  $value  The value to set
     *
     * @return  AbstractApplication
     *
     * @since  5.0.0
     */
    final protected function onSetSubject(AbstractApplication $value): AbstractApplication
    {
        return $value;
    }

    /**
     * Get the event's application object
     *
     * @return  AbstractApplication
     *
     * @since  5.0.0
     */
    final public function getApplication(): AbstractApplication
    {
        return $this->getArgument('subject');
    }
}
