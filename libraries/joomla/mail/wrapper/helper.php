<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Mail
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JMailHelper
 *
 * @package     Joomla.Platform
 * @subpackage  Mail
 * @since       3.4
 */
class JMailWrapperHelper
{
	/**
	 * Helper wrapper method for cleanLine
	 *
	 * @param   string  $value  String to be cleaned.
	 *
	 * @return  string  Cleaned string.
	 *
	 * @see     JMailHelper::cleanLine()
	 * @since   3.4
	 */
	public function cleanLine($value)
	{
		return JMailHelper::cleanLine($value);
	}

	/**
	 * Helper wrapper method for cleanText
	 *
	 * @param   string  $value  Multi-line string to be cleaned.
	 *
	 * @return  string  Cleaned multi-line string.
	 *
	 * @see     JMailHelper::cleanText()
	 * @since   3.4
	 */
	public function cleanText($value)
	{
		return JMailHelper::cleanText($value);
	}

	/**
	 * Helper wrapper method for cleanBody
	 *
	 * @param   string  $body  email body string.
	 *
	 * @return  string  Cleaned email body string.
	 *
	 * @see     JMailHelper::cleanBody()
	 * @since   3.4
	 */
	public function cleanBody($body)
	{
		return JMailHelper::cleanBody($body);
	}

	/**
	 * Helper wrapper method for cleanSubject
	 *
	 * @param   string  $subject  email subject string.
	 *
	 * @return  string  Cleaned email subject string.
	 *
	 * @see     JMailHelper::cleanSubject()
	 * @since   3.4
	 */
	public function cleanSubject($subject)
	{
		return JMailHelper::cleanSubject($subject);
	}

	/**
	 * Helper wrapper method for cleanAddress
	 *
	 * @param   string  $address  email address.
	 *
	 * @return  mixed   email address string or boolean false if injected headers are present
	 *
	 * @see     JMailHelper::cleanAddress()
	 * @since   3.4
	 */
	public function cleanAddress($address)
	{
		return JMailHelper::cleanAddress($address);
	}

	/**
	 * Helper wrapper method for isEmailAddress
	 *
	 * @param   string  $email  String to be verified.
	 *
	 * @return boolean  True if string has the correct format; false otherwise.
	 *
	 * @see     JMailHelper::isEmailAddress()
	 * @since   3.4
	 */
	public function isEmailAddress($email)
	{
		return JMailHelper::isEmailAddress($email);
	}
}
