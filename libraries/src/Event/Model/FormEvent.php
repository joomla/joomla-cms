<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model Form event
 *
 * @since  5.0.0
 */
abstract class FormEvent extends AbstractImmutableEvent
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
    protected $legacyArgumentsOrder = ['subject', 'data'];

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
            $arguments = $this->reshapeArguments($arguments, $this->legacyArgumentsOrder);
        }

        parent::__construct($name, $arguments);

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('data', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'data' of event {$name} is required but has not been provided");
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
     * Getter for the context argument.
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
     * Setter for the subject argument.
     *
     * @param   Form  $value  The value to set
     *
     * @return  Form
     *
     * @since  5.0.0
     */
    protected function onSetSubject(Form $value): Form
    {
        return $value;
    }

    /**
     * Setter for the data argument.
     *
     * @param   object|array  $value  The value to set
     *
     * @return  object|array
     *
     * @since  5.0.0
     */
    protected function onSetData(object|array $value): object|array
    {
        return $value;
    }

    /**
     * Getter for the form.
     *
     * @return  Form
     *
     * @since  5.0.0
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
     * @since  5.0.0
     */
    public function getData(): object|array
    {
        return $this->arguments['data'];
    }
}
