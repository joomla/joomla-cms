<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('_JEXEC') or die;

/**
 * Interface defining a mail message
 *
 * @since  __DEPLOY_VERSION__
 */
interface MailMessageInterface
{
	/**
	 * Add a BCC address for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addBcc(string $email, string $name = '');

	/**
	 * Add a CC address for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addCc(string $email, string $name = '');

	/**
	 * Add a recipient for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addRecipient(string $email, string $name = '');

	/**
	 * Get the mail transport in use.
	 *
	 * @return  object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMailer();

	/**
	 * Add the message to the system's job queue.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\MailExceptionInterface
	 */
	public function queue(): bool;

	/**
	 * Send the message.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\MailExceptionInterface
	 */
	public function send(): bool;

	/**
	 * Set the body of the message.
	 *
	 * @param   string  $content  Body of the email.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setBody(string $content);

	/**
	 * Set the date of the message.
	 *
	 * @param   \DateTimeInterface  $date  The date for the email.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDate(\DateTimeInterface $date);

	/**
	 * Set the sender of the message.
	 *
	 * @param   string  $email  The email address of the sender.
	 * @param   string  $name   The name of the sender.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function setSender(string $email, string $name = '');

	/**
	 * Set the subject of the message.
	 *
	 * @param   string  $subject  Subject of the email.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSubject(string $subject);
}
