<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_articles_popular
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'models');

abstract class modArticlesPopularHelper
{
	public static function getList(&$params)
	{
		// Get an instance of the generic articles model
		$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$appParams = JFactory::getApplication()->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.published', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		if ($catid = $params->get('catid')) {
			$model->setState('filter.category_id', $catid);
		}

		// Ordering
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'DESC');

		$items = $model->getItems();

		foreach ($items as &$item) {
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid.':'.$item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			}
			else {
				$item->link = JRoute::_('index.php?option=com_user&view=login');
			}

			$item->introtext = JHtml::_('content.prepare', $item->introtext);
		}

		return $items;
	}
}
