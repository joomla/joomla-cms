<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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

	/**
	 * Returns an array of months.
	 *
	 * @param   Registry  $state  The state object.
	 *
	 * @return  array
	 *
	 * @since   3.8.2
	 */
	public static function months($state)
	{
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		foreach($state as $key => $value) {
			$model->setState($key, $value);
		}
		$model->setState('filter.category_id', $state->get('category.id'));
		$model->setState('list.start', 0);
		$model->setState('list.limit', -1);
		$model->setState('list.direction', 'asc');
		$model->setState('list.filter', '');
	
		$months = array();
		foreach ($model->getItems() as $item)
		{
			$d = date("Y-m", strtotime($item->created)).'-01';
			$months[$d] = (isset($months[$d]) ? $months[$d] + 1 : 1);
		}
	
		$items = array();
		foreach ($months as $d => $c)
		{
			$items[] = JHtml::_('select.option', $d, (new JDate($d))->format('F Y') . ' [' . $c . ']');
		}
		return $items;
	}
}
