<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a template style.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesViewStyle extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$itemForm	= $this->get('Form');
		$paramsForm	= $this->get('ParamsForm');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the record to the form.
		$itemForm->bind($item);
		$paramsForm->bind($item->params);

		$this->assignRef('state',		$state);
		$this->assignRef('item',		$item);
		$this->assignRef('form',		$itemForm);
		$this->assignRef('paramsform',	$paramsForm);

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
		$canDo		= TemplatesHelper::getActions();

		JToolBarHelper::title(
			$isNew ? JText::_('Templates_Manager_Add_Style')
			: JText::_('Templates_Manager_Edit_Style')
		);

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('style.apply');
			JToolBarHelper::save('style.save');
			JToolBarHelper::addNew('style.save2new', 'JToolbar_Save_and_new');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('style.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('style.cancel');
		}
		else {
			JToolBarHelper::cancel('style.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.style.edit');
	}
}
