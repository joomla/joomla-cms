<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('idna_convert', JPATH_LIBRARIES . '/idna_convert/idna_convert.class.php');

/**
 * Wrapper class for JStringPunycode
 *
 * @package     Joomla.Platform
 * @subpackage  String
 * @since       3.4
 * @deprecated  4.0 Will be removed without replacement
 */
class JStringWrapperPunycode
{
	/**
	 * Helper wrapper method for toPunycode
	 *
	 * @param   string  $utfString  The UTF-8 string to transform.
	 *
	 * @return string  The punycode string.
	 *
	 * @see     JUserHelper::toPunycode()
	 * @since   3.4
	 */
	public function toPunycode($utfString)
	{
		return JStringPunycode::toPunycode($utfString);
	}

	/**
	 * Helper wrapper method for fromPunycode
	 *
	 * @param   string  $punycodeString  The Punycode string to transform.
	 *
	 * @return string  The UF-8 URL.
	 *
	 * @see     JUserHelper::fromPunycode()
	 * @since   3.4
	 */
	public function fromPunycode($punycodeString)
	{
		return JStringPunycode::fromPunycode($punycodeString);
	}

	/**
	 * Helper wrapper method for urlToPunycode
	 *
	 * @param   string  $uri  The UTF-8 URL to transform.
	 *
	 * @return string  The punycode URL.
	 *
	 * @see     JUserHelper::urlToPunycode()
	 * @since   3.4
	 */
	public function urlToPunycode($uri)
	{
		return JStringPunycode::urlToPunycode($uri);
	}

	/**
	 * Helper wrapper method for urlToUTF8
	 *
	 * @param   string  $uri  The Punycode URL to transform.
	 *
	 * @return string  The UTF-8 URL.
	 *
	 * @see     JStringPunycode::urlToUTF8()
	 * @since   3.4
	 */
	public function urlToUTF8($uri)
	{
		return JStringPunycode::urlToUTF8($uri);
	}

	/**
	 * Helper wrapper method for emailToPunycode
	 *
	 * @param   string  $email  The UTF-8 email to transform.
	 *
	 * @return string  The punycode email.
	 *
	 * @see     JStringPunycode::emailToPunycode()
	 * @since   3.4
	 */
	public function emailToPunycode($email)
	{
		return JStringPunycode::emailToPunycode($email);
	}

	/**
	 * Helper wrapper method for emailToUTF8
	 *
	 * @param   string  $email  The punycode email to transform.
	 *
	 * @return string  The punycode email.
	 *
	 * @see     JStringPunycode::emailToUTF8()
	 * @since   3.4
	 */
	public function emailToUTF8($email)
	{
		return JStringPunycode::emailToUTF8($email);
	}
}
