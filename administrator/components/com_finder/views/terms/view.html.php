<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Terms view class for Finder.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderViewTerms extends JViewLegacy
{
	/**
	 * The current content item.
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * An array of term objects.
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
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		// Load the view data.
		$this->item			 = $this->get('Item');
		$this->items		 = $this->get('Items');
		$this->pagination	 = $this->get('Pagination');
		$this->state		 = $this->get('State');
		$this->filterForm	 = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		FinderHelper::addSubmenu('terms');

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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$canDo = JHelperContent::getActions('com_finder');

		if ($this->item->link_id)
		{
			JToolbarHelper::title(JText::sprintf('COM_FINDER_TERMS_TOOLBAR_TITLE', strtolower($this->item->type), $this->item->title), 'zoom-in finder');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_FINDER_TERMS_NO_LINK_TOOLBAR_TITLE'), 'zoom-in finder');
		}

		$toolbar = JToolbar::getInstance('toolbar');

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_finder');
		}

		$toolbar->appendButton('Popup', 'bars', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350);

		JToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_TERMS');
	}
}
