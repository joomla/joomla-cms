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
	static $included_css = 0;
	static $included_js = 0;

	/**
	 * Simple JavaScript email cloaker
	 *
	 * By default replaces an email with a mailto link with email cloaked
	 *
	 * @param   string   $mail    The -mail address to cloak.
	 * @param   boolean  $mode    True if address should be displayed as link
	 * @param   string   $text    Text for the link
	 * @param   boolean  $email   True if text is an e-mail address
	 *
	 * @return  string  The cloaked email.
	 *
	 * @since   1.5
	 */
	public static function cloak($mail, $mode = true, $text = '', $email = true, $pre = '', $post = '')
	{
		if(!self::$included_css) {
			$css = "
				.cloaked_email span:before {
					content: attr(data-content-pre);
				}
				.cloaked_email span:after {
					content: attr(data-content-post);
				}
			";
			JFactory::getDocument()->addStyleDeclaration($css);
			self::$included_css = 1;
		}

		$id = 'ce_' . substr(md5(rand()), 0, 8);

		if(!$text || $text == $mail) {
			// No link or text is equal to mailto.
			$text = self::_createSpans($mail, $id);
		} else {
			// Text is an email address.
			if($email) {
				$text = self::_createSpans($text);
			}
			// Text is different to email address.
			if($mode) {
				$text .= self::_createSpans($mail, $id, 1);
			}
		}

		if(!$mode) {
			// Output text only link.
			return '<!--- ' . JText::_('JLIB_HTML_CLOAKING') . ' --->' . $text;
		}

		if(!self::$included_js) {
			/* Below javascript is minified via http://closure-compiler.appspot.com/home
				var Joomla = ( Joomla || {} );
				Joomla.addCloakedMailto = function(id){
					var el = document.getElementById(id);
					if(el) {
						var els = el.getElementsByTagName("span");
						var pre = "";
						var post = "";
						for (var i = 0, l = els.length; i < l; i++) {
							pre += els[i].getAttribute("data-content-pre");
							post = els[i].getAttribute("data-content-post") + post;
						}
						el.parentNode.href= "mailto:" + pre + post;
					}
				}
			*/
			$js = 'var Joomla=Joomla||{};Joomla.addCloakedMailto=function(a){if(a=document.getElementById(a)){for(var c=a.getElementsByTagName("span"),d="",e="",b=0,f=c.length;b<f;b++)d+=c[b].getAttribute("data-content-pre"),e=c[b].getAttribute("data-content-post")+e;a.parentNode.href="mailto:"+d+e}};';

			JFactory::getDocument()->addScriptDeclaration($js);
			self::$included_js = 1;
		}

		return
			'<a ' . $pre . 'href="javascript:// ' . htmlentities(JText::_('JLIB_HTML_CLOAKING'), ENT_COMPAT, 'UTF-8') . '"' . $post . '>'
			. $text
			. '</a>'
			. '<script type="text/javascript">Joomla.addCloakedMailto("' . $id . '");</script>';

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
	protected static function _createSpans($str, $id = 0, $hide = 0)
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

		return
			'<span class="cloaked_email"' . ($id ? ' id="' . $id . '"' : '') . '' . ($hide ? ' style="display:none;"' : '') . '>'
			. '<span data-content-post="' . $parts['5'] . '" data-content-pre="' . $parts['0'] . '">'
			. '<span data-content-post="' . $parts['4'] . '" data-content-pre="' . $parts['1'] . '">'
			. '<span data-content-post="' . $parts['3'] . '" data-content-pre="' . $parts['2'] . '">'
			. '</span></span></span></span>';
	}
}
