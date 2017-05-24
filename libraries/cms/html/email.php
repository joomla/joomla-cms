<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for cloaking email addresses
 *
 * @since  1.5
 */
abstract class JHtmlEmail
{
	/**
	 * Simple JavaScript email cloaker
	 *
	 * By default replaces an email with a mailto link with email cloaked
	 *
	 * @param   string   $mail    The -mail address to cloak.
	 * @param   boolean  $mailto  True if text and mailing address differ
	 * @param   string   $text    Text for the link
	 * @param   boolean  $email   True if text is an email address
	 *
	 * @return  string  The cloaked email.
	 *
	 * @since   1.5
	 */
	public static function cloak($mail, $mailto = true, $text = '', $email = true)
	{
		// Handle IDN addresses: punycode for href but utf-8 for text displayed.
		if ($mailto && (empty($text) || $email))
		{
			// Use dedicated $text whereas $mail is used as href and must be punycoded.
			$text = JStringPunycode::emailToUTF8($text ? $text : $mail);
		}
		elseif (!$mailto)
		{
			// In that case we don't use link - so convert $mail back to utf-8.
			$mail = JStringPunycode::emailToUTF8($mail);
		}

		// Convert mail
		$mail = static::convertEncoding($mail);

		// Random hash
		$rand = md5($mail . mt_rand(1, 100000));

		// Split email by @ symbol
		$mail       = explode('@', $mail);
		$mail_parts = explode('.', $mail[1]);

		if ($mailto)
		{
			// Special handling when mail text is different from mail address
			if ($text)
			{
				// Convert text - here is the right place
				$text = static::convertEncoding($text);

				if ($email)
				{
					// Split email by @ symbol
					$text = explode('@', $text);
					$text_parts = explode('.', $text[1]);
					$tmpScript = "var addy_text" . $rand . " = '" . @$text[0] . "' + '&#64;' + '" . implode("' + '&#46;' + '", @$text_parts)
						. "';";
				}
				else
				{
					$tmpScript = "var addy_text" . $rand . " = '" . $text . "';";
				}

				$tmpScript .= "document.getElementById('cloak" . $rand . "').innerHTML += '<a ' + path + '\'' + prefix + ':' + addy"
					. $rand . " + '\'>'+addy_text" . $rand . "+'<\/a>';";
			}
			else
			{
				$tmpScript = "document.getElementById('cloak" . $rand . "').innerHTML += '<a ' + path + '\'' + prefix + ':' + addy"
					. $rand . " + '\'>' +addy" . $rand . "+'<\/a>';";
			}
		}
		else
		{
			$tmpScript = "document.getElementById('cloak" . $rand . "').innerHTML += addy" . $rand . ";";
		}

		$script       = "
				document.getElementById('cloak" . $rand . "').innerHTML = '';
				var prefix = '&#109;a' + 'i&#108;' + '&#116;o';
				var path = 'hr' + 'ef' + '=';
				var addy" . $rand . " = '" . @$mail[0] . "' + '&#64;';
				addy" . $rand . " = addy" . $rand . " + '" . implode("' + '&#46;' + '", $mail_parts) . "';
				$tmpScript
		";

		// TODO: Use inline script for now
		$inlineScript = "<script type='text/javascript'>" . $script . "</script>";

		return '<span id="cloak' . $rand . '">' . JText::_('JLIB_HTML_CLOAKING') . '</span>' . $inlineScript;
	}

	/**
	 * Convert encoded text
	 *
	 * @param   string  $text  Text to convert
	 *
	 * @return  string  The converted text.
	 *
	 * @since   1.5
	 */
	protected static function convertEncoding($text)
	{
		$text = html_entity_decode($text);

		// Replace vowels with character encoding
		$text = str_replace('a', '&#97;', $text);
		$text = str_replace('e', '&#101;', $text);
		$text = str_replace('i', '&#105;', $text);
		$text = str_replace('o', '&#111;', $text);
		$text = str_replace('u', '&#117;', $text);
		$text = htmlentities($text, ENT_QUOTES, 'UTF-8', false);

		return $text;
	}
}
