<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of modules.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
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
				JText::_('COM_MODULES_MSG_MANAGE_NO_MODULES'),
				'warning'
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

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_MODULES_MANAGER_MODULES'), 'module.png');

		if ($canDo->get('core.create')) {
			$title = JText::_('JTOOLBAR_NEW');
			$dhtml = "<button onClick=\"location.href='index.php?option=com_modules&amp;view=select'\" class=\"btn btn-primary\">
						<i class=\"icon-plus icon-white\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'new');
		}

		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('module.edit');
		}

		if ($canDo->get('core.create')) {
			JToolbarHelper::custom('modules.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::publish('modules.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('modules.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin('modules.checkin');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('modules.trash');
		}

		// Add a batch button
		if ($canDo->get('core.edit'))
		{
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_modules');
		}
		JToolbarHelper::help('JHELP_EXTENSIONS_MODULE_MANAGER');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.title' => JText::_('JGLOBAL_TITLE'),
			'position' => JText::_('COM_MODULES_HEADING_POSITION'),
			'name' => JText::_('COM_MODULES_HEADING_MODULE'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'a.created_by' => JText::_('JAUTHOR'),
			'language_title' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
