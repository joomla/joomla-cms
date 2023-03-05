<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Editor;

use BadMethodCallException;
use Joomla\CMS\Editor\Button\ButtonsRegistryInterface;
use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Editor setup event
 *
 * @since   __DEPLOY_VERSION__
 */
final class EditorButtonsSetupEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('subject', $arguments)) {
            throw new BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('editorType', $arguments)) {
            throw new BadMethodCallException("Argument 'editorType' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument
     *
     * @param   ButtonsRegistryInterface  $value  The value to set
     *
     * @return  ButtonsRegistryInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject(ButtonsRegistryInterface $value)
    {
        return $value;
    }

    /**
     * Setter for the Editor ID argument
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setEditorId(string $value)
    {
        return $value;
    }

    /**
     * Setter for the Editor Type argument
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setEditorType(string $value)
    {
        return $value;
    }

    /**
     * Setter for the asset argument
     *
     * @param   int  $value  The value to set
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setAsset(int $value)
    {
        return $value;
    }

    /**
     * Setter for the author argument
     *
     * @param   int  $value  The value to set
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setAuthor(int $value)
    {
        return $value;
    }
}
