<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of modules.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesViewModules extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= ModulesHelper::getActions();

		JToolBarHelper::title(JText::_('Modules_Manager_Modules'), 'module.png');
		if ($canDo->get('core.create')) {
			//JToolBarHelper::addNew('module.add');
			$bar = &JToolBar::getInstance('toolbar');
			$bar->appendButton('Popup', 'new', 'New', 'index.php?option=com_modules&amp;view=select&amp;tmpl=component', 850, 400);
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('module.edit');
		}
		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('modules.duplicate', 'copy.png', 'copy_f2.png', 'JToolbar_Duplicate', true);
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::custom('modules.publish', 'publish.png', 'publish_f2.png', 'JToolbar_Enable', true);
			JToolBarHelper::custom('modules.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JToolbar_Disable', true);
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'modules.delete');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('modules.trash');
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_modules');
		}
		JToolBarHelper::help('screen.module');
	}
}
