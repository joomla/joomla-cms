<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentViewArticles extends JView
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
		if (count($errors = $this->get('Errors')))
		{
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
	 * Display the toolbar
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= ContentHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('Content_Articles_Title'), 'article.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('article.add', 'new.png', 'new_f2.png','JTOOLBAR_NEW', false);
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::custom('article.edit', 'edit.png', 'edit_f2.png','JTOOLBAR_EDIT', true);
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('articles.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('articles.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			if ($state->get('filter.published') != -1) {
				JToolBarHelper::divider();
				JToolBarHelper::archiveList('articles.archive','JTOOLBAR_ARCHIVE');
			}
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {

			JToolBarHelper::deleteList('', 'articles.delete','JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('articles.trash','JTOOLBAR_TRASH');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_content');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.content.articles','JTOOLBAR_HELP');
	}
}