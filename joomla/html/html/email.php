<?php
/**
 * @version		$Id: email.php 11236 2008-11-02 02:44:35Z ian $
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/**
 * Utility class for cloaking email adresses
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlEmail
{
	/**
	* Simple Javascript email Cloaker
	*
 	* By default replaces an email with a mailto link with email cloacked
	*/
	public static function cloak($mail, $mailto=1, $text='', $email=1)
	{
		// convert text
		$mail 			= JHtmlEmail::_convertEncoding($mail);
		// split email by @ symbol
		$mail			= explode('@', $mail);
		$mail_parts		= explode('.', $mail[1]);
		// random number
		$rand			= rand(1, 100000);

		$replacement 	= "\n <script language='JavaScript' type='text/javascript'>";
		$replacement 	.= "\n <!--";
		$replacement 	.= "\n var prefix = '&#109;a' + 'i&#108;' + '&#116;o';";
		$replacement 	.= "\n var path = 'hr' + 'ef' + '=';";
		$replacement 	.= "\n var addy". $rand ." = '". @$mail[0] ."' + '&#64;';";
		$replacement 	.= "\n addy". $rand ." = addy". $rand ." + '". implode("' + '&#46;' + '", $mail_parts) ."';";

		if ($mailto) {
			// special handling when mail text is different from mail addy
			if ($text) {
				if ($email) {
					// convert text
					$text 			= JHtmlEmail::_convertEncoding($text);
					// split email by @ symbol
					$text 			= explode('@', $text);
					$text_parts		= explode('.', $text[1]);
					$replacement 	.= "\n var addy_text". $rand ." = '". @$text[0] ."' + '&#64;' + '". implode("' + '&#46;' + '", @$text_parts) ."';";
				} else {
					$replacement 	.= "\n var addy_text". $rand ." = '". $text ."';";
				}
				$replacement 	.= "\n document.write('<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>');";
				$replacement 	.= "\n document.write(addy_text". $rand .");";
				$replacement 	.= "\n document.write('<\/a>');";
			} else {
				$replacement 	.= "\n document.write('<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>');";
				$replacement 	.= "\n document.write(addy". $rand .");";
				$replacement 	.= "\n document.write('<\/a>');";
			}
		} else {
			$replacement 	.= "\n document.write(addy". $rand .");";
		}
		$replacement 	.= "\n //-->";
		$replacement 	.= '\n </script>';

		// XHTML compliance `No Javascript` text handling
		$replacement 	.= "<script language='JavaScript' type='text/javascript'>";
		$replacement 	.= "\n <!--";
		$replacement 	.= "\n document.write('<span style=\'display: none;\'>');";
		$replacement 	.= "\n //-->";
		$replacement 	.= "\n </script>";
		$replacement 	.= JText::_('CLOAKING');
		$replacement 	.= "\n <script language='JavaScript' type='text/javascript'>";
		$replacement 	.= "\n <!--";
		$replacement 	.= "\n document.write('</');";
		$replacement 	.= "\n document.write('span>');";
		$replacement 	.= "\n //-->";
		$replacement 	.= "\n </script>";

		return $replacement;
	}

	protected static function _convertEncoding($text)
	{
		// replace vowels with character encoding
		$text 	= str_replace('a', '&#97;', $text);
		$text 	= str_replace('e', '&#101;', $text);
		$text 	= str_replace('i', '&#105;', $text);
		$text 	= str_replace('o', '&#111;', $text);
		$text	= str_replace('u', '&#117;', $text);

		return $text;
	}
}

