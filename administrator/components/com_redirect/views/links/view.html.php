<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of redirection links.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @since		1.6
 */
class RedirectViewLinks extends JView
{
	protected $enabled;
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
		$this->enabled		= RedirectHelper::isEnabled();
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= RedirectHelper::getActions();

		JToolBarHelper::title(JText::_('COM_REDIRECT_MANAGER_LINKS'), 'redirect');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('link.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('link.edit');
		}
		if ($canDo->get('core.edit.state')) {
			if ($state->get('filter.state') != 2){
				JToolBarHelper::divider();
				JToolBarHelper::publish('links.publish', 'JTOOLBAR_ENABLE');
				JToolBarHelper::unpublish('links.unpublish', 'JTOOLBAR_DISABLE');
			}
			if ($state->get('filter.state') != -1 ) {
				JToolBarHelper::divider();
				if ($state->get('filter.state') != 2) {
					JToolBarHelper::archiveList('links.archive');
				}
				else if ($state->get('filter.state') == 2) {
					JToolBarHelper::unarchiveList('links.publish', 'JTOOLBAR_UNARCHIVE');
				}
			}
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'links.delete', 'JTOOLBAR_EMPTY_TRASH');
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('links.trash');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_redirect');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_COMPONENTS_REDIRECT_MANAGER');
	}
}
