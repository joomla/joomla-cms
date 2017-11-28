<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Administrator\View\Filters;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Finder\Administrator\Helper\FinderHelper;

/**
 * Filters view class for Finder.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since  3.6.1
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \Joomla\CMS\Pagination\Pagination
	 *
	 * @since  3.6.1
	 */
	protected $pagination;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var  string
	 *
	 * @since  3.6.1
	 */
	protected $sidebar;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 *
	 * @since  3.6.1
	 */
	protected $state;

	/**
	 * The total number of items
	 *
	 * @var  integer
	 *
	 * @since  3.6.1
	 */
	protected $total;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a \JError object.
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
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		\JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();
		$this->sidebar = \JHtmlSidebar::render();

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
		$canDo = ContentHelper::getActions('com_finder');

		ToolbarHelper::title(\JText::_('COM_FINDER_FILTERS_TOOLBAR_TITLE'), 'zoom-in finder');
		$toolbar = Toolbar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('filter.add');
			ToolbarHelper::divider();
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publishList('filters.publish');
			ToolbarHelper::unpublishList('filters.unpublish');
			ToolbarHelper::checkin('filters.checkin');
			ToolbarHelper::divider();
		}

		ToolbarHelper::divider();
		$toolbar->appendButton('Popup', 'bars', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350);
		ToolbarHelper::divider();

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'filters.delete');
			ToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_finder');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_SEARCH_FILTERS');
	}
}
