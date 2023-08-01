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
class AfterRenderModulesEvent extends ModuleEvent
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
     * @param   string  $value  The value to set
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @return  self
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setContent(string $value): self
    {
        $this->arguments['subject'] = $value;

        return $this;
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
