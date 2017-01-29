<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The total number of items
	 *
	 * @var  object
	 */
	protected $total;

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
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->total         = $this->get('Total');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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

		return parent::display($tpl);
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
			JToolbarHelper::checkin('filters.checkin');
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
	}
}
