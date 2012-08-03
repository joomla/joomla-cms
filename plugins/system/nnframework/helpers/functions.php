<?php
/**
 * NoNumber Framework Helper File: Functions
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Framework Functions
 */

class NNFrameworkFunctions
{
	/* Backwards compatibility */
	static function setSurroundingTags($pre, $post, $tags = 0)
	{
		require_once __DIR__.'/tags.php';
		return NNTags::setSurroundingTags($pre, $post, $tags);
	}

	static function dateToDateFormat($dateFormat)
	{
		require_once __DIR__.'/text.php';
		return NNText::dateToDateFormat($dateFormat);
	}

	static function dateToStrftimeFormat($dateFormat)
	{
		require_once __DIR__.'/text.php';
		return NNText::dateToStrftimeFormat($dateFormat);
	}

	static function html_entity_decoder($given_html, $quote_style = ENT_QUOTES, $charset = 'UTF-8')
	{
		require_once __DIR__.'/text.php';
		return NNText::html_entity_decoder($given_html, $quote_style, $charset);
	}

	static function cleanTitle($str, $striptags = 0)
	{
		require_once __DIR__.'/text.php';
		return NNText::cleanTitle($str, $striptags);
	}

	static function isEditPage()
	{
		require_once __DIR__.'/protect.php';
		return NNProtect::isEditPage();
	}

	static function getFormRegex($regex_format = 0)
	{
		require_once __DIR__.'/protect.php';
		return NNProtect::getFormRegex($regex_format);
	}

	static function protectForm(&$string, $tags = array(), $protected = array())
	{
		require_once __DIR__.'/protect.php';
		NNProtect::protectForm($string, $tags, $protected);
	}

	static function unprotectForm(&$string, $tags = array(), $protected = array())
	{
		require_once __DIR__.'/protect.php';
		NNProtect::unprotectForm($string, $tags, $protected);
	}
}