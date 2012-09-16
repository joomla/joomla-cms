<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a module.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ModulesViewModule extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

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
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= ModulesHelper::getActions($this->state->get('filter.category_id'), $this->item->id);
		$item		= $this->get('Item');

		JToolbarHelper::title(JText::sprintf('COM_MODULES_MANAGER_MODULE', JText::_($this->item->module)), 'module.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || $canDo->get('core.create') )) {
			JToolbarHelper::apply('module.apply');
			JToolbarHelper::save('module.save');
		}
		if (!$checkedOut && $canDo->get('core.create')) {
			JToolbarHelper::save2new('module.save2new');
		}
			// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolbarHelper::save2copy('module.save2copy');
		}
		if (empty($this->item->id))  {
			JToolbarHelper::cancel('module.cancel');
		} else {
			JToolbarHelper::cancel('module.cancel', 'JTOOLBAR_CLOSE');
		}

		// Get the help information for the menu item.
		$lang = JFactory::getLanguage();

		$help = $this->get('Help');
		if ($lang->hasKey($help->url)) {
			$debug = $lang->setDebug(false);
			$url = JText::_($help->url);
			$lang->setDebug($debug);
		}
		else {
			$url = null;
		}
		JToolbarHelper::help($help->key, false, $url);
	}
}
