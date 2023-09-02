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
        // TODO: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['subject'] = &$arguments[0];
        } elseif (\array_key_exists('subject', $arguments)) {
            $this->arguments['subject'] = &$arguments['subject'];
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  object
     *
     * @since  5.0.0
     */
    protected function setSubject(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the attributes argument.
     *
     * @param   array|\ArrayAccess  $value  The value to set
     *
     * @return  array|\ArrayAccess
     *
     * @since  5.0.0
     */
    protected function setAttributes(array|\ArrayAccess $value): array|\ArrayAccess
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
        return $this->arguments['subject'];
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
        $this->arguments['subject'] = $value;

        return $this;
    }

    /**
     * Getter for the attributes argument.
     *
     * @return  array|\ArrayAccess
     *
     * @since  5.0.0
     */
    public function getAttributes(): array|\ArrayAccess
    {
        return $this->arguments['attributes'];
    }
}
