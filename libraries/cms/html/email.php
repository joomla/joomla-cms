<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for cloaking email addresses
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
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
	 * @param   boolean  $email   True if text is an e-mail address
	 *
	 * @return  string  The cloaked email.
	 *
	 * @since   1.5
	 */
	public static function cloak($mail, $mailto = true, $text = '', $email = true, $pre = '', $post = '')
	{
		JHtml::_('jquery.framework');
		JHtml::script('system/emailcloak.js', false, true);
		JHtml::stylesheet('system/emailcloak.css', false, true);

		$id = 'e_' . substr(md5(rand()), 0, 8);

		$hide = '';
		if ($text && $text != $mail)
		{
			$hide = ' style="display:none;"';
			if ($email)
			{
				$parts = self::_createParts($text);
				$text = '<span data-content-post="' . $parts['5'] . '" data-content-pre="' . $parts['0'] . '">'
					. '<span data-content-post="' . $parts['4'] . '" data-content-pre="' . $parts['1'] . '">'
					. '<span data-content-post="' . $parts['3'] . '" data-content-pre="' . $parts['2'] . '">'
					. '</span></span></span>';
			}
		}
		elseif (!$mailto)
		{
			$parts = self::_createParts($mail);
			$text = '<span data-content-post="' . $parts['5'] . '" data-content-pre="' . $parts['0'] . '">'
				. '<span data-content-post="' . $parts['4'] . '" data-content-pre="' . $parts['1'] . '">'
				. '<span data-content-post="' . $parts['3'] . '" data-content-pre="' . $parts['2'] . '">'
				. '</span></span></span>';
		}
		else
		{
			$text = '';
		}

		if($mailto) {
			$parts = self::_createParts($mail);
			$spans = '<span class="cloaked_email" id="' . $id . '"' . $hide . '>'
				. '<span data-content-post="' . $parts['5'] . '" data-content-pre="' . $parts['0'] . '">'
				. '<span data-content-post="' . $parts['4'] . '" data-content-pre="' . $parts['1'] . '">'
				. '<span data-content-post="' . $parts['3'] . '" data-content-pre="' . $parts['2'] . '">'
				. '</span></span></span></span>'
				. $text;

				return '<a ' . $pre . 'class="email_address" href="javascript:// ' . htmlentities(JText::_('JLIB_HTML_CLOAKING'), ENT_COMPAT, 'UTF-8') . '"' . $post . '>'
				. $spans
				. '</a>';
		} else {
			return '<!--- ' . JText::_('JLIB_HTML_CLOAKING') . ' --->'
			.'<span class="email_address">'. $text . '</span>';
		}
	}

	/**
	 * Convert text to 6 encoded parts in an array.
	 *
	 * @param   string  $text  Text to convert.
	 *
	 * @return  array   The encoded parts.
	 *
	 * @since   3.2
	 */
	protected static function _createParts($str)
	{
		$str = mb_convert_encoding($str, 'UTF-32', 'UTF-8');
		$split = str_split($str, 4);
		$size = ceil(count($split) / 6);
		$parts = array('', '', '', '', '', '');
		foreach ($split as $i => $c)
		{
			$c = trim($c);
			$v = ($c == '@' || (strlen($c) === 1 && rand(0, 2))) ? '&#' . ord($c) . ';' : $c;
			$pos = floor($i / $size);
			$parts[$pos] .= $v;
		}
		return $parts;
	}
}
