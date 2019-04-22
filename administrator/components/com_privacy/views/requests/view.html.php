<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Requests view class
 *
 * @since  3.9.0
 */
class PrivacyViewRequests extends JViewLegacy
{
	/**
	 * The active search tools filters
	 *
	 * @var    array
	 * @since  3.9.0
	 * @note   Must be public to be accessed from the search tools layout
	 */
	public $activeFilters;

	/**
	 * Form instance containing the search tools filter form
	 *
	 * @var    JForm
	 * @since  3.9.0
	 * @note   Must be public to be accessed from the search tools layout
	 */
	public $filterForm;

	/**
	 * The items to display
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  3.9.0
	 */
	protected $pagination;

	/**
	 * Flag indicating the site supports sending email
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $sendMailEnabled;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $sidebar;

	/**
	 * The state information
	 *
	 * @var    JObject
	 * @since  3.9.0
	 */
	protected $state;

	/**
	 * The age of urgent requests
	 *
	 * @var    integer
	 * @since  3.9.0
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
	 * @since   3.9.0
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
		$this->sendMailEnabled  = (bool) JFactory::getConfig()->get('mailonline', 1);

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
	 * @since   3.9.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_REQUESTS'), 'lock');

		// Requests can only be created if mail sending is enabled
		if (JFactory::getConfig()->get('mailonline', 1))
		{
			JToolbarHelper::addNew('request.add');
		}

		JToolbarHelper::preferences('com_privacy');
		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_REQUESTS');

	}
}
