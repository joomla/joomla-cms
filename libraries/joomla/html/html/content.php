<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class to fire onPrepareContent for non-article based content.
 *
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlContent
{
	/**
	 * Fire onPrepareContent for content that isn't part of an article.
	 *
	 * @param	string	The content to be transformed.
	 * @param	array	The content params.
	 * @return	string	The content after transformation.
	 */
	public static function prepare($text, $params = null)
	{
		if ($params === null) {
			$params = array();
		}
		$article = new stdClass;
		$article->text = $text;
		JPluginHelper::importPlugin('content');
		$dispatcher = &JDispatcher::getInstance();
		$results = $dispatcher->trigger(
			'onPrepareContent', array (&$article, &$params, 0)
		);

		return $article->text;
	}
}
