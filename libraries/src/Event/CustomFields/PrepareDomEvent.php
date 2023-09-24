<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\CustomFields;

use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for CustomFields events
 *
 * @since  5.0.0
 */
class PrepareDomEvent extends CustomFieldsEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'fieldset', 'form'];

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

        if (!\array_key_exists('fieldset', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'fieldset' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('form', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'form' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the fieldset argument.
     *
     * @param   \DOMElement  $value  The value to set
     *
     * @return  \DOMElement
     *
     * @since  5.0.0
     */
    protected function onSetFieldset(\DOMElement $value): \DOMElement
    {
        return $value;
    }

    /**
     * Setter for the form argument.
     *
     * @param   Form  $value  The value to set
     *
     * @return  Form
     *
     * @since  5.0.0
     */
    protected function onSetForm(Form $value): Form
    {
        return $value;
    }

    /**
     * Getter for the field.
     *
     * @return  object
     *
     * @since  5.0.0
     */
    public function getField(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the fieldset.
     *
     * @return  \DOMElement
     *
     * @since  5.0.0
     */
    public function getFieldset(): \DOMElement
    {
        return $this->arguments['fieldset'];
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
        return $this->arguments['form'];
    }
}
