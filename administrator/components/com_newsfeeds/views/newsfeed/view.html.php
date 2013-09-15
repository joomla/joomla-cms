<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a newsfeed.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.6
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
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($this->getLayout() == 'modal')
		{
			$this->form->setFieldAttribute('language', 'readonly', 'true');
			$this->form->setFieldAttribute('catid', 'readonly', 'true');
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		// Since we don't track these assets at the item level, use the category id.
		$canDo		= NewsfeedsHelper::getActions($this->item->catid, 0);

		JToolbarHelper::title(JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEED'), 'newsfeeds.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0))
		{
			JToolbarHelper::apply('newsfeed.apply');
			JToolbarHelper::save('newsfeed.save');
		}
		if (!$checkedOut && count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0){
			JToolbarHelper::save2new('newsfeed.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('newsfeed.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('newsfeed.cancel');
		}
		else
		{
			JToolbarHelper::cancel('newsfeed.cancel', 'JTOOLBAR_CLOSE');
		}

		if ($this->state->params->get('save_history') && $user->authorise('core.edit'))
		{
			$itemId = $this->item->id;
			$typeAlias = 'com_newsfeeds.newsfeed';
			JToolbarHelper::versions($typeAlias, $itemId);
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS_EDIT');
	}
}
