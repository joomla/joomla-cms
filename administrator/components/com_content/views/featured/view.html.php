<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentViewFeatured extends JView
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
		$canDo	= ContentHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_CONTENT_FEATURED_TITLE'), 'featured.png');

		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('article.add', 'new.png', 'new_f2.png','JTOOLBAR_NEW', false);
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::custom('article.edit', 'edit.png', 'edit_f2.png','JTOOLBAR_EDIT', true);
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('articles.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('articles.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			if ($this->state->get('filter.published') != -1) {
				JToolBarHelper::archiveList('articles.archive','JTOOLBAR_ARCHIVE');
			}
		}
		if(JFactory::getUser()->authorise('core.manage','com_checkin')) {
			JToolBarHelper::custom('featured.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('featured.delete','remove.png','remove_f2.png','JTOOLBAR_REMOVE', true);

		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_content');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_CONTENT_FEATURED_ARTICLES');
	}
}
