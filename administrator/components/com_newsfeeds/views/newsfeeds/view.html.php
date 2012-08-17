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
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		JToolbarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEEDS'), 'newsfeeds.png');
		if (count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0) {
			JToolbarHelper::addNew('newsfeed.add');
		}
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('newsfeed.edit');
		}
		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::publish('newsfeeds.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('newsfeeds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('newsfeeds.archive');
		}
		if ($canDo->get('core.admin')) {
			JToolbarHelper::checkin('newsfeeds.checkin');
			}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'newsfeeds.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('newsfeeds.trash');
		}
		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			$title = JText::_('JTOOLBAR_BATCH');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_newsfeeds');
		}
		JToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.name' => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'numarticles' => JText::_('COM_NEWSFEEDS_NUM_ARTICLES_HEADING'),
			'a.cache_time' => JText::_('COM_NEWSFEEDS_CACHE_TIME_HEADING'),
			'a.language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
