<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of modules.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesViewModules extends JViewLegacy
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

		// Check if there are no matching items
		if(!count($this->items)){
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_MODULES_MSG_MANAGE_NO_MODULES')
				, 'warning'
			);
		}

		$this->addToolbar();
		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
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
			JToolBarHelper::publish('modules.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('modules.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::checkin('modules.checkin');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} elseif ($canDo->get('core.edit.state')) {
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
