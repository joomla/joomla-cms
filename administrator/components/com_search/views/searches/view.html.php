<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of search terms.
 *
 * @since  1.5
 */
class SearchViewSearches extends JViewLegacy
{
	/**
	 * True if gathering search statistics is enabled
	 *
	 * @var  boolean
	 */
	protected $enabled;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  JObject
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    JForm
	 * @since  __DEPLOY_VERSION__
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public $activeFilters;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var    JObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $canDo;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$app                 = JFactory::getApplication();
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->enabled       = $this->state->params->get('enabled');
		$this->canDo         = JHelperContent::getActions('com_search');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Check if plugin is enabled
		if ($this->enabled)
		{
			$app->enqueueMessage(JText::_('COM_SEARCH_LOGGING_ENABLED'), 'notice');
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_SEARCH_LOGGING_DISABLED'), 'warning');
		}

		$this->addToolbar();
		parent::display($tpl);
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
		$canDo = $this->canDo;

		JToolbarHelper::title(JText::_('COM_SEARCH_MANAGER_SEARCHES'), 'search');

		$showResults = $this->state->get('show_results', 1, 'int');

		if ($showResults === 0)
		{
			JToolbarHelper::custom('searches.toggleresults', 'zoom-in.png', null, 'COM_SEARCH_SHOW_SEARCH_RESULTS', false);
		}
		else
		{
			JToolbarHelper::custom('searches.toggleresults', 'zoom-out.png', null, 'COM_SEARCH_HIDE_SEARCH_RESULTS', false);
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::custom('searches.reset', 'refresh.png', 'refresh_f2.png', 'JSEARCH_RESET', false);
		}

		JToolbarHelper::divider();

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_search');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_SEARCH');
	}
}
