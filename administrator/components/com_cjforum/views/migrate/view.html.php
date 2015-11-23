<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class CjForumViewMigrate extends JViewLegacy
{
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->canDo = JHelperContent::getActions('com_cjforum');
		
		CjForumHelper::addSubmenu('migrate');
		$this->sidebar = JHtmlSidebar::render();
		$this->addToolbar();
		
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo	= $this->canDo;
		$user 	= JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_CJFORUM_VIEW_MIGRATE_TITLE'), 'migrate');

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::custom('migrate.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			JToolbarHelper::preferences('com_cjforum');
		}
	}
}
