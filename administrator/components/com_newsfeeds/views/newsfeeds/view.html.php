<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of newsfeeds.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class NewsfeedsViewNewsfeeds extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
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
		$canDo	= NewsfeedsHelper::getActions($state->get('filter.category_id'));
		$user	= JFactory::getUser();
		JToolBarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEEDS'), 'newsfeeds.png');
		if (count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0) {
			JToolBarHelper::addNew('newsfeed.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('newsfeed.edit');
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::publish('newsfeeds.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('newsfeeds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('newsfeeds.archive');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::checkin('newsfeeds.checkin');
			}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'newsfeeds.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} elseif ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('newsfeeds.trash');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_newsfeeds');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS');
	}
}
