<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Mail;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
use Joomla\CMS\Mail\MailTemplate;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for MailTemplate events
 *
 * @since  5.2.0
 */
abstract class MailTemplateEvent extends AbstractImmutableEvent
{
    use ReshapeArgumentsAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.2.0
     * @deprecated 5.2.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = [];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.2.0
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

        if (!\array_key_exists('templateId', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'templateId' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Pre-Setter for the subject argument.
     *
     * @param   MailTemplate  $value  The value to set
     *
     * @return  MailTemplate
     *
     * @since  5.2.0
     */
    protected function onSetSubject(MailTemplate $value): MailTemplate
    {
        return $value;
    }

    /**
     * Pre-getter for the subject argument.
     *
     * @param   MailTemplate  $value  The value to set
     *
     * @return  MailTemplate
     *
     * @since  5.2.0
     */
    protected function onGetSubject(MailTemplate $value): MailTemplate
    {
        return $value;
    }

    /**
     * Pre-setter for the templateId argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.2.0
     */
    protected function onSetTemplateId(string $value): string
    {
        return $value;
    }

    /**
     * Pre-getter for the templateId argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.2.0
     */
    protected function onGetTemplateId(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the subject argument.
     *
     * @return  MailTemplate
     *
     * @since  5.2.0
     */
    public function getTemplate(): MailTemplate
    {
        return $this->getArgument('subject');
    }

    /**
     * Getter for the templateId argument.
     *
     * @return  string
     *
     * @since  5.2.0
     */
    public function getTemplateId(): string
    {
        return $this->getArgument('templateId');
    }
}
