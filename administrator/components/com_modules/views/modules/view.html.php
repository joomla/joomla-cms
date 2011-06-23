<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= ModulesHelper::getActions();

		JToolBarHelper::title(JText::_('COM_MODULES_MANAGER_MODULES'), 'module.png');

		if ($canDo->get('core.create')) {
			//JToolBarHelper::addNew('module.add');
			$bar = JToolBar::getInstance('toolbar');
			$bar->appendButton('Popup', 'new', 'JTOOLBAR_NEW', 'index.php?option=com_modules&amp;view=select&amp;tmpl=component', 850, 400);
		}

		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('module.edit');
		}

		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('modules.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publish('modules.publish');
			JToolBarHelper::unpublish('modules.unpublish');
			JToolBarHelper::divider();
			JToolBarHelper::checkin('modules.checkin');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('modules.trash');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_modules');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_EXTENSIONS_MODULE_MANAGER');
	}
}
