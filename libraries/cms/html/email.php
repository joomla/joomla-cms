<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\HTML\HTMLHelper;

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
	 * @param   string   $mail            The -mail address to cloak.
	 * @param   boolean  $mailto          True if text and mailing address differ
	 * @param   string   $text            Text for the link
	 * @param   boolean  $email           True if text is an email address
	 * @param   string   $attribsBefore   Any attributes before the email address
	 * @param   string   $attribsAfter    Any attributes before the email address
	 *
	 * @return  string  The cloaked email.
	 *
	 * @since   1.5
	 */
	public static function cloak($mail, $mailto = true, $text = '', $email = true, $attribsBefore = '', $attribsAfter = '')
	{
		// Handle IDN addresses: punycode for href but utf-8 for text displayed.
		if ($mailto && (empty($text) || $email))
		{
			// Use dedicated $text whereas $mail is used as href and must be punycoded.
			$text = PunycodeHelper::emailToUTF8($text ?: $mail);
		}
		elseif (!$mailto)
		{
			// In that case we don't use link - so convert $mail back to utf-8.
			$mail = PunycodeHelper::emailToUTF8($mail);
		}

		// Random hash
		$rand = md5($mail . mt_rand(1, 100000));

		// Split email by @ symbol
		$mail   = explode('@', $mail);
		$name   = @$mail[0];
		$domain = @$mail[1];

		// Pass the parameters to javascript
		Factory::getDocument()->addScriptOptions('email-cloak', [
			$rand => [
				'linkable' => $mailto,
				'isEmail'  => $email,
				'properties' => [
					'name'   => $name,
					'domain' => $domain,
					'text'   => $text,
					'before' => $attribsBefore,
					'after'  => $attribsAfter
				]
			]
		]);

		HTMLHelper::_('behavior.core');

		// Include the email cloaking script
		HTMLHelper::_('script', 'system/email-cloak.js', ['version' => 'auto', 'relative' => true]);

		return '<span id="cloak-' . $rand . '">' . Text::_('JLIB_HTML_CLOAKING') . '</span>';
	}
}
