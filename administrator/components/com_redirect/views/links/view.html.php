<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of redirection links.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 * @since       1.6
 */
class RedirectViewLinks extends JViewLegacy
{
	protected $enabled;

	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->enabled		= RedirectHelper::isEnabled();
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
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
		$state	= $this->get('State');
		$canDo	= RedirectHelper::getActions();

		JToolbarHelper::title(JText::_('COM_REDIRECT_MANAGER_LINKS'), 'redirect');
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('link.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('link.edit');
		}
		if ($canDo->get('core.edit.state'))
		{
			if ($state->get('filter.state') != 2){
				JToolbarHelper::divider();
				JToolbarHelper::publish('links.publish', 'JTOOLBAR_ENABLE', true);
				JToolbarHelper::unpublish('links.unpublish', 'JTOOLBAR_DISABLE', true);
			}
			if ($state->get('filter.state') != -1 )
			{
				JToolbarHelper::divider();
				if ($state->get('filter.state') != 2)
				{
					JToolbarHelper::archiveList('links.archive');
				}
				elseif ($state->get('filter.state') == 2)
				{
					JToolbarHelper::unarchiveList('links.publish', 'JTOOLBAR_UNARCHIVE');
				}
			}
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'links.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		} elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('links.trash');
			JToolbarHelper::divider();
		}
		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_redirect');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_COMPONENTS_REDIRECT_MANAGER');

		JHtmlSidebar::setAction('index.php?option=com_redirect&view=links');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_state',
			JHtml::_('select.options', RedirectHelper::publishedOptions(), 'value', 'text', $this->state->get('filter.state'), true)
		);
	}
}
