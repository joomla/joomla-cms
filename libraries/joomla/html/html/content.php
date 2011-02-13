<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class to fire onContentPrepare for non-article based content.
 *
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @since		11.1
 */
abstract class JHtmlContent
{
	/**
	 * Fire onContentPrepare for content that isn't part of an article.
	 *
	 * @param	string	The content to be transformed.
	 * @param	array	The content params.
	 * @return	string	The content after transformation.
	 */
	public static function prepare($text, $params = null)
	{
		if ($params === null) {
			$params = new JObject;
		}
		$article = new stdClass;
		$article->text = $text;
		JPluginHelper::importPlugin('content');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger(
			'onContentPrepare', array ('text', &$article, &$params, 0)
		);

		return $article->text;
	}
}
