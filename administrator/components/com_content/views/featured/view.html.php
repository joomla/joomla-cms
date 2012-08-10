<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
class ContentViewFeatured extends JViewLegacy
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

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= ContentHelper::getActions($this->state->get('filter.category_id'));

		JToolbarHelper::title(JText::_('COM_CONTENT_FEATURED_TITLE'), 'featured.png');

		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew('article.add');
		}
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('article.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::divider();
			JToolbarHelper::publish('articles.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('articles.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('articles.archive');
			JToolbarHelper::checkin('articles.checkin');
			JToolbarHelper::custom('featured.delete', 'remove.png', 'remove_f2.png', 'JTOOLBAR_REMOVE', true);
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'articles.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::divider();
			JToolbarHelper::trash('articles.trash');
		}

		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_content');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_CONTENT_FEATURED_ARTICLES');
	}
}
