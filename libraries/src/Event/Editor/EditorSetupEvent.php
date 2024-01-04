<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Editor;

use Joomla\CMS\Editor\EditorsRegistryInterface;
use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Editor setup event
 *
 * @since   5.0.0
 */
final class EditorSetupEvent extends AbstractImmutableEvent
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
     * @param   EditorsRegistryInterface  $value  The value to set
     *
     * @return  EditorsRegistryInterface
     *
     * @since  5.0.0
     */
    protected function onSetSubject(EditorsRegistryInterface $value): EditorsRegistryInterface
    {
        return $value;
    }

    /**
     * Returns Editors Registry instance.
     *
     * @return  EditorsRegistryInterface
     *
     * @since  5.0.0
     */
    public function getEditorsRegistry(): EditorsRegistryInterface
    {
        return $this->getArgument('subject');
    }
}
