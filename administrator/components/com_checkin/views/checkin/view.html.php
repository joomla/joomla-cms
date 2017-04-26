<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Checkin component
 *
 * @since  1.0
 */
class CheckinViewCheckin extends JViewLegacy
{
	/**
	 * Unused class variable
	 *
	 * @var  object
	 * @deprecated  4.0
	 */
	protected $tables;

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
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->total         = $this->get('Total');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
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
		JToolbarHelper::title(JText::_('COM_CHECKIN_GLOBAL_CHECK_IN'), 'checkin');

		JToolbarHelper::custom('checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);

		if (JFactory::getUser()->authorise('core.admin', 'com_checkin'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_checkin');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_SITE_MAINTENANCE_GLOBAL_CHECK-IN');
	}
}
