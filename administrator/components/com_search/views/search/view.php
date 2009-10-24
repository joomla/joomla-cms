<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	Search
 * @since 1.5
 */
class SearchViewSearch extends JView
{
	function display($tpl=null)
	{
		$app	= &JFactory::getApplication();

		JToolBarHelper::title(JText::_('Search Statistics'), 'search.png');
		JToolBarHelper::custom('reset', 'delete.png', 'delete_f2.png', 'Reset', false);
		if (JFactory::getUser()->authorise('core.admin', 'com_cache')) {
			JToolBarHelper::preferences('com_search');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.stats.searches');

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('Search Statistics'));

		$limit 		= $app->getUserStateFromRequest('global.list.limit',	'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest('com_search.limitstart', 'limitstart', 0, 'int');

		$model = $this->getModel();
		$items = $model->getItems();
		$params = &JComponentHelper::getParams('com_search');
		$enabled = $params->get('enabled');
		JHtml::_('behavior.tooltip');
		jimport('joomla.html.pagination');
		$pageNav = new JPagination(count($items), $limitstart, $limit);

		$showResults	= JRequest::getInt('search_results');

		$search 		= $app->getUserStateFromRequest('com_search.search', 'search', '', 'string');

		$this->assignRef('items', 	$items);
		$this->assignRef('enabled', $enabled);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('search', 	$search);
		$this->assignRef('lists',	$model->lists);

		$this->assignRef('showResults', $showResults);

		parent::display($tpl);
	}
}