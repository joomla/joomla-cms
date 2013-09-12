<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of template styles.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesViewStyles extends JViewLegacy
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
		$this->preview		= JComponentHelper::getParams('com_templates')->get('template_positions_display');

		TemplatesHelper::addSubmenu('styles');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

			// Check if there are no matching items
		if (!count($this->items))
		{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_TEMPLATES_MSG_MANAGE_NO_STYLES'),
				'warning'
			);
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo	= TemplatesHelper::getActions();

		JToolbarHelper::title(JText::_('COM_TEMPLATES_MANAGER_STYLES'), 'thememanager');

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::makeDefault('styles.setDefault', 'COM_TEMPLATES_TOOLBAR_SET_HOME');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('style.edit');
		}
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::custom('styles.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'styles.delete');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_templates');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES');

		JHtmlSidebar::setAction('index.php?option=com_templates&view=styles');

		JHtmlSidebar::addFilter(
			JText::_('COM_TEMPLATES_FILTER_TEMPLATE'),
			'filter_template',
			JHtml::_('select.options', TemplatesHelper::getTemplateOptions($this->state->get('filter.client_id')), 'value', 'text', $this->state->get('filter.template'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JGLOBAL_FILTER_CLIENT'),
			'filter_client_id',
			JHtml::_('select.options', TemplatesHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'))
		);
	}
}
