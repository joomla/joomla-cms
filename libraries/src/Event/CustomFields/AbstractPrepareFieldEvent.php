<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\CustomFields;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for CustomFields events
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractPrepareFieldEvent extends CustomFieldsEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'item', 'subject'];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('context', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'context' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('item', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'item' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the context argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setContext(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the item argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setItem(object $value): object
    {
        return $value;
    }

    /**
     * Getter for the field.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getField(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the context.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getContext(): string
    {
        return $this->arguments['context'];
    }

    /**
     * Getter for the item.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getItem(): object
    {
        return $this->arguments['item'];
    }
}
