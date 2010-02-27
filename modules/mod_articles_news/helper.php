<?php
/**
 * @version		$Id: helper.php 14276 2010-01-18 14:20:28Z louis $
 * @package		Joomla.Site
 * @subpackage	mod_articles_latest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'models');

abstract class modArticlesNewsHelper
{
	public static function getList(&$params)
	{
		$app	= &JFactory::getApplication();

		// Get an instance of the generic articles model
		$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$appParams = JFactory::getApplication()->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));

		$model->setState('filter.published', 1);

		$model->setState('list.select', 'a.fulltext, a.id, a.title, a.alias, a.title_alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
			' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' .
			' a.hits, a.featured,' .
			' LENGTH(a.fulltext) AS readmore');
		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		if ($catid = $params->get('catid')) {
			$model->setState('filter.category_id', $catid);
		}

		// Set ordering
		$order_map = array(
			'm_dsc' => 'a.modified DESC, a.created',
			/*
			 * TODO below line does not work because it's running through JDatabase::_getEscaped
			 * which adds unnecessary quotes before and after the null date.
			 * This should be uncommented when it's fixed.
			 */
			//'mc_dsc' => 'CASE WHEN (a.modified = \'0000-00-00 00:00:00\') THEN a.created ELSE a.modified END',
			'c_dsc' => 'a.created'
		);

		$ordering = JArrayHelper::getValue($order_map, $params->get('ordering'), 'a.created');
		$dir = 'DESC';

		$model->setState('list.ordering', $ordering);
		$model->setState('list.direction', $dir);

		$items = $model->getItems();

		foreach ($items as &$item) {
			$item->readmore = (trim($item->fulltext) != '');
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid.':'.$item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentRoute::article($item->slug, $item->catslug));
				$item->linkText = JText::_('MOD_ARTICLES_NEWS_READMORE');
			}
			else {
				$item->link = JRoute::_('index.php?option=com_user&view=login');
				$item->linkText = JText::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
			}

			$item->introtext = JHtml::_('content.prepare', $item->introtext);


			//new
			if (!$params->get('image')) {
				$item->introtext = preg_replace('/<img[^>]*>/', '', $item->introtext);
			}

			$results = $app->triggerEvent('onAfterDisplayTitle', array (&$item, &$params, 1));
			$item->afterDisplayTitle = trim(implode("\n", $results));

			$results = $app->triggerEvent('onBeforeDisplayContent', array (&$item, &$params, 1));
			$item->beforeDisplayContent = trim(implode("\n", $results));
		}
//echo "<pre>";print_r($item);echo "</pre>";
		return $items;
	}
}
