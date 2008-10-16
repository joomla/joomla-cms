<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Utility class to fire onPrepareContent for non-article based content.
 *
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHTMLContent
{
	/**
	 * Fire onPrepareContent for content that isn't part of an article.
	 *
	 * @param string The content to be transformed.
	 * @param array The content params.
	 * @return string The content after transformation.
	 */
	public static function prepare($text, $params = null)
	{
		if ($params === null) {
			$params = array();
		}
		/*
		 * Create a skeleton of an article
		 */
		$article = new stdClass();
		$article->text = $text;
		JPluginHelper::importPlugin('content');
		$dispatcher = &JDispatcher::getInstance();
		$results = $dispatcher->trigger(
			'onPrepareContent', array (&$article, &$params, 0)
		);

		return $article->text;
	}

}
