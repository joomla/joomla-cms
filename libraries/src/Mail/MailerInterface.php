<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a common interface to send emails with.
 *
 * @since  4.4.0
 */
interface MailerInterface
{
    /**
     * Send the mail. Throws an exception when something goes wrong.
     *
     * @return  void
     *
     * @since   4.4.0
     *
     * @throws  \RuntimeException
     */
    public function send();

    /**
     * Set the email sender.
     *
     * @param   string  $fromEmail  The Email address of the sender
     * @param   string  $name       The name of the sender
     *
     * @return  void
     *
     * @since   4.4.0
     *
     * @throws  \UnexpectedValueException  if the sender is not a valid address
     */
    public function setSender(string $fromEmail, string $name = '');

    /**
     * Set the email subject.
     *
     * @param   string  $subject  Subject of the email
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function setSubject(string $subject);

    /**
     * Set the email body.
     *
     * @param   string  $content  Body of the email
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function setBody(string $content);

    /**
     * Add a recipient to the email.
     *
     * @param   string  $recipientEmail  The email of the recipient
     * @param   string  $name            The name of the recipient
     *
     * @return  void
     *
     * @since   4.4.0
     *
     * @throws  \UnexpectedValueException  if the recipient is not a valid address
     */
    public function addRecipient(string $recipientEmail, string $name = '');

    /**
     * Add a carbon copy recipient to the email.
     *
     * @param   string  $ccEmail  The email of the CC recipient
     * @param   string  $name     The name of the CC recipient
     *
     * @return  void
     *
     * @since   4.4.0
     *
     * @throws  \UnexpectedValueException  if the CC is not a valid address
     */
    public function addCc(string $ccEmail, string $name = '');

    /**
     * Add a blind carbon copy recipient to the email.
     *
     * @param   string  $bccEmail  The email of the BCC recipient
     * @param   string  $name      The name of the BCC recipient
     *
     * @return  void
     *
     * @since   4.4.0
     *
     * @throws  \UnexpectedValueException  if the BCC is not a valid address
     */
    public function addBcc(string $bccEmail, string $name = '');

    /**
     * Add file attachment to the email.
     *
     * @param   string  $data         The data of the attachment
     * @param   string  $name         The name of the attachment
     * @param   string  $encoding     The encoding of the attachment
     * @param   string  $type         The mime type of the attachment
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function addAttachment(string $data, string $name = '', string $encoding = 'base64', string $type = 'application/octet-stream');

    /**
     * Add Reply to email address to the email
     *
     * @param   string  $replyToEmail  The email of the reply address
     * @param   string  $name          The name of the reply address
     *
     * @return  void
     *
     * @since   4.4.0
     *
     * @throws  \UnexpectedValueException  if the replay to is not a valid address
     */
    public function addReplyTo(string $replyToEmail, string $name = '');
}
