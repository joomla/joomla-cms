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
 *  new AfterRenderModulesEvent('onEventName', ['subject' => $content, 'attributes' => $attrs]);
 *
 * @since  5.0.0
 */
class AfterRenderModulesEvent extends ModuleEvent
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
        // This event has a dummy subject for now
        $this->arguments['subject'] ??= new \stdClass();

        parent::__construct($name, $arguments);

        if (!\array_key_exists('content', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'content' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('attributes', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'attributes' of event {$name} is required but has not been provided");
        }

        if (key($arguments) === 0) {
            $this->arguments['content'] = $arguments[0];
        } elseif (\array_key_exists('content', $arguments)) {
            $this->arguments['content'] = $arguments['content'];
        }
    }

    /**
     * Setter for the content argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  object
     *
     * @since  5.0.0
     */
    protected function onSetContent(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the attributes argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetAttributes(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the content.
     *
     * @return  object
     *
     * @since  5.0.0
     */
    public function getContent(): string
    {
        return $this->arguments['content'];
    }

    /**
     * Setter for the content.
     *
     * @param   string  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateContent(string $value): static
    {
        $this->arguments['content'] = $this->onSetContent($value);

        return $this;
    }

    /**
     * Getter for the attributes argument.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getAttributes(): array
    {
        return $this->arguments['attributes'];
    }
}
