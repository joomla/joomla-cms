<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	protected $enabled;

	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->enabled    = $this->state->params->get('enabled');
		$this->canDo      = JHelperContent::getActions('com_search');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
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
