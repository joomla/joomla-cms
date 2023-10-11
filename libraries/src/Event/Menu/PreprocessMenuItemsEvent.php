<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Menu;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for menu events
 *
 * @since  5.0.0
 */
class PreprocessMenuItemsEvent extends AbstractImmutableEvent
{
    use ReshapeArgumentsAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'params', 'enabled'];

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
        // Reshape the arguments array to preserve b/c with legacy listeners
        if ($this->legacyArgumentsOrder) {
            parent::__construct($name, $this->reshapeArguments($arguments, $this->legacyArgumentsOrder));
        } else {
            parent::__construct($name, $arguments);
        }

        if (!\array_key_exists('context', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'context' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the content is referenced
        // @todo: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['subject'] = &$arguments[1];
        } elseif (\array_key_exists('subject', $arguments)) {
            $this->arguments['subject'] = &$arguments['subject'];
        }
    }

    /**
     * Setter for the context argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetContext(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the subject argument.
     *
     * @param   MenuItem[]  $value  The value to set
     *
     * @return  MenuItem[]
     *
     * @since  5.0.0
     */
    protected function onSetSubject(array $value): array
    {
        // Filter out MenuItem elements. Non empty result means invalid data
        $valid = !array_filter($value, function ($item) {
            return !$item instanceof MenuItem;
        });

        if (!$valid) {
            throw new \UnexpectedValueException("Argument 'subject' of event {$this->name} is not of the expected type");
        }

        return $value;
    }

    /**
     * Setter for the registry argument.
     *
     * @param   ?Registry  $value  The value to set
     *
     * @return  ?Registry
     *
     * @since  5.0.0
     */
    protected function onSetParams(?Registry $value): ?Registry
    {
        return $value;
    }

    /**
     * Setter for the enabled argument.
     *
     * @param   ?bool  $value  The value to set
     *
     * @return  ?bool
     *
     * @since  5.0.0
     */
    protected function onSetEnabled(?bool $value): ?bool
    {
        return $value;
    }

    /**
     * Getter for the context.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getContext(): string
    {
        return $this->arguments['context'];
    }

    /**
     * Getter for the items.
     *
     * @return  MenuItem[]
     *
     * @since  5.0.0
     */
    public function getItems(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the params.
     *
     * @return  ?Registry
     *
     * @since  5.0.0
     */
    public function getParams(): ?Registry
    {
        return $this->arguments['params'] ?? null;
    }

    /**
     * Getter for the enabled.
     *
     * @return  ?bool
     *
     * @since  5.0.0
     */
    public function getEnabled(): ?bool
    {
        return $this->arguments['enabled'] ?? null;
    }

    /**
     * Update the items.
     *
     * @param   MenuItem[]  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateItems(array $value): static
    {
        $this->arguments['subject'] = $this->onSetSubject($value);

        return $this;
    }
}
