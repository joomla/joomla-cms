<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 */
class NewsfeedsViewNewsfeeds extends JView
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
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= NewsfeedsHelper::getActions($state->get('filter.category_id'));

		JHtml::_('behavior.modal', 'a.modal');
		JToolBarHelper::title(JText::_('Newsfeeds_Manager_Newsfeeds'), 'generic.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('newsfeed.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('newsfeed.edit');
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('newsfeeds.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
			JToolBarHelper::custom('newsfeeds.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
			if ($state->get('filter.published') != -1) {
				JToolBarHelper::archiveList('newsfeeds.archive');
			}
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'newsfeeds.delete');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('newsfeeds.trash');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_newsfeeds');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.newsfeed');
	}
}
