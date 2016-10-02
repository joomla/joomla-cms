<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of messages.
 *
 * @since  1.6
 */
class MessagesViewMessages extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

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
		$state = $this->get('State');
		$canDo = JHelperContent::getActions('com_messages');
		JToolbarHelper::title(JText::_('COM_MESSAGES_MANAGER_MESSAGES'), 'envelope inbox');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('message.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publish('messages.publish', 'COM_MESSAGES_TOOLBAR_MARK_AS_READ', true);
			JToolbarHelper::unpublish('messages.unpublish', 'COM_MESSAGES_TOOLBAR_MARK_AS_UNREAD', true);
		}

		JToolbarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton(
			'Popup',
			'cog',
			'COM_MESSAGES_TOOLBAR_MY_SETTINGS',
			'index.php?option=com_messages&amp;view=config&amp;tmpl=component',
			500,
			250,
			0,
			0,
			'',
			'',
			'<button class="btn" type="button" data-dismiss="modal" aria-hidden="true">'
			. JText::_('JCANCEL')
			. '</button>'
			. '<button class="btn btn-success" type="button" data-dismiss="modal" aria-hidden="true"'
			. ' onclick="jQuery(\'#modal-cog iframe\').contents().find(\'#saveBtn\').click();">'
			. JText::_('JSAVE')
			. '</button>'
		);

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'messages.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::trash('messages.trash');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_messages');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_MESSAGING_INBOX');
	}
}
