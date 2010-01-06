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
		$form 	= $this->get('Form');

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

		JToolBarHelper::title($isNew ? JText::_('Banners_Manager_Banner_New') : JText::_('Banners_Manager_Banner_Edit'));

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			JToolBarHelper::apply('banner.apply', 'JToolbar_Apply');
			JToolBarHelper::save('banner.save', 'JToolbar_Save');
			JToolBarHelper::addNew('banner.save2new', 'JToolbar_Save_and_new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('banner.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('banner.cancel');
		}
		else {
			JToolBarHelper::cancel('banner.cancel', 'JToolbar_Close');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('screen.banners.banner');
	}
}
