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
 * Class for Module events
 *
 * @since  5.0.0
 */
abstract class RenderModuleEvent extends ModuleEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'attributes'];

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

        if (!\array_key_exists('attributes', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'attributes' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the content is referenced
        // @todo: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['attributes'] = &$arguments[1];
        } elseif (\array_key_exists('attributes', $arguments)) {
            $this->arguments['attributes'] = &$arguments['attributes'];
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  5.0.0
     */
    protected function onSetSubject(object $value): object
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
     * Getter for the subject argument.
     *
     * @return  object
     *
     * @since  5.0.0
     */
    public function getModule(): object
    {
        return $this->arguments['subject'];
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

    /**
     * Update the attributes.
     *
     * @param   array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateAttributes(array $value): static
    {
        $this->arguments['attributes'] = $this->onSetAttributes($value);

        return $this;
    }
}
