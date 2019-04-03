<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Requests view class
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyViewRequests extends JViewLegacy
{
	/**
	 * The active search tools filters
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 * @note   Must be public to be accessed from the search tools layout
	 */
	public $activeFilters;

	/**
	 * Form instance containing the search tools filter form
	 *
	 * @var    JForm
	 * @since  __DEPLOY_VERSION__
	 * @note   Must be public to be accessed from the search tools layout
	 */
	public $filterForm;

	/**
	 * The items to display
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $sidebar;

	/**
	 * The state information
	 *
	 * @var    JObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * The age of urgent requests
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $urgentRequestAge;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Initialise variables
		$this->items            = $this->get('Items');
		$this->pagination       = $this->get('Pagination');
		$this->state            = $this->get('State');
		$this->filterForm       = $this->get('FilterForm');
		$this->activeFilters    = $this->get('ActiveFilters');
		$this->urgentRequestAge = (int) JComponentHelper::getParams('com_privacy')->get('notify', 14);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_REQUESTS'), 'lock');

		JToolbarHelper::addNew('request.add');

		JToolbarHelper::preferences('com_privacy');
		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_REQUESTS');

	}
}
