<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin\System\Schemaorg;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for SchemaPrepareFormEvent event
 * Example:
 *  new PrepareFormEvent('onSchemaPrepareForm', ['subject' => $form]);
 *
 * @since  5.0.0
 */
class PrepareFormEvent extends AbstractImmutableEvent
{
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
        if (!\array_key_exists('subject', $arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
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
     * @since   5.0.0
     */
    protected function onSetSubject(Form $value): Form
    {
        return $value;
    }

    /**
     * Getter for the form argument.
     *
     * @return  Form
     *
     * @since   5.0.0
     */
    public function getForm(): Form
    {
        return $this->arguments['subject'];
    }
}
