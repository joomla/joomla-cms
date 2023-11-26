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
abstract class ModuleListEvent extends ModuleEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['modules', 'subject'];

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
        $this->arguments['subject'] = $this->arguments['subject'] ?? new \stdClass();

        parent::__construct($name, $arguments);

        if (!\array_key_exists('modules', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'modules' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the content is referenced
        // @todo: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['modules'] = &$arguments[0];
        } elseif (\array_key_exists('modules', $arguments)) {
            $this->arguments['modules'] = &$arguments['modules'];
        }
    }

    /**
     * Setter for the modules argument.
     *
     * @param   object[]  $value  The value to set
     *
     * @return  object[]
     *
     * @since  5.0.0
     */
    protected function onSetModules(array $value): array
    {
        // Filter out Module elements. Non empty result means invalid data
        $valid = !array_filter($value, function ($item) {
            return !\is_object($item);
        });

        if (!$valid) {
            throw new \UnexpectedValueException("Argument 'modules' of event {$this->name} is not of the expected type");
        }

        return $value;
    }

    /**
     * Getter for the subject argument.
     *
     * @return  object[]
     *
     * @since  5.0.0
     */
    public function getModules(): array
    {
        return $this->arguments['modules'];
    }

    /**
     * Update the modules.
     *
     * @param   object[]  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateModules(array $value): static
    {
        $this->arguments['modules'] = $this->onSetModules($value);

        return $this;
    }
}
