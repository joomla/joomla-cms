<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class to fire onContentPrepare for non-article based content.
 *
 * @since  1.5
 */
abstract class JHtmlContent
{
	/**
	 * Fire onContentPrepare for content that isn't part of an article.
	 *
	 * @param   string  $text     The content to be transformed.
	 * @param   array   $params   The content params.
	 * @param   string  $context  The context of the content to be transformed.
	 *
	 * @return  string   The content after transformation.
	 *
	 * @since   1.5
	 */
	public static function prepare($text, $params = null, $context = 'text')
	{
		if ($params === null)
		{
			$params = new JObject;
		}

		$article = new stdClass;
		$article->text = $text;
		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onContentPrepare', array($context, &$article, &$params, 0));

		return $article->text;
	}
}
