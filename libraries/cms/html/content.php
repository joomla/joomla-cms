<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
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
	 * @since   3.9.0
	 */
	public static function months($state)
	{
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		foreach ($state as $key => $value) 
		{
			$model->setState($key, $value);
		}

		$model->setState('filter.category_id', $state->get('category.id'));
		$model->setState('list.start', 0);
		$model->setState('list.limit', -1);
		$model->setState('list.direction', 'asc');
		$model->setState('list.filter', '');

		$items = array();

		foreach ($model->countItemsByMonth() as $item)
		{
			$date    = new JDate($item->d);
			$items[] = JHtml::_('select.option', $item->d, $date->format('F Y') . ' [' . $item->c . ']');
		}

		return $items;
	}
}
