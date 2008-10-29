<?php
/**
* @version		$Id$
* @package		oomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent('onPrepareContent', 'plgContentEmailCloak');

/**
 * Plugin that cloaks all emails in content from spambots via Javascript.
 *
 * @param object|string An object with a "text" property or the string to be
 * cloaked.
 * @param array Additional parameters. See {@see plgEmailCloak()}.
 * @param int Optional page number. Unused. Defaults to zero.
 * @return boolean True on success.
 */
function plgContentEmailCloak(&$row, &$params, $page=0)
{
	if (is_object($row)) {
		return plgEmailCloak($row->text, $params);
	}
	return plgEmailCloak($row, $params);
}

/**
 * Genarate a search pattern based on link and text.
 *
 * @param string The target of an e-mail link.
 * @param string The text enclosed by the link.
 * @return string A regular expression that matches a link containing the
 * parameters.
 */
function plgContentEmailCloak_searchPattern ($link, $text) {
	// <a href="mailto:anyLink">anyText</a>
	$pattern = '~(?:<a [\w "\'=\@\.\-]*href\s*=\s*"mailto:'
		. $link . '"[\w "\'=\@\.\-]*)>' . $text . '</a>~i';

	return $pattern;
}

/**
 * Cloak all emails in text from spambots via Javascript.
 *
 * @param string The string to be cloaked.
 * @param array Additional parameters. Parameter "mode" (integer, default 1)
 * replaces addresses with "mailto:" links if nonzero.
 * @return boolean True on success.
 */
function plgEmailCloak(&$text, &$params)
{

	/*
	 * Check for presence of {emailcloak=off} which is explicits disables this
	 * bot for the item.
	 */
	if (JString::strpos($text, '{emailcloak=off}') !== false) {
		$text = JString::str_ireplace('{emailcloak=off}', '', $text);
		return true;
	}

	// Simple performance check to determine whether bot should process further.
	if (JString::strpos($text, '@') === false) {
		return true;
	}

	$plugin = & JPluginHelper::getPlugin('content', 'emailcloak');

	// Load plugin params info
	$pluginParams = new JParameter($plugin->params);
	$mode = $pluginParams->def('mode', 1);

	// any@email.address.com
	$searchEmail = '([\w\.\-]+\@(?:[a-z0-9\.\-]+\.)+(?:[a-z0-9\-]{2,4}))';
	// any@email.address.com?subject=anyText
	$searchEmailLink = $searchEmail . '([?&][\x20-\x7f][^"<>]+)';
	// anyText
	$searchText = '([\x20-\x7f][^<>]+)';

	/*
	 * Search for derivatives of link code <a href="mailto:email@amail.com"
	 * >email@amail.com</a>
	 */
	$pattern = plgContentEmailCloak_searchPattern($searchEmail, $searchEmail);
	while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
		$mail = $regs[1][0];
		$mailText = $regs[2][0];

		// Check to see if mail text is different from mail addy
		$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

		// Replace the found address with the js cloaked email
		$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	/*
	 * Search for derivatives of link code <a href="mailto:email@amail.com">
	 * anytext</a>
	 */
	$pattern = plgContentEmailCloak_searchPattern($searchEmail, $searchText);
	while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
		$mail = $regs[1][0];
		$mailText = $regs[2][0];

		$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

		// Replace the found address with the js cloaked email
		$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	/*
	 * Search for derivatives of link code <a href="mailto:email@amail.com?
	 * subject=Text">email@amail.com</a>
	 */
	$pattern = plgContentEmailCloak_searchPattern($searchEmailLink, $searchEmail);
	while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
		$mail = $regs[1][0] . $regs[2][0];
		$mailText = $regs[3][0];
		// Needed for handling of Body parameter
		$mail = str_replace( '&amp;', '&', $mail );

		// Check to see if mail text is different from mail addy
		$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText);

		// Replace the found address with the js cloaked email
		$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	/*
	 * Search for derivatives of link code <a href="mailto:email@amail.com?
	 * subject=Text">anytext</a>
	 */
	$pattern = plgContentEmailCloak_searchPattern($searchEmailLink, $searchText);
	while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
		$mail = $regs[1][0] . $regs[2][0];
		$mailText = $regs[3][0];
		// Needed for handling of Body parameter
		$mail = str_replace('&amp;', '&', $mail);

		$replacement = JHtml::_('email.cloak', $mail, $mode, $mailText, 0);

		// Replace the found address with the js cloaked email
		$text = substr_replace($text, $replacement, $regs[0][1], strlen($regs[0][0]));
	}

	// Search for plain text email@amail.com
	$pattern = '~' . $searchEmail . '([^a-z0-9]|$)~i';
	while (preg_match($pattern, $text, $regs, PREG_OFFSET_CAPTURE)) {
		$mail = $regs[1][0];
		$replacement = JHtml::_('email.cloak', $mail, $mode);

		// Replace the found address with the js cloaked email
		$text = substr_replace($text, $replacement, $regs[1][1], strlen($mail));
	}
	return true;
}
