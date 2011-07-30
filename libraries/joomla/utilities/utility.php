<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JUtility is a utility functions class
 *
 * @package     Joomla.Platform
 * @subpackage  Utilities
 * @since       11.1
 */
class JUtility
{
	/**
	 * Mail function (uses phpMailer)
	 *
	 * @param   string   $from         From email address
	 * @param   string   $fromname     From name
	 * @param   mixed    $recipient    Recipient email address(es)
	 * @param   string   $subject      Email subject
	 * @param   string   $body         Message body
	 * @param   boolean  $mode         False = plain text, true = HTML
	 * @param   mixed    $cc           CC email address(es)
	 * @param   mixed    $bcc          BCC email address(es)
	 * @param   mixed    $attachment   Attachment file name(s)
	 * @param   mixed    $replyto      Reply to email address(es)
	 * @param   mixed    $replytoname  Reply to name(s)
	 *
	 * @return  boolean  True on success
	 *
	 * @deprecated  12.1
	 * @see     JMail::sendMail()
	 * @since   11.1
	 */
	public static function sendMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null)
	{
		// Get a JMail instance
		$mail = JFactory::getMailer();

		return $mail->sendMail(
			$from, $fromname, $recipient, $subject, $body, $mode, $cc,
			$bcc, $attachment, $replyto, $replytoname
		);
	}

	/**
	 * Sends mail to administrator for approval of a user submission
	 *
	 * @param   string  $adminName   Name of administrator
	 * @param   string  $adminEmail  Email address of administrator
	 * @param   string  $email       [NOT USED]
	 * @param   string  $type        Type of item to approve
	 * @param   string  $title       Title of item to approve
	 * @param   string  $author      Author of item to approve
	 * @param   string  $url         url
	 *
	 * @return  boolean  True on success
	 *
	 * @deprecated  12.1
	 * @see     JMail::sendAdminMail()
	 * @since   11.1
	 */
	public static function sendAdminMail($adminName, $adminEmail, $email, $type, $title, $author, $url = null)
	{
		// Get a JMail instance
		$mail = JFactory::getMailer();

		return $mail->sendAdminMail(
			$adminName, $adminEmail, $email, $type, $title, $author, $url
		);
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param   string  $seed  Seed string.
	 *
	 * @return  string
	 *
	 * @deprecated  12.1
	 * @see     JApplication:getHash()
	 * @since   11.1
	 */
	public static function getHash($seed)
	{
		$conf = JFactory::getConfig();

		return md5($conf->get('secret').$seed);
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
	 * @param   boolean  $forceNew  Force creation of a new token.
	 *
	 * @return  string   Hashed var name
	 *
	 * @deprecated  12.1
	 * @see     JApplication:getHash()
	 * @since   11.1
	 */
	public static function getToken($forceNew = false)
	{
		$session = JFactory::getSession();

		return $session->getFormToken($forceNew);
	}

	/**
	 * Method to extract key/value pairs out of a string with XML style attributes
	 *
	 * @param   string  $string  String containing XML style attributes
	 *
	 * @return  array  Key/Value pairs for the attributes
	 *
	 * @since   11.1
	 */
	public static function parseAttributes($string)
	{
		// Initialise variables.
		$attr		= array();
		$retarray	= array();

		// Let's grab all the key/value pairs using a regular expression
		preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

		if (is_array($attr)) {
			$numPairs = count($attr[1]);
			for ($i = 0; $i < $numPairs; $i++)
			{
				$retarray[$attr[1][$i]] = $attr[2][$i];
			}
		}

		return $retarray;
	}

	/**
	 * Method to determine if the host OS is Windows.
	 *
	 * @return  boolean  True if Windows OS.
	 *
	 * @deprecated  12.1
	 * @see     JApplication::isWinOS()
	 * @since   11.1
	 */
	public static function isWinOS()
	{
		$application = JFactory::getApplication();

		return $application->isWinOS();
	}

	/**
	 * Method to dump the structure of a variable for debugging purposes
	 *
	 * @param   mixed    &$var      A variable
	 * @param   boolean  $htmlSafe  True to ensure all characters are htmlsafe
	 *
	 * @return  string
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public static function dump(&$var, $htmlSafe = true)
	{
		$result = var_export($var, true);

		return '<pre>'.($htmlSafe ? htmlspecialchars($result, ENT_COMPAT, 'UTF-8') : $result).'</pre>';
	}

	/**
	 * Prepend a reference to an element to the beginning of an array.
	 * Renumbers numeric keys, so $value is always inserted to $array[0]
	 *
	 * @param   array  &$array  Array to be modified
	 * @param   mixed  &$value  Value to add
	 *
	 * @return  integer
	 *
	 * @deprecated  12.1
	 * @see     http://www.php.net/manual/en/function.array-unshift.php#40270
	 * @since   11.1
	 */
	function array_unshift_ref(&$array, &$value)
	{
		$return = array_unshift($array, '');
		$array[0] = &$value;

		return $return;
	}

	/**
	 * Return the byte value of a particular string
	 *
	 * @param   string  $val  String optionally with G, M or K suffix
	 *
	 * @return  integer  size in bytes
	 *
	 * @deprecated  12.1
	 * @see     JHtmlNumber::bytes
	 * @since   11.1
	 */
	function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});

		switch($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
}
