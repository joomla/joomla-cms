<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.emailcloak
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Email cloack plugin class.
 *
 * @since  1.5
 */
class PlgContentEmailcloak extends JPlugin
{
	/**
	 * Used to save the extracted form elements in an array. If nothing to replace, it's default false
	 *
	 * @since  3.4.5
	 */
	private $saveReplacements = false;

	/**
	 * Plugin that cloaks all emails in content from spambots via Javascript.
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property or the string to be cloaked.
	 * @param   mixed    &$params  Additional parameters. See {@see PlgContentEmailcloak()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context === 'com_finder.indexer')
		{
			return true;
		}

		if (is_object($row))
		{
			return $this->_cloak($row->text, $params);
		}

		return $this->_cloak($row, $params);
	}

	/**
	 * Generate a search pattern based on link and text.
	 *
	 * @param   string  $link  The target of an email link.
	 * @param   string  $text  The text enclosed by the link.
	 *
	 * @return  string	A regular expression that matches a link containing the parameters.
	 */
	protected function _getPattern ($link, $text)
	{
		$pattern = '~(?:<a ([^>]*)href\s*=\s*"mailto:' . $link . '"([^>]*))>' . $text . '</a>~i';

		return $pattern;
	}

	/**
	 * Adds an attributes to the js cloaked email.
	 *
	 * @param   string  $jsEmail  Js cloaked email.
	 * @param   string  $before   Attributes before email.
	 * @param   string  $after    Attributes after email.
	 *
	 * @return string Js cloaked email with attributes.
	 */
	protected function _addAttributesToEmail($jsEmail, $before, $after)
	{
		if ($before !== '')
		{
			$before = str_replace("'", "\'", $before);
			$jsEmail = str_replace(".innerHTML += '<a '", ".innerHTML += '<a {$before}'", $jsEmail);
		}

		if ($after !== '')
		{
			$after = str_replace("'", "\'", $after);
			$jsEmail = str_replace("'\'>'", "'\'{$after}>'", $jsEmail);
		}

		return $jsEmail;
	}

	/**
	 * Cloak all emails in text from spambots via Javascript.
	 *
	 * @param   string  &$text    The string to be cloaked.
	 * @param   mixed   &$params  Additional parameters. Parameter "mode" (integer, default 1)
	 *                             replaces addresses with "mailto:" links if nonzero.
	 *
	 * @return  boolean  True on success.
	 */
	protected function _cloak(&$text, &$params)
	{
		/*
		 * Check for presence of {emailcloak=off} which is explicits disables this
		 * bot for the item.
		 */
		if (StringHelper::strpos($text, '{emailcloak=off}') !== false)
		{
			$text = StringHelper::str_ireplace('{emailcloak=off}', '', $text);

			return true;
		}

		// Simple performance check to determine whether bot should process further.
		if (StringHelper::strpos($text, '@') === false)
		{
			return true;
		}

		// Extract all Formelements out of the text.
		$text = $this->_extractFormElements($text);
		$mode = $this->params->def('mode', 1);

		// Example: any@example.org
		$searchEmail = '([\w\.\'\-\+]+\@(?:[a-z0-9\.\-]+\.)+(?:[a-zA-Z0-9\-]{2,10}))';

		// Example: any@example.org?subject=anyText
		$searchEmailLink = $searchEmail . '([?&][\x20-\x7f][^"<>]+)';

		// Any Text
		$searchText = '((?:[\x20-\x7f]|[\xA1-\xFF]|[\xC2-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF4][\x80-\xBF]{3})[^<>]+)';

		// Any Image link
		$searchImage = '(<img[^>]+>)';

		// Any Text with <span or <strong
		$searchTextSpan = '(<span[^>]+>|<span>|<strong>|<strong><span[^>]+>|<strong><span>)' . $searchText . '(</span>|</strong>|</span></strong>)';

		// Any address with <span or <strong
		$searchEmailSpan = '(<span[^>]+>|<span>|<strong>|<strong><span[^>]+>|<strong><span>)' . $searchEmail . '(</span>|</strong>|</span></strong>)';

		/*
		 * Search and fix derivatives of link code <a href="http://mce_host/ourdirectory/email@example.org"
		 * >email@example.org</a>. This happens when inserting an email in TinyMCE, cancelling its suggestion to add
		 * the mailto: prefix...
		 */
		$pattern = $this->_getPattern($searchEmail, $searchEmail);
		$pattern = str_replace('"mailto:', '"http://mce_host([\x20-\x7f][^<>]+/)', $pattern);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[3][0];
			$mailText = $regs[5][0];

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = $this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search and fix derivatives of link code <a href="http://mce_host/ourdirectory/email@example.org"
		 * >anytext</a>. This happens when inserting an email in TinyMCE, cancelling its suggestion to add
		 * the mailto: prefix...
		 */
		$pattern = $this->_getPattern($searchEmail, $searchText);
		$pattern = str_replace('"mailto:', '"http://mce_host([\x20-\x7f][^<>]+/)', $pattern);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[3][0];
			$mailText = $regs[5][0];

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = $this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org"
		 * >email@example.org</a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchEmail);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = $regs[4][0];

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = $this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@amail.com"
		 * ><anyspan >email@amail.com</anyspan></a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchEmailSpan);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = $regs[4][0] . $regs[5][0] . $regs[6][0];

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@amail.com">
		 * <anyspan >anytext</anyspan></a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchTextSpan);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = $regs[4][0] . addslashes($regs[5][0]) . $regs[6][0];

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org">
		 * anytext</a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchText);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = addslashes($regs[4][0]);

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = $this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org">
		 * <img anything></a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchImage);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = $regs[4][0];

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org">
		 * <img anything>email@example.org</a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchImage . $searchEmail);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = $regs[4][0] . $regs[5][0];

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org">
		 * <img anything>any text</a>
		 */
		$pattern = $this->_getPattern($searchEmail, $searchImage . $searchText);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0];
			$mailText = $regs[4][0] . addslashes($regs[5][0]);

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[3][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org?
		 * subject=Text">email@example.org</a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchEmail);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0] . $regs[3][0];
			$mailText = $regs[5][0];

			// Needed for handling of Body parameter
			$mail = str_replace('&amp;', '&', $mail);

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = $this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@example.org?
		 * subject=Text">anytext</a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchText);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0] . $regs[3][0];
			$mailText = addslashes($regs[5][0]);

			// Needed for handling of Body parameter
			$mail = str_replace('&amp;', '&', $mail);

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = $this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@amail.com?subject= Text"
		 * ><anyspan >email@amail.com</anyspan></a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchEmailSpan);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0] . $regs[3][0];
			$mailText = $regs[5][0] . $regs[6][0] . $regs[7][0];

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code <a href="mailto:email@amail.com?subject= Text">
		 * <anyspan >anytext</anyspan></a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchTextSpan);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[2][0] . $regs[3][0];
			$mailText = $regs[5][0] . addslashes($regs[6][0]) . $regs[7][0];

			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code
		 * <a href="mailto:email@amail.com?subject=Text"><img anything></a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchImage);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[1][0] . $regs[2][0] . $regs[3][0];
			$mailText = $regs[5][0];

			// Needed for handling of Body parameter
			$mail = str_replace('&amp;', '&', $mail);

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code
		 * <a href="mailto:email@amail.com?subject=Text"><img anything>email@amail.com</a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchImage . $searchEmail);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[1][0] . $regs[2][0] . $regs[3][0];
			$mailText = $regs[4][0] . $regs[5][0] . $regs[6][0];

			// Needed for handling of Body parameter
			$mail = str_replace('&amp;', '&', $mail);

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for derivatives of link code
		 * <a href="mailto:email@amail.com?subject=Text"><img anything>any text</a>
		 */
		$pattern = $this->_getPattern($searchEmailLink, $searchImage . $searchText);

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[1][0] . $regs[2][0] . $regs[3][0];
			$mailText = $regs[4][0] . $regs[5][0] . addslashes($regs[6][0]);

			// Needed for handling of Body parameter
			$mail = str_replace('&amp;', '&', $mail);

			// Check to see if mail text is different from mail addy
			$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

			// Ensure that attributes is not stripped out by email cloaking
			$replacement = html_entity_decode($this->_addAttributesToEmail($replacement, $regs[1][0], $regs[4][0]));

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
		}

		/*
		 * Search for plain text email addresses, such as email@example.org but not within HTML tags:
		 * <img src="..." title="email@example.org"> or <input type="text" placeholder="email@example.org">
		 * The negative lookahead '(?![^<]*>)' is used to exclude this kind of occurrences
		 */
		$pattern = '~(?![^<>]*>)' . $searchEmail . '~i';

		while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE))
		{
			$mail = $regs[1][0];
			$replacement = JHtml::_('email.cloak', $mail, $mode);

			// Replace the found address with the js cloaked email
			$text = substr_replace($text, $replacement, $regs[1][1], strlen($mail));
		}
		// All exctracted Form elements get insert the text again.
		$text = $this->_insertFormElements($text);

		return true;
	}

	/**
	 * Extract all input, select, textarea, script, picture and noemailclloak tags out of the given Text.
	 *
	 * @param   string  $text  The text wich must be cleaned of elements
	 *
	 * @return  string  $text  The cleaned text
	 *
	 * @since   3.4.5
	 */
	private function _extractFormElements($text)
	{
		// Pattern to extract the noemailcloak sections.
		$noemailcloak_pattern = '#\{noemailcloak\}(.*)\{/noemailcloak\}#Uis';
		$counter              = 0;

		preg_match_all($noemailcloak_pattern, $text, $results, PREG_SET_ORDER);

		foreach ($results as $result)
		{
			$replace_string = "{{##formelement_" . $counter . "##}}";
			$text           = str_replace($result[0], $replace_string,  $text);

			$this->saveReplacements[$replace_string] = $result[1];
			$counter++;
		}

		unset($results);

		// Pattern to extract all input tags, (text, checkbox, radio, hidden, submit> and img-tag.
		$input_pattern = '#<(input|img) [^<].*>#Uis';

		preg_match_all($input_pattern, $text, $results);

		// Pattern to extract select, textarea picture and script tags.
		$st_pattern = '#<(select|textarea|picture|script).*</\1>#Uis';

		preg_match_all($st_pattern, $text, $st_results);

		$results = array_merge($results[0], $st_results[0]);

		foreach ($results as $value)
		{
			$replace_string = "{{##formelement_" . $counter . "##}}";
			$text           = str_replace($value, $replace_string, $text);

			$this->saveReplacements[$replace_string] = $value;
			$counter++;
		}

		return $text;
	}

	/**
	 * Inserts all input, select and textareelements in the given Text, which was extractedfrom _extractFormElements
	 *
	 * @param   string  $text  The text where the extracted Formelements get inserted.
	 *
	 * @return  string  $text  The modifiy text.
	 *
	 * @since   3.4.5
	 */
	private function _insertFormElements($text)
	{
		if (!is_array($this->saveReplacements))
		{
			return $text;
		}

		foreach ($this->saveReplacements as $key => $value)
		{
			$text = str_replace($key, $value, $text);
		}

		return $text;
	}
}
