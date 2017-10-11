<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for MailHelper
 *
 * @package     Joomla.Platform
 * @subpackage  Mail
 * @since       3.4
 * @deprecated  4.0 Will be removed without replacement
 */
class MailWrapper
{
	/**
	 * Helper wrapper method for cleanLine
	 *
	 * @param   string  $value  String to be cleaned.
	 *
	 * @return  string  Cleaned string.
	 *
	 * @see     MailHelper::cleanLine()
	 * @since   3.4
	 */
	public function cleanLine($value)
	{
		return MailHelper::cleanLine($value);
	}

	/**
	 * Helper wrapper method for cleanText
	 *
	 * @param   string  $value  Multi-line string to be cleaned.
	 *
	 * @return  string  Cleaned multi-line string.
	 *
	 * @see     MailHelper::cleanText()
	 * @since   3.4
	 */
	public function cleanText($value)
	{
		return MailHelper::cleanText($value);
	}

	/**
	 * Helper wrapper method for cleanBody
	 *
	 * @param   string  $body  email body string.
	 *
	 * @return  string  Cleaned email body string.
	 *
	 * @see     MailHelper::cleanBody()
	 * @since   3.4
	 */
	public function cleanBody($body)
	{
		return MailHelper::cleanBody($body);
	}

	/**
	 * Helper wrapper method for cleanSubject
	 *
	 * @param   string  $subject  email subject string.
	 *
	 * @return  string  Cleaned email subject string.
	 *
	 * @see     MailHelper::cleanSubject()
	 * @since   3.4
	 */
	public function cleanSubject($subject)
	{
		return MailHelper::cleanSubject($subject);
	}

	/**
	 * Helper wrapper method for cleanAddress
	 *
	 * @param   string  $address  email address.
	 *
	 * @return  mixed   email address string or boolean false if injected headers are present
	 *
	 * @see     MailHelper::cleanAddress()
	 * @since   3.4
	 */
	public function cleanAddress($address)
	{
		return MailHelper::cleanAddress($address);
	}

	/**
	 * Helper wrapper method for isEmailAddress
	 *
	 * @param   string  $email  String to be verified.
	 *
	 * @return boolean  True if string has the correct format; false otherwise.
	 *
	 * @see     MailHelper::isEmailAddress()
	 * @since   3.4
	 */
	public function isEmailAddress($email)
	{
		return MailHelper::isEmailAddress($email);
	}
}
