<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a newsfeed.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class NewsfeedsViewNewsfeed extends JViewLegacy
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
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		// Since we don't track these assets at the item level, use the category id.
		$canDo		= NewsfeedsHelper::getActions($this->item->catid,0);

		JToolBarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEED'), 'newsfeeds.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)) {
			JToolBarHelper::apply('newsfeed.apply');
			JToolBarHelper::save('newsfeed.save');
		}
		if (!$checkedOut && count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0){
			JToolBarHelper::save2new('newsfeed.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::save2copy('newsfeed.save2copy');
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('newsfeed.cancel');
		} else {
			JToolBarHelper::cancel('newsfeed.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS_EDIT');
	}
}
