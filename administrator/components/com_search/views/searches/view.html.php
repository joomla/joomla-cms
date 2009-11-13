<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of search terms.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @since		1.5
 */
class SearchViewSearches extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('enabled',		$state->params->get('enabled'));
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$canDo	= SearchHelper::getActions();

		JToolBarHelper::title(JText::_('Search_Manager_Searches'), 'search.png');
		JToolBarHelper::custom('searches.reset', 'refresh.png', 'refresh_f2.png', 'Reset', false);
		
		JToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_search');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.stats.searches');
	}
}