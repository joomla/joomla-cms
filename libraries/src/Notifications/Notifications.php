<?php
/**
 * Joomla! Content Management System
 * 
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Joomla\CMS\Notifications;
use JModelLegacy;
use JModelList;

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_notifications/models', 'NotificationsModel');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_notifications/models', 'NotificationsModel');

/**
 * The Notifications class
 *
 * @since       4.0.0
 */
class Notifications
{
	/**
	 * The constructor
	 *
	 * @since  4.0.0
	 */
	public function __construct()
	{
		// Check for component
		if (!JComponentHelper::getComponent('com_notifications', true)->enabled)
		{
			throw new Exception('Notifications not installed');
		}
	}

	/**
	 * Method to send the form data.
	 *
	 * @param   string      $client        A requird field same as component name.
	 * @param   string      $key           Key is unique in client.
	 * @param   array       $recipients    It's an array of user objects
	 * @param   Object      $replacements  It is a object contains replacement.
	 * @param   JParameter  $options       It is a object contains Jparameters like cc,bcc.
	 *
	 * @return  boolean value.
	 *
	 * @since 1.0
	 */
	public static function send($client, $key, $recipients, $replacements, $options)
	{
		try
		{
			$model = JModelList::getInstance('Notifications', 'NotificationsModel', array('ignore_request' => true));

			$template = $model->getTemplate($client, $key);
			$addRecipients = self::getRecipients($client, $key, $recipients, $options);

			if (isset($addRecipients))
			{
				// Invoke JMail Class
				$mailer = \JFactory::getMailer();

				if ($options->get('from') != null && $options->get('fromname') != null)
				{
					$from = array($options->get('from'),$options->get('fromname'));
				}
				else
				{
					$config = \JFactory::getConfig();
					$from = array($config->get('mailfrom'), $config->get('fromname'));
				}

				// Set cc for email
				if ($options->get('cc') != null)
				{
					$mailer->addCC($options->get('cc'));
				}

				// Set bcc for email
				if ($options->get('bcc') != null)
				{
					$mailer->addBcc($options->get('bcc'));
				}

				// Set bcc for email
				if ($options->get('replyTo') != null)
				{
					$mailer->addReplyTo($options->get('replyTo'));
				}

				if ($options->get('attachment') != null)
				{
					if ($options->get('attachmentName') != null)
					{
						$mailer->addAttachment($options->get('attachment'), $options->get('attachmentName'));
					}
					else
					{
						$mailer->addAttachment($options->get('attachment'));
					}
				}

				// If you would like to send String Attachment in email
				if ($options->get('stringAttachment') != null)
				{
					$stringAttachment = array();
					$stringAttachment = $options->get('stringAttachment');
					$encoding         = isset($stringAttachment['encoding']) ? $stringAttachment['encoding'] : '';
					$type             = isset($stringAttachment['type']) ? $stringAttachment['type'] : '';

					if (isset($stringAttachment['content']) && isset($stringAttachment['name']))
					{
						$mailer->addStringAttachment(
										$stringAttachment['content'],
										$stringAttachment['name'],
										$encoding,
										$type
									);
					}
				}

				// If you would like to send as HTML, include this line; otherwise, leave it out
				if (($options->get('isNotHTML')) != 1)
				{
					$mailer->isHTML();
				}

				// Set sender array so that my name will show up neatly in your inbox
				$mailer->setSender($from);

				// Add a recipient -- this can be a single address (string) or an array of addresses
				$mailer->addRecipient($addRecipients);

				// Set subject for email
				$mailer->setSubject(self::getSubject($template->email_subject, $options));

				// Set body for email
				$mailer->setBody(self::getBody($template->email_body, $replacements));

				// Send once you have set all of your options
				if ($template->email_status == 1)
				{
					$status = $mailer->send();

					if ($status)
					{
						$return['success'] = 1;
						$return['message'] = \JText::_('LIB_TECHJOOMLA_NOTIFICATION_EMAIL_SEND_SUCCESSFULLY');

						return $return;
					}
					else
					{
						throw new Exception(\JText::_('LIB_TECHJOOMLA_NOTIFICATION_EMAIL_SEND_FAILED'));
					}
				}
			}
			else
			{
				throw new Exception(\JText::_('LIB_TECHJOOMLA_NOTIFICATION_ADD_RECIPIENTS_OR_CHECK_PREFERENCES'));
			}
		}
		catch (Exception $e)
		{
			$return['success'] = 0;
			$return['message'] = $e->getMessage();

			return $return;
		}
	}

	/**
	 * Method to get Recipients.
	 *
	 * @param   string      $client      A requird field same as component name.
	 * @param   string      $key         Key is unique in client.
	 * @param   array       $recipients  It's an array of user objects
	 * @param   JParameter  $options     It is a object contains Jparameters like cc,bcc.
	 *
	 * @return  array Reciepients.
	 *
	 * @since 4.0.0
	 */
	public static function getRecipients($client,$key,$recipients,$options)
	{
		$model = JModelList::getInstance('Preferences', 'NotificationsModel', array('ignore_request' => true));
		$unsubscribed_users = $model->getUnsubscribedUsers($client, $key);

		$addRecipients = array();

		if (!empty($recipients))
		{
			foreach ($recipients as $recipient)
			{
				/* $unsubscribed_users array is not empty.
				 * $recipient->id is not in $unsubscribed_users array.
				 * $recipient->block is empty or not set.
				*/
				if (!empty($unsubscribed_users) && !in_array($recipient->id, $unsubscribed_users) && !($recipient->block))
				{
					// Make an array of recipients.
					$addRecipients[] = $recipient->email;
				}
				/*$recipient->block is empty or not set.*/
				elseif ($recipient->block == 0 || !isset($recipient->block))
				{
					// Make an array of recipients.
					$addRecipients[] = $recipient->email;
				}
			}
		}

		if ($options->get('guestEmails') != null)
		{
			foreach ($options->get('guestEmails') as $guestEmail)
			{
				$addRecipients[] = $guestEmail;
			}
		}

		return $addRecipients;
	}

	/**
	 * Method to get Body.
	 *
	 * @param   string  $body_template  A template body for email.
	 * @param   array   $replacements   It is a object contains replacement.
	 *
	 * @return  string  $body
	 *
	 * @since 4.0.0
	 */
	public static function getBody($body_template, $replacements)
	{
		$matches = self::getTags($body_template);

		$replacamentTags = $matches[0];
		$tags = $matches[1];
		$index = 0;

		if (isset($replacements))
		{
			foreach ($replacamentTags as $ind => $replacamentTag)
			{
				// Explode e.g doner.name with "." so $data[0]=doner and $data[1]=name
				$data = explode(".", $tags[$ind]);

				if (isset($data))
				{
					$key = $data[0];
					$value = $data[1];

					if (!empty($replacements->$key->$value))
					{
						$replaceWith = $replacements->$key->$value;
					}
					else
					{
						$replaceWith = "";
					}

					if (isset ($replaceWith))
					{
						$body_template = str_replace($replacamentTag, $replaceWith, $body_template);
						$index++;
					}
				}
			}
		}

		return $body_template;
	}

	/**
	 * Method to get Subject.
	 *
	 * @param   string  $subject_template  A template body for email.
	 * @param   array   $options           It is a object contains replacement.
	 *
	 * @return  string  $subject
	 *
	 * @since 4.0.0
	 */
	public static function getSubject($subject_template,$options)
	{
			$matches = self::getTags($subject_template);
			$tags = $matches[0];
			$index = 0;

		foreach ($tags as $tag)
		{
			// Explode e.g doner.name with "." so $data[0]=doner and $data[1]=name
			$data = explode(".", $matches[1][$index]);
			$key = $data[0];
			$value = $data[1];
			$replaceWith = $options->get($key)->$value;
			$subject_template = str_replace($tag, $replaceWith, $subject_template);
			$index++;
		}

		return $subject_template;
	}

	/**
	 * Method to get Tags.
	 *
	 * @param   string  $data_template  A template.
	 *
	 * @return  array   $matches
	 *
	 * @since 4.0.0
	 */
	public static function getTags($data_template)
	{
		//  Pattern for {text};
			$pattern = "/{([^}]*)}/";

			preg_match_all($pattern, $data_template, $matches);

		//  $matches[0]= stores tag like {doner.name} and $matches[1] stores doner.name. Explode it and make it doner->name
			return $matches;
	}
}
