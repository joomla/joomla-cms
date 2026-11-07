<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Mail;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event fired before the list of insertable tags of a mail template is rendered.
 * Example:
 *  new BeforeTagsRenderingEvent('onEventName', ['templateId' => $mail->template_id, 'subject' => $mail]);
 *
 * The subject is the mail template row being edited, not a MailTemplate. Listeners amend it via updateMail().
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeTagsRenderingEvent extends AbstractImmutableEvent
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
        parent::__construct($name, $arguments);

        if (!\array_key_exists('templateId', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'templateId' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the templateId argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetTemplateId(string $value): string
    {
        return $value;
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
    protected function onSetSubject(object $value): object
    {
        return $value;
    }

    /**
     * Getter for the mail template id.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTemplateId(): string
    {
        return $this->arguments['templateId'];
    }

    /**
     * Getter for the mail template row.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getMail(): object
    {
        return $this->arguments['subject'];
    }

    /**
     * Update the mail template row.
     *
     * @param   object  $value  The value to set
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function updateMail(object $value): static
    {
        $this->arguments['subject'] = $this->onSetSubject($value);

        return $this;
    }
}
