<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a module.
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesViewModule extends JView
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
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= ModulesHelper::getActions($this->state->get('filter.category_id'), $this->item->id);
		$client		= $this->get('client');

		JToolBarHelper::title(JText::_('Modules_Manager_Module'));

		if ($this->item->module == 'mod_custom') {
			JToolBarHelper::Preview('index.php?option=com_modules&tmpl=component&client='.$client->id.'&pollid='.$this->item->id);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('module.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			JToolBarHelper::save('module.save');
			JToolBarHelper::apply('module.apply');
			JToolBarHelper::addNew('module.save2new', 'JToolbar_Save_and_new');
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('module.cancel');
		}
		else {
			JToolBarHelper::cancel('module.cancel', 'JToolbar_Close');
		}

		JToolBarHelper::help('screen.module.edit');
	}
}
