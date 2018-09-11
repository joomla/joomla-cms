<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Language\Text;

/**
 * Email Templating Class
 *
 * @since  4.0.0
 */
class MailTemplate
{
	/**
	 * Mailer object to send the actual mail.
	 *
	 * @var    Joomla\CMS\Mail\Mail
	 * @since  4.0.0
	 */
	protected $mailer;

	/**
	 * Identifier of the mail template.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $mail_id;

	/**
	 * Language of the mail template.
	 *
	 * @var    string
	 */
	protected $language;

	/**
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $data = array();

	/**
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $attachments = array();

	/**
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $recipients = array();

	/**
	 * Constructor for the mail templating class
	 * 
	 * @param   string  $mail_id   Id of the mail template.
	 * @param   string  $language  Language of the template to use.
	 * @param   Mail    $mailer    Mail object to send the mail with.
	 * 
	 * @since   4.0.0
	 */
	public function __construct($mail_id, $language, Mail $mailer = null)
	{
		$this->mail_id = $mail_id;
		$this->language = $language;

		if ($mailer)
		{
			$this->mailer = $mailer;
		}
		else
		{
			$this->mailer = Factory::getMailer();
		}
	}

	/**
	 * Add an attachment to the mail
	 * 
	 * @param   string  $name  Filename of the attachment
	 * @param   string  $file  Either a filepath or filecontent
	 * 
	 * @since   4.0.0
	 */
	public function addAttachment($name, $file)
	{
		$attachment = new \stdClass;
		$attachment->name = $name;
		$attachment->file = $file;
		$this->attachments[] = $attachment;
	}

	/**
	 * Adds recipients for this mail
	 * 
	 * @param   string  $mail  Mail address of the recipient
	 * @param   string  $name  Name of the recipient
	 * @param   string  $type  How should the recipient receive the mail? ('to', 'cc', 'bcc')
	 * 
	 * @since   4.0.0
	 */
	public function addRecipient($mail, $name, $type = 'to')
	{
		$recipient = new \stdClass;
		$recipient->mail = $mail;
		$recipient->name = $name;
		$recipient->type = $type;
		$this->recipients[] = $recipient;
	}

	/**
	 * Add data to replace in the template
	 * 
	 * @param   array  $data  Associative array of strings to replace
	 * 
	 * @since   4.0.0
	 */
	public function addTemplateData($data)
	{
		$this->data = array_merge($this->data, $data);
	}

	/**
	 * Render and send the mail
	 * 
	 * @return  boolean  True on success
	 * @since   4.0.0
	 */
	public function send()
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__mail_templates')
			->where('mail_id = '.$db->quote($this->mail_id))
			->where('language IN (\'\','.$db->quote($this->language) . ')')
			->order('language DESC');
		$db->setQuery($query);
		$mail = $db->loadObject();

		Factory::getApplication()->triggerEvent('onMailBeforeRendering', array($this->mail_id, &$this));

		$keys = array_keys($this->data);

		foreach ($keys as &$key)
		{
			$key = '{'.strtoupper($key).'}';
		}

		$mail->subject = str_replace($keys, array_values($this->data), Text::_($mail->subject));
		$this->mailer->setSubject($mail->subject);

		if ($mail->htmlbody != '')
		{
			$this->mailer->IsHTML(true);
			$mail->htmlbody = str_replace($keys, array_values($this->data), Text::_($mail->htmlbody));
			$this->mailer->setBody($mail->htmlbody);
		}
		else
		{
			$mail->body = str_replace($keys, array_values($this->data), Text::_($mail->body));
			$this->mailer->setBody($mail->body);
		}

		foreach ($this->recipients as $recipient)
		{
			switch ($recipient->type)
			{
				case 'cc':
					$this->mailer->addcc($recipient->mail, $recipient->name);
					break;
				case 'bcc':
					$this->mailer->addBcc($recipient->mail, $recipient->name);
					break;
				case 'to':
				default:
					$this->mailer->addAddress($recipient->mail, $recipient->name);
			}
		}

		$attachments = (array) json_decode($mail->attachments);
		$attachments = array_merge($attachments, $this->attachments);

		foreach ($attachments as $attachment)
		{
			if (is_file($attachment->file))
			{
				$this->mailer->addAttachment($attachment->file, $attachment->name);
			}
			else
			{
				$this->mailer->AddStringAttachment($attachment->file, $attachment->name);
			}
		}

		return $this->mailer->Send();
	}
}