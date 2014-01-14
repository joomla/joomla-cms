<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_weblinks/helpers/route.php';
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_weblinks/models', 'WeblinksModel');

class modWeblinksHelper
{
	static function getList($params)
	{

		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Category', 'WeblinksModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));

		$model->setState('filter.state', 1);
		$model->setState('filter.publish_date', true);
		$model->setState('filter.archived', 0);
		$model->setState('filter.approved', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_weblinks')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		$ordering = $params->get('ordering', 'ordering');
		$model->setState('list.ordering', $ordering == 'order' ? 'ordering' : $ordering);
		$model->setState('list.direction', $params->get('direction', 'asc'));

		$catid	= (int) $params->get('catid', 0);
		$model->setState('category.id', $catid);

		// Create query object
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$case_when1 = ' CASE WHEN ';
		$case_when1 .= $query->charLength('a.alias');
		$case_when1 .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when1 .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when1 .= ' ELSE ';
		$case_when1 .= $a_id.' END as slug';

		$case_when2 = ' CASE WHEN ';
		$case_when2 .= $query->charLength('c.alias');
		$case_when2 .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when2 .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when2 .= ' ELSE ';
		$case_when2 .= $c_id.' END as catslug';

		$model->setState('list.select', 'a.*, c.published AS c_published,'.$case_when1.','.$case_when2.','.
		'DATE_FORMAT(a.date, "%Y-%m-%d") AS created');

		$model->setState('filter.c.published', 1);

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

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
