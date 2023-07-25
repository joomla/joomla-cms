<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Content;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Content Form event
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentPrepareFormEvent extends AbstractImmutableEvent
{
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
        // Reshape the arguments array to preserve b/c with legacy listeners
        // Check for non-associative list
        if (key($arguments) === 0) {
            $arguments = array_combine(['subject', 'data'], $arguments);
        } else {
            $arguments = array_replace(['subject' => null, 'data' => null], $arguments);
        }

        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('data', $arguments)) {
            throw new \BadMethodCallException("Argument 'data' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument.
     *
     * @param   Form  $value  The value to set
     *
     * @return  Form
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject(Form $value): Form
    {
        return $value;
    }

    /**
     * Getter for the form.
     *
     * @return  Form
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getForm(): Form
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the data.
     *
     * @return  object|array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getData()
    {
        return $this->arguments['data'];
    }
}
