<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Consents view class
 *
 * @since  3.9.0
 */
class PrivacyViewConsents extends JViewLegacy
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
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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
		JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_CONSENTS'), 'lock');

		$bar = JToolbar::getInstance('toolbar');

		// Add a button to invalidate a consent
		$bar->appendButton(
			'Confirm',
			'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE_CONFIRM_MSG',
			'trash',
			'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE',
			'consents.invalidate',
			true
		);

		// If the filter is restricted to a specific subject, show the "Invalidate all" button
		if ($this->state->get('filter.subject') != '')
		{
			$bar->appendButton(
				'Confirm',
				'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE_ALL_CONFIRM_MSG',
				'cancel',
				'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE_ALL',
				'consents.invalidateAll',
				false
			);
		}

		JToolbarHelper::preferences('com_privacy');

		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_CONSENTS');
	}
}
