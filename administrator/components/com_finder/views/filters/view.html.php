<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Filters view class for Finder.
 *
 * @since  2.5
 */
class FinderViewFilters extends JViewLegacy
{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Load the view data.
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->total = $this->get('Total');
		$this->state = $this->get('State');

		FinderHelper::addSubmenu('filters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Method to configure the toolbar for this view.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_finder');

		JToolbarHelper::title(JText::_('COM_FINDER_FILTERS_TOOLBAR_TITLE'), 'zoom-in finder');
		$toolbar = JToolbar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('filter.add');
			JToolbarHelper::editList('filter.edit');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publishList('filters.publish');
			JToolbarHelper::unpublishList('filters.unpublish');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_finder');
		}

		JToolbarHelper::divider();
		$toolbar->appendButton('Popup', 'bars', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350);
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_SEARCH_FILTERS');

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'filters.delete');
			JToolbarHelper::divider();
		}

		JHtmlSidebar::setAction('index.php?option=com_finder&view=filters');

		JHtmlSidebar::addFilter(
			JText::_('COM_FINDER_INDEX_FILTER_BY_STATE'),
			'filter_state',
			JHtml::_('select.options', JHtml::_('finder.statelist'), 'value', 'text', $this->state->get('filter.state'))
		);
	}
}
