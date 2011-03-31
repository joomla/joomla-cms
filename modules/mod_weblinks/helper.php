<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.DS.'components'.DS.'com_weblinks'.DS.'helpers'.DS.'route.php';
JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_weblinks'.DS.'models', 'WeblinksModel');

class modWeblinksHelper
{
	static function getList($params)
	{

		// Get an instance of the generic articles model
		$model = JModel::getInstance('Category', 'WeblinksModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));

		$model->setState('filter.state', 1);
		$model->setState('filter.archived', 0);
		$model->setState('filter.approved', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_weblinks')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		$model->setState('list.ordering', 'title');
		$model->setState('list.direction', 'asc');

		$catid	= (int) $params->get('catid', 0);
		$model->setState('category.id', $catid);

		$model->setState('list.select', 'a.*, c.published AS c_published,
		CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,
		CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug,
		DATE_FORMAT(a.date, "%Y-%m-%d") AS created');

		$model->setState('filter.c.published', 1);

		// Filter by language
		$model->setState('filter.language',$app->getLanguageFilter());

		$items = $model->getItems();

		/*
		 * This was in the previous code before we changed over to using the
		 * weblinkscategory model but I don't see any models using checked_out filters
		 * in their getListQuery() methods so I believe we should not be adding this now
		 */

		/*
		 $query->where('(a.checked_out = 0 OR a.checked_out = '.$user->id.')');
		 */
		for ($i =0, $count = count($items); $i < $count; $i++) {
			$item = &$items[$i];
			if ($item->params->get('count_clicks', $params->get('count_clicks')) == 1) {
				$item->link	= JRoute::_('index.php?option=com_weblinks&task=weblink.go&catid='.$item->catslug.'&id='. $item->slug);
			} else {
				$item->link = $item->url;
			}
		}
		return $items;

	}
}
