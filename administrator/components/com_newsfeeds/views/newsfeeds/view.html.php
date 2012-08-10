<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of newsfeeds.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.6
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
		JToolbarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEEDS'), 'newsfeeds.png');
		if (count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0) {
			JToolbarHelper::addNew('newsfeed.add');
		}
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('newsfeed.edit');
		}
		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::divider();
			JToolbarHelper::publish('newsfeeds.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('newsfeeds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('newsfeeds.archive');
		}
		if ($canDo->get('core.admin')) {
			JToolbarHelper::checkin('newsfeeds.checkin');
			}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'newsfeeds.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('newsfeeds.trash');
			JToolbarHelper::divider();
		}
		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_newsfeeds');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS');
	}
}
