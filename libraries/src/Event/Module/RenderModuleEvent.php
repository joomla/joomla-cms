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
 * @since  __DEPLOY_VERSION__
 */
abstract class RenderModuleEvent extends ModuleEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('attributes', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'attributes' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject(object $value): object
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
     * @since  __DEPLOY_VERSION__
     */
    protected function setAttributes(array|\ArrayAccess $value): array|\ArrayAccess
    {
        return $value;
    }

    /**
     * Getter for the subject argument.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getModule(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the attributes argument.
     *
     * @return  array|\ArrayAccess
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getAttributes(): array|\ArrayAccess
    {
        return $this->arguments['attributes'];
    }
}
