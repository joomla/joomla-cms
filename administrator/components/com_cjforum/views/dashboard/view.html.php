<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewDashboard extends JViewLegacy
{
	protected $state;
	
	public function display ($tpl = null)
	{
		$model = $this->getModel();
		$state = $model->getState();
		
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$this->recent = $model->getItems();
		
		$model->setState('list.ordering', 'a.replied');
		$this->trending = $model->getItems();
		$this->topicCount = $model->getTopicCountByDay();
		$this->replyCount = $model->getReplyCountByDay();
		$this->geoReport = $model->getGeoLocationReport();
		
		JLoader::import('joomla.application.component.model');
		JLoader::import('users', JPATH_COMPONENT_ADMINISTRATOR.'/models');
		$model = JModelLegacy::getInstance( 'users', 'CjForumModel' );
		
		$state = $model->getState();
		$model->setState('list.limit', 5);
		$model->setState('list.ordering', 'cju.topics');
		$model->setState('list.direction', 'desc');
		$this->topusers = $model->getItems();

		CjForumHelper::addSubmenu('dashboard');
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		$version = CJFunctions::get_component_update_check('com_cjforum', CF_CURR_VERSION);
		$v = array();
		
		if(!empty($version) && !empty($version['connect']))
		{
			$v['connect'] = (int)$version['connect'];
			$v['version'] = (string)$version['version'];
			$v['released'] = (string)$version['released'];
			$v['changelog'] = (string)$version['changelog'];
			$v['status'] = (string)$version['status'];
		}
		
		$this->version = $v;
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = JHelperContent::getActions('com_cjforum');
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFORUM_DASHBOARD_TITLE'), 'stack dashboard');
		
		if ($user->authorise('core.admin', 'com_cjforum'))
		{
			JToolbarHelper::preferences('com_cjforum');
		}
		
		JToolbarHelper::help('JHELP_CJFORUM_DASHABOARD');
	}
}
