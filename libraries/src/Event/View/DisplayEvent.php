<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\View;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\MVC\View\ViewInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for WebAsset events
 *
 * @since  4.0.0
 */
class DisplayEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   4.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        if (!isset($arguments['subject'])) {
            throw new \BadMethodCallException("Argument 'subject' of event {$this->name} is required but has not been provided");
        }

        if (!($arguments['subject'] instanceof ViewInterface)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$this->name} is not of type 'ViewInterface'");
        }

        if (!isset($arguments['extension'])) {
            throw new \BadMethodCallException("Argument 'extension' of event {$this->name} is required but has not been provided");
        }

        if (!isset($arguments['extension']) || !\is_string($arguments['extension'])) {
            throw new \BadMethodCallException("Argument 'extension' of event {$this->name} is not of type 'string'");
        }

        if (strpos($arguments['extension'], '.') === false) {
            throw new \BadMethodCallException("Argument 'extension' of event {$this->name} has wrong format. Valid format: 'component.section'");
        }

        if (!\array_key_exists('extensionName', $arguments) || !\array_key_exists('section', $arguments)) {
            $parts = explode('.', $arguments['extension']);

            $arguments['extensionName'] = $arguments['extensionName'] ?? $parts[0];
            $arguments['section']       = $arguments['section'] ?? $parts[1];
        }

        parent::__construct($name, $arguments);
    }
}
