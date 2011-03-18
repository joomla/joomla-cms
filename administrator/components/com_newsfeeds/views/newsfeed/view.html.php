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
 * View to edit a newsfeed.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class NewsfeedsViewNewsfeed extends JView
{
	protected $item;
	protected $form;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

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
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= NewsfeedsHelper::getActions($this->state->get('filter.category_id'), $this->item->id);

		JToolBarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEED'), 'newsfeeds.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)) {
			JToolBarHelper::apply('newsfeed.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('newsfeed.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0){		
			JToolBarHelper::custom('newsfeed.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('newsfeed.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('newsfeed.cancel','JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('newsfeed.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS_EDIT');
	}
}
