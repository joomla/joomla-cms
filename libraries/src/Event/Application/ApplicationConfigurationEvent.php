<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Application;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Application Configuration events
 *
 * @since  5.0.0
 */
abstract class ApplicationConfigurationEvent extends AbstractImmutableEvent
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
        parent::__construct($name, $arguments);

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   Registry  $value  The value to set
     *
     * @return  Registry
     *
     * @since  5.0.0
     */
    protected function onSetSubject(Registry $value): Registry
    {
        return $value;
    }

    /**
     * Get the configuration object
     *
     * @return  Registry
     *
     * @since  5.0.0
     */
    public function getConfiguration(): Registry
    {
        return $this->arguments['subject'];
    }
}
