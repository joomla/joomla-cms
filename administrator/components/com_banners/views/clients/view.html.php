<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of clients.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersViewClients extends JViewLegacy
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
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		BannersHelper::addSubmenu('clients');

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
		require_once JPATH_COMPONENT.'/helpers/banners.php';

		$canDo	= BannersHelper::getActions();

		JToolbarHelper::title(JText::_('COM_BANNERS_MANAGER_CLIENTS'), 'banners-clients.png');
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('client.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('client.edit');
		}
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('clients.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('clients.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('clients.archive');
			JToolbarHelper::checkin('clients.checkin');
		}
		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'clients.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('clients.trash');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_banners');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_BANNERS_CLIENTS');

		JHtmlSidebar::setAction('index.php?option=com_banners&view=clients');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_state',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);
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
			'a.status' => JText::_('JSTATUS'),
			'a.name' => JText::_('COM_BANNERS_HEADING_CLIENT'),
			'contact' => JText::_('COM_BANNERS_HEADING_CONTACT'),
			'client_name' => JText::_('COM_BANNERS_HEADING_CLIENT'),
			'nbanners' => JText::_('COM_BANNERS_HEADING_ACTIVE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
