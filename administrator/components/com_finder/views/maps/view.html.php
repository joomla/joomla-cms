<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

/**
 * Groups view class for Finder.
 *
 * @since  2.5
 */
class FinderViewMaps extends JViewLegacy
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
		// Load plugin language files.
		FinderHelperLanguage::loadPluginLanguage();

		// Load the view data.
		$this->items         = $this->get('Items');
		$this->total         = $this->get('Total');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		FinderHelper::addSubmenu('maps');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Prepare the view.
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

		JToolbarHelper::title(JText::_('COM_FINDER_MAPS_TOOLBAR_TITLE'), 'zoom-in finder');

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publishList('maps.publish');
			JToolbarHelper::unpublishList('maps.unpublish');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_finder');
		}

		JToolbarHelper::divider();
		JToolbar::getInstance('toolbar')->appendButton(
			'Popup',
			'bars',
			'COM_FINDER_STATISTICS',
			'index.php?option=com_finder&view=statistics&tmpl=component',
			550,
			350
		);
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_CONTENT_MAPS');

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'maps.delete');
			JToolbarHelper::divider();
		}
	}
}
