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
 * View to edit a banner.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.5
 */
class BannersViewBanner extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the record to the form.
		$form->bind($item);
		$form->bind($item->params);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= BannersHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title($isNew ? JText::_('COM_BANNERS_MANAGER_BANNER_NEW') : JText::_('COM_BANNERS_MANAGER_BANNER_EDIT'));

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			JToolBarHelper::apply('banner.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('banner.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::addNew('banner.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('banner.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('banner.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('banner.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('screen.banners.banner');
	}
}