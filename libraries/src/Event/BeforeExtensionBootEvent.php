<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use Joomla\DI\Container;

/**
 * Event class for representing the extensions's `onBeforeExtensionBoot` event
 *
 * @since  4.0.0
 */
class BeforeExtensionBootEvent extends AbstractImmutableEvent
{
    /**
     * Get the event's extension type. Can be:
     * - component
     *
     *
     * @since  4.0.0
     */
    public function getExtensionType(): string
    {
        return $this->getArgument('type');
    }

    /**
     * Get the event's extension name.
     *
     *
     * @since  4.0.0
     */
    public function getExtensionName(): string
    {
        return $this->arguments['extensionName'];
    }

    /**
     * Get the event's container object
     *
     *
     * @since  4.0.0
     */
    public function getContainer(): Container
    {
        return $this->arguments['container'];
    }
}
