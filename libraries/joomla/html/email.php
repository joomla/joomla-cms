<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for cloaking email addresses
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JHtmlEmail
{
	/**
	 * Simple Javascript email Cloaker
	 *
	 * By default replaces an email with a mailto link with email cloaked
	 *
	 * @param   string   $mail    The -mail address to cloak.
	 * @param   boolean  $mailto  True if text and mailing address differ
	 * @param   string   $text    Text for the link
	 * @param   boolean  $email   True if text is an e-mail address
	 *
	 * @return  string  The cloaked email.
	 *
	 * @since   11.1
	 */
	public static function cloak($mail, $mailto = true, $text = '', $email = true)
	{
		// Convert text
		$mail = self::_convertEncoding($mail);

		// Split email by @ symbol
		$mail = explode('@', $mail);
		$mail_parts = explode('.', $mail[1]);

		// Random number
		$rand = rand(1, 100000);

		$replacement = "\n <script type='text/javascript'>";
		$replacement .= "\n <!--";
		$replacement .= "\n var prefix = '&#109;a' + 'i&#108;' + '&#116;o';";
		$replacement .= "\n var path = 'hr' + 'ef' + '=';";
		$replacement .= "\n var addy" . $rand . " = '" . @$mail[0] . "' + '&#64;';";
		$replacement .= "\n addy" . $rand . " = addy" . $rand . " + '" . implode("' + '&#46;' + '", $mail_parts) . "';";

		if ($mailto)
		{
			// Special handling when mail text is different from mail address
			if ($text)
			{
				if ($email)
				{
					// Convert text
					$text = self::_convertEncoding($text);

					// Split email by @ symbol
					$text = explode('@', $text);
					$text_parts = explode('.', $text[1]);
					$replacement .= "\n var addy_text" . $rand . " = '" . @$text[0] . "' + '&#64;' + '" . implode("' + '&#46;' + '", @$text_parts)
						. "';";
				}
				else
				{
					$replacement .= "\n var addy_text" . $rand . " = '" . $text . "';";
				}
				$replacement .= "\n document.write('<a ' + path + '\'' + prefix + ':' + addy" . $rand . " + '\'>');";
				$replacement .= "\n document.write(addy_text" . $rand . ");";
				$replacement .= "\n document.write('<\/a>');";
			}
			else
			{
				$replacement .= "\n document.write('<a ' + path + '\'' + prefix + ':' + addy" . $rand . " + '\'>');";
				$replacement .= "\n document.write(addy" . $rand . ");";
				$replacement .= "\n document.write('<\/a>');";
			}
		}
		else
		{
			$replacement .= "\n document.write(addy" . $rand . ");";
		}
		$replacement .= "\n //-->";
		$replacement .= '\n </script>';

		// XHTML compliance no Javascript text handling
		$replacement .= "<script type='text/javascript'>";
		$replacement .= "\n <!--";
		$replacement .= "\n document.write('<span style=\'display: none;\'>');";
		$replacement .= "\n //-->";
		$replacement .= "\n </script>";
		$replacement .= JText::_('JLIB_HTML_CLOAKING');
		$replacement .= "\n <script type='text/javascript'>";
		$replacement .= "\n <!--";
		$replacement .= "\n document.write('</');";
		$replacement .= "\n document.write('span>');";
		$replacement .= "\n //-->";
		$replacement .= "\n </script>";

		return $replacement;
	}

	/**
	 * Convert encoded text
	 *
	 * @param   string  $text  Text to convert
	 *
	 * @return  string  The converted text.
	 *
	 * @since   11.1
	 */
	protected static function _convertEncoding($text)
	{
		// Replace vowels with character encoding
		$text = str_replace('a', '&#97;', $text);
		$text = str_replace('e', '&#101;', $text);
		$text = str_replace('i', '&#105;', $text);
		$text = str_replace('o', '&#111;', $text);
		$text = str_replace('u', '&#117;', $text);

		return $text;
	}
}
